<?php

namespace Pterodactyl\Services\Coins;

use Pterodactyl\Models\User;
use Pterodactyl\Models\CoinTransaction;
use Pterodactyl\Exceptions\DisplayException;
use Illuminate\Database\ConnectionInterface;

class CoinService
{
    /**
     * Transaction types that count as a referred user "becoming active".
     */
    private const REFERRAL_TRIGGER_TYPES = ['linkvertise', 'afk'];

    public function __construct(private ConnectionInterface $connection)
    {
    }

    /**
     * Add coins to a user's balance and record the transaction.
     */
    public function credit(User $user, int $amount, string $type, ?string $description = null): User
    {
        if ($amount < 0) {
            throw new \InvalidArgumentException('Amount to credit must not be negative.');
        }

        $user = $this->connection->transaction(function () use ($user, $amount, $type, $description) {
            $user->increment('coins', $amount);

            CoinTransaction::query()->create([
                'user_id' => $user->id,
                'amount' => $amount,
                'type' => $type,
                'description' => $description,
            ]);

            return $user->refresh();
        });

        if (in_array($type, self::REFERRAL_TRIGGER_TYPES, true)) {
            $this->rewardReferrer($user);
        }

        return $user;
    }

    /**
     * Pays the referrer of a user once, the first time that user actually earns coins
     * through a real activity (Linkvertise or the AFK page).
     */
    private function rewardReferrer(User $user): void
    {
        $reward = (int) config('coins.referral.referrer_reward');

        if ($reward <= 0 || !$user->referred_by || $user->referral_rewarded_at) {
            return;
        }

        $referrer = User::query()->find($user->referred_by);
        if (!$referrer) {
            return;
        }

        // Claim the reward atomically so concurrent earnings can't pay it out twice.
        $claimed = User::query()
            ->whereKey($user->id)
            ->whereNull('referral_rewarded_at')
            ->update(['referral_rewarded_at' => now()]);

        if ($claimed) {
            $this->credit($referrer, $reward, 'referral', "Referral reward — {$user->username} became active");
        }
    }

    /**
     * Remove coins from a user's balance and record the transaction.
     *
     * @throws DisplayException if the user does not have enough coins.
     */
    public function debit(User $user, int $amount, string $type, ?string $description = null): User
    {
        if ($amount < 0) {
            throw new \InvalidArgumentException('Amount to debit must not be negative.');
        }

        return $this->connection->transaction(function () use ($user, $amount, $type, $description) {
            /** @var User $locked */
            $locked = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();

            if ($locked->coins < $amount) {
                throw new DisplayException(trans('coins.errors.insufficient', ['needed' => $amount, 'have' => $locked->coins]));
            }

            $locked->decrement('coins', $amount);

            CoinTransaction::query()->create([
                'user_id' => $user->id,
                'amount' => -$amount,
                'type' => $type,
                'description' => $description,
            ]);

            return $locked->refresh();
        });
    }

    /**
     * Attempt to remove coins from a user's balance without throwing if they
     * don't have enough — returns whether the debit succeeded.
     */
    public function tryDebit(User $user, int $amount, string $type, ?string $description = null): bool
    {
        try {
            $this->debit($user, $amount, $type, $description);

            return true;
        } catch (DisplayException) {
            return false;
        }
    }
}
