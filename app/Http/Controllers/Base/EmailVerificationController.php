<?php

namespace Pterodactyl\Http\Controllers\Base;

use Pterodactyl\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Pterodactyl\Http\Controllers\Controller;

class EmailVerificationController extends Controller
{
    /**
     * Target of the link in the confirmation mail (signed, so it can't be forged).
     */
    public function __invoke(Request $request, int $id, string $hash): RedirectResponse
    {
        $user = User::query()->find($id);
        if (!$user || !hash_equals(sha1(mb_strtolower($user->email)), $hash)) {
            return redirect('/?verified=invalid');
        }

        if (!$user->email_verified_at) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        return redirect('/?verified=1');
    }
}
