<?php

namespace Pterodactyl\Http\Controllers\Api\Client;

use Illuminate\Http\JsonResponse;
use Pterodactyl\Models\Announcement;

class AnnouncementController extends ClientApiController
{
    /**
     * Returns every announcement an admin currently has visible, newest first.
     */
    public function index(): JsonResponse
    {
        $announcements = Announcement::query()
            ->where('is_active', true)
            ->orderByDesc('created_at')
            ->get(['id', 'title', 'content', 'created_at']);

        return new JsonResponse(['data' => $announcements]);
    }
}
