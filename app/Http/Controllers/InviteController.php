<?php

namespace App\Http\Controllers;

use App\Models\GroupPlayer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class InviteController extends Controller
{
    public function show(string $token)
    {
        $player = GroupPlayer::where('invite_token', $token)->firstOrFail();
        $group  = $player->group;

        // Already logged in — try to link now
        if (Auth::check()) {
            $user = Auth::user();
            if (! $player->user_id) {
                $player->update(['user_id' => $user->id]);

                // Also add them as a group member if not already
                if (! $group->memberships()->where('user_id', $user->id)->exists()) {
                    \App\Models\GroupMember::create([
                        'group_id'  => $group->id,
                        'user_id'   => $user->id,
                        'role'      => 'MEMBER',
                        'joined_at' => now(),
                    ]);
                }
            }
            return redirect()->route('groups.show', $group)
                ->with('success', "You've been linked to your player profile in {$group->name}!");
        }

        // Not logged in — store token in session and send to register
        Session::put('invite_token', $token);
        $email = $player->email ?? '';
        return redirect()->route('register', ['email' => $email])
            ->with('invite_info', "Create an account to join {$group->name} as {$player->displayName()}.");
    }
}
