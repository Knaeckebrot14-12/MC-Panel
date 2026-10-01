<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Pterodactyl\Models\User;
use Pterodactyl\Models\Model;
use Illuminate\Support\Collection;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\View\Factory as ViewFactory;
use Pterodactyl\Exceptions\DisplayException;
use Pterodactyl\Http\Controllers\Controller;
use Illuminate\Contracts\Translation\Translator;
use Pterodactyl\Models\Server;
use Pterodactyl\Services\Users\UserUpdateService;
use Pterodactyl\Traits\Helpers\AvailableLanguages;
use Pterodactyl\Services\Users\UserCreationService;
use Pterodactyl\Services\Users\UserDeletionService;
use Pterodactyl\Services\Servers\SuspensionService;
use Pterodactyl\Services\Coins\CoinService;
use Pterodactyl\Services\StaffAudit;
use Pterodactyl\Http\Requests\Admin\UserFormRequest;
use Pterodactyl\Http\Requests\Admin\NewUserFormRequest;
use Pterodactyl\Repositories\Wings\DaemonPowerRepository;
use Pterodactyl\Contracts\Repository\UserRepositoryInterface;

class UserController extends Controller
{
    use AvailableLanguages;

    /**
     * UserController constructor.
     */
    public function __construct(
        protected AlertsMessageBag $alert,
        protected UserCreationService $creationService,
        protected UserDeletionService $deletionService,
        protected Translator $translator,
        protected UserUpdateService $updateService,
        protected UserRepositoryInterface $repository,
        protected ViewFactory $view,
        protected SuspensionService $suspensionService,
        protected DaemonPowerRepository $powerRepository,
        protected CoinService $coins,
    ) {
    }

    /**
     * Display user index page.
     */
    public function index(Request $request): View
    {
        $base = User::query()->select('users.*')
            ->selectRaw('COUNT(DISTINCT(subusers.id)) as subuser_of_count')
            ->selectRaw('COUNT(DISTINCT(servers.id)) as servers_count')
            ->leftJoin('subusers', 'subusers.user_id', '=', 'users.id')
            ->leftJoin('servers', 'servers.owner_id', '=', 'users.id')
            ->groupBy('users.id');

        $role = (string) $request->query('role', '');
        if (array_key_exists($role, User::ROLES)) {
            $base->where('users.role', $role);
        }

        // Without users.email, searching or sorting by e-mail would reveal the addresses too, so the
        // search box looks at usernames instead.
        $seesEmails = $request->user()->hasStaffPermission('users.email');
        if (!$seesEmails && $request->has('filter.email')) {
            $request->merge(['filter' => ['username' => $request->input('filter.email')]]);
        }

        $users = QueryBuilder::for($base)
            ->allowedFilters($seesEmails ? ['username', 'email', 'uuid'] : ['username', 'uuid'])
            ->defaultSort('-root_admin')
            ->allowedSorts($seesEmails ? ['id', 'uuid', 'username', 'email', 'coins', 'created_at', 'root_admin'] : ['id', 'uuid', 'username', 'coins', 'created_at', 'root_admin'])
            ->paginate(50)
            ->appends($request->query());

        return view('admin.users.index', ['users' => $users, 'roleFilter' => $role]);
    }

    /**
     * Display new user page.
     */
    public function create(): View
    {
        return view('admin.users.new', [
            'languages' => $this->getAvailableLanguages(true),
        ]);
    }

    /**
     * Only allow acting on accounts that rank below the acting team member.
     *
     * @throws DisplayException
     */
    protected function ensureOutranks(Request $request, User $target): void
    {
        if (!$request->user()->outranks($target)) {
            throw new DisplayException(trans('admin/users.view.roles.rank_too_low'));
        }
    }

    /**
     * Change the role of a user.
     *
     * @throws DisplayException
     */
    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $actor = $request->user();
        $role = (string) $request->input('role');

        if ($actor->is($user)) {
            throw new DisplayException(trans('admin/users.view.roles.change_self'));
        }

        if (!in_array($role, $actor->assignableRoles(), true) || !$actor->outranks($user)) {
            throw new DisplayException(trans('admin/users.view.roles.rank_too_low'));
        }

        $user->role = $role;
        $user->save();

        StaffAudit::record('user.role', $user->username, ['role' => trans('admin/users.roles.' . $role)], $user);

        $this->alert->success(trans('admin/users.view.roles.updated'))->flash();

