<?php

namespace Pterodactyl\Http\Requests\Api\Client\Servers\Databases;

use Pterodactyl\Models\Permission;
use Pterodactyl\Http\Requests\Api\Client\ClientApiRequest;

class OpenManagerRequest extends ClientApiRequest
{
    /**
     * phpMyAdmin signs in with the database password, so it needs the permission to see it.
     */
    public function permission(): string
    {
        return Permission::ACTION_DATABASE_VIEW_PASSWORD;
    }
}
