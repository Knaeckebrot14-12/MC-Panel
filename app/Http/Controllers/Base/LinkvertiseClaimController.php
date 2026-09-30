<?php

namespace Pterodactyl\Http\Controllers\Base;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Pterodactyl\Models\LinkvertiseClaim;
use Pterodactyl\Services\Coins\CoinService;
use Pterodactyl\Http\Controllers\Controller;

class LinkvertiseClaimController extends Controller
{
    public function __construct(private CoinService $coins)
    {
    }

    /**
     * The page a user lands back on after completing a Linkvertise link. Credits
     * the coins for the claim (once, if it's still valid) and sends them back to
     * the dashboard with a flag the SPA uses to show a confirmation toast.
     */
    public function __invoke(Request $request, string $token): RedirectResponse
    {
        $user = $request->user();

        /** @var LinkvertiseClaim|null $claim */
        $claim = LinkvertiseClaim::query()->where('token', $token)->first();

        if (!$claim || $claim->user_id !== $user->id || $claim->isClaimed() || $claim->isExpired()) {
            return redirect('/coins/earn?claim=invalid');
        }

        $claim->update(['claimed_at' => now()]);

        $this->coins->credit($user, $claim->coins, 'linkvertise', 'Completed a Linkvertise link');

        return redirect('/coins/earn?claim=success&coins=' . $claim->coins);
    }
}
