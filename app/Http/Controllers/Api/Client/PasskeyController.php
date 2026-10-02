<?php

namespace Pterodactyl\Http\Controllers\Api\Client;

use Illuminate\Http\JsonResponse;
use Pterodactyl\Models\UserPasskey;
use Pterodactyl\Facades\Activity;
use Pterodactyl\Exceptions\DisplayException;
use Pterodactyl\Services\Users\PasskeyService;
use Pterodactyl\Http\Requests\Api\Client\ClientApiRequest;

/**
 * Self-service management of the account's own passkeys. Adding and removing one needs the
 * current password, so a hijacked session can't plant a passkey for later.
 */
class PasskeyController extends ClientApiController
{
    public function __construct(private PasskeyService $passkeys)
    {
        parent::__construct();
    }

    public function index(ClientApiRequest $request): JsonResponse
    {
        $passkeys = UserPasskey::query()
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        return new JsonResponse(['data' => $passkeys->map(fn (UserPasskey $passkey) => $this->present($passkey))->all()]);
    }

    /**
     * Challenge for navigator.credentials.create().
     *
     * @throws \Pterodactyl\Exceptions\DisplayException
     */
    public function options(ClientApiRequest $request): JsonResponse
    {
        $this->assertPassword($request);

        return new JsonResponse(['data' => $this->passkeys->registrationOptions($request->user())]);
    }

    /**
     * @throws \Pterodactyl\Exceptions\DisplayException
     */
    public function store(ClientApiRequest $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:64'],
            'credential' => ['required', 'array'],
        ]);
        $this->assertPassword($request);

        $passkey = $this->passkeys->register($request->user(), $data['name'], $data['credential']);

        Activity::event('auth:passkey.add')->property('name', $passkey->name)->log();

        return new JsonResponse(['data' => $this->present($passkey)], JsonResponse::HTTP_CREATED);
    }

    /**
     * @throws \Pterodactyl\Exceptions\DisplayException
     */
    public function delete(ClientApiRequest $request, int $id): JsonResponse
    {
        $this->assertPassword($request);

        $passkey = UserPasskey::query()->where('user_id', $request->user()->id)->whereKey($id)->first();
        if ($passkey) {
            $passkey->delete();

            Activity::event('auth:passkey.remove')->property('name', $passkey->name)->log();
        }

        return new JsonResponse([], JsonResponse::HTTP_NO_CONTENT);
    }

    private function assertPassword(ClientApiRequest $request): void
    {
        $password = $request->input('password');
        if (!is_string($password) || !password_verify($password, $request->user()->password)) {
            throw new DisplayException(trans('passkeys.errors.invalid_password'));
        }
    }

    private function present(UserPasskey $passkey): array
    {
        return [
            'id' => $passkey->id,
            'name' => $passkey->name,
            'created_at' => $passkey->created_at?->toAtomString(),
            'last_used_at' => $passkey->last_used_at?->toAtomString(),
        ];
    }
}
