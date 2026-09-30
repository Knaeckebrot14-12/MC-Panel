<?php

namespace Pterodactyl\Http\Middleware;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Pterodactyl\Exceptions\Http\PasswordChangeRequiredException;

class EnsurePasswordHasBeenChanged
{
    /**
     * Blocks access for a user who was issued a generated password (e.g. via
     * the forgot-password flow) and has not yet replaced it with one of their
     * own choosing. API/JSON requests get a displayable error; normal page
     * loads (e.g. the Blade admin panel) are redirected back to the client
     * dashboard, where the React app shows a screen that forces the change
     * before anything else is reachable. The account update routes themselves
     * are excluded from this middleware so the user is able to actually set
     * the new password.
     *
     * @throws PasswordChangeRequiredException
     */
    public function handle(Request $request, \Closure $next): mixed
    {
        $user = $request->user();

        if (!$user || !$user->must_change_password) {
            return $next($request);
        }

        $uri = rtrim($request->getRequestUri(), '/') . '/';
        if ($request->isJson() || Str::startsWith($uri, '/api/')) {
            throw new PasswordChangeRequiredException();
        }

        return redirect()->to('/');
    }
}
