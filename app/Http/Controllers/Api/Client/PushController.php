<?php

namespace Pterodactyl\Http\Controllers\Api\Client;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Pterodactyl\Exceptions\DisplayException;
use Pterodactyl\Services\Notifications\PushService;

class PushController extends ClientApiController
{
    public function __construct(private PushService $push)
    {
        parent::__construct();
    }

    public function index(Request $request): JsonResponse
    {
        return new JsonResponse([
            'supported' => PushService::supported(),
            'public_key' => PushService::supported() ? $this->push->publicKey() : null,
            'devices' => DB::table('push_subscriptions')->where('user_id', $request->user()->id)->count(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'endpoint' => 'required|string|url|max:1000',
            'keys.p256dh' => 'required|string|max:255|regex:/^[A-Za-z0-9_-]+=*$/',
            'keys.auth' => 'required|string|max:255|regex:/^[A-Za-z0-9_-]+=*$/',
        ]);

        if (!PushService::allowedEndpoint($data['endpoint'])) {
            throw new DisplayException(trans('push.errors.endpoint'));
        }
        // At most a handful of devices per account.
        if (DB::table('push_subscriptions')->where('user_id', $request->user()->id)->count() >= 10) {
            DB::table('push_subscriptions')->where('user_id', $request->user()->id)->orderBy('updated_at')->limit(1)->delete();
        }

        $this->push->subscribe($request->user(), $data['endpoint'], $data['keys']['p256dh'], $data['keys']['auth']);

        return new JsonResponse([], 204);
    }

    public function delete(Request $request): JsonResponse
    {
        $data = $request->validate(['endpoint' => 'required|string|max:1000']);
        $this->push->unsubscribe($request->user(), $data['endpoint']);

        return new JsonResponse([], 204);
    }

    public function test(Request $request): JsonResponse
    {
        $sent = $this->push->sendToUsers($request->user()->id, trans('push.test.title'), trans('push.test.body'), '/account', 'test');

        return new JsonResponse(['sent' => $sent]);
    }
}