        return redirect()->route('admin.users.view', $user->id);
    }

    /**
     * Display user view page.
     */
    public function view(User $user): View
    {
        $ip = \Pterodactyl\Services\Users\RegistrationGuard::countableIp($user->registration_ip);

        return view('admin.users.view', [
            'user' => $user,
            'languages' => $this->getAvailableLanguages(true),
            // Other accounts registered from the same public address (possible alt accounts).
            'sameIpUsers' => $ip
                ? User::query()->where('registration_ip', $ip)->where('id', '!=', $user->id)->orderBy('id')->limit(20)->get(['id', 'username', 'created_at'])
                : collect(),
        ]);
    }

    /**
     * Delete a user from the system.
     *
     * @throws \Exception
     * @throws DisplayException
     */
    public function delete(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) {
            throw new DisplayException(__('admin/user.exceptions.delete_self'));
        }

        $this->ensureOutranks($request, $user);

        $username = $user->username;
        $this->deletionService->handle($user);
        StaffAudit::record('user.deleted', $username, [], $user);

        return redirect()->route('admin.users');
    }

    /**
     * Suspend a user's account and stop + suspend every server they own.
     *
     * @throws \Throwable
     */
    /**
     * Confirms a user's e-mail address by hand (e.g. when their mail never arrived).
     */
    public function verifyEmail(Request $request, User $user): RedirectResponse
    {
        if (!$user->email_verified_at) {
            $user->forceFill(['email_verified_at' => now()])->save();
            StaffAudit::record('user.email_verified', $user->username, [], $user);
        }

        $this->alert->success(trans('admin/users.notices.email_verified'))->flash();

        return redirect()->route('admin.users.view', $user->id);
    }

    public function suspend(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) {
            throw new DisplayException(trans('admin/users.notices.suspend_self'));
        }

        $this->ensureOutranks($request, $user);

        $user->update(['suspended_at' => now()]);

        Server::query()->where('owner_id', $user->id)->get()->each(function (Server $server) {
            try {
                $this->powerRepository->setServer($server)->send('stop');
            } catch (\Throwable) {
                // The server may already be offline or the node unreachable — that
                // shouldn't block suspending the account itself.
            }

            $this->suspensionService->toggle($server, SuspensionService::ACTION_SUSPEND);
        });

        StaffAudit::record('user.suspended', $user->username, [], $user);
        $this->alert->success(trans('admin/users.notices.suspended'))->flash();

        return redirect()->route('admin.users.view', $user->id);
    }

    /**
     * Unsuspend a user's account and every server they own.
     *
     * @throws \Throwable
     */
    public function unsuspend(Request $request, User $user): RedirectResponse
    {
        $this->ensureOutranks($request, $user);

        $user->update(['suspended_at' => null]);

        Server::query()->where('owner_id', $user->id)->get()->each(function (Server $server) {
            $this->suspensionService->toggle($server, SuspensionService::ACTION_UNSUSPEND);
        });

        StaffAudit::record('user.unsuspended', $user->username, [], $user);
        $this->alert->success(trans('admin/users.notices.unsuspended'))->flash();

        return redirect()->route('admin.users.view', $user->id);
    }

    /**
     * Manually credit or debit a user's coin balance.
     *
     * @throws DisplayException
     * @throws \Throwable
     */
    public function adjustCoins(Request $request, User $user): RedirectResponse
    {
        $this->ensureOutranks($request, $user);

        $request->validate([
            'amount' => 'required|integer|not_in:0',
            'description' => 'nullable|string|max:191',
        ]);

        $amount = (int) $request->input('amount');
        $description = $request->input('description') ?: 'Manual adjustment by an administrator';

        if ($amount > 0) {
            $this->coins->credit($user, $amount, 'admin:adjustment', $description);
        } else {
            $this->coins->debit($user, abs($amount), 'admin:adjustment', $description);
        }

        StaffAudit::record('user.coins', $user->username, ['amount' => ($amount > 0 ? '+' : '') . $amount], $user);
        $this->alert->success(trans('admin/users.notices.coins_updated'))->flash();

        return redirect()->route('admin.users.view', $user->id);
    }

    /**
     * Create a user.
     *
     * @throws \Exception
     * @throws \Throwable
     */
    public function store(NewUserFormRequest $request): RedirectResponse
    {
        $user = $this->creationService->handle($request->normalize());

        $role = (string) $request->input('role', User::ROLE_USER);
        if ($role !== User::ROLE_USER && in_array($role, $request->user()->assignableRoles(), true)) {
            $user->role = $role;
            $user->save();
        }

        $this->alert->success($this->translator->get('admin/user.notices.account_created'))->flash();

        return redirect()->route('admin.users.view', $user->id);
    }

    /**
     * Update a user on the system.
     *
     * @throws \Pterodactyl\Exceptions\Model\DataValidationException
     * @throws \Pterodactyl\Exceptions\Repository\RecordNotFoundException
     */
    public function update(UserFormRequest $request, User $user): RedirectResponse
    {
        if (!$request->user()->is($user)) {
            $this->ensureOutranks($request, $user);
        }

        $this->updateService
            ->setUserLevel(User::USER_LEVEL_ADMIN)
            ->handle($user, $request->normalize());

        StaffAudit::record('user.updated', $user->username, [], $user);
        $this->alert->success(trans('admin/user.notices.account_updated'))->flash();

        return redirect()->route('admin.users.view', $user->id);
    }

    /**
     * Get a JSON response of users on the system.
     */
    public function json(Request $request): Model|Collection
    {
        $actor = $request->user();
        $seesEmails = $actor->hasStaffPermission('users.email');
        if (!$seesEmails && $request->has('filter.email')) {
            $request->merge(['filter' => ['username' => $request->input('filter.email')]]);
        }

        $users = QueryBuilder::for(User::query())->allowedFilters($seesEmails ? ['email', 'username'] : ['username'])->paginate(25);

        // The server owner pickers show these; the e-mail only for team members allowed to see it.
        $present = function (User $user) use ($actor) {
            $visible = $actor->canSeeEmailOf($user);
            // @phpstan-ignore-next-line property.notFound
            $user->md5 = md5(strtolower($visible ? $user->email : $user->username));
            $user->email = $actor->visibleEmail($user);

            return $user;
        };

        // Handle single user requests.
        if ($request->query('user_id')) {
            return $present(User::query()->findOrFail($request->input('user_id')));
        }

        return $users->map($present);
    }
}
