<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\GroupMember;
use App\Models\GroupPlayer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class RegisterController extends Controller
{
    public function show(Request $request)
    {
        return view('auth.register', [
            'prefillEmail' => $request->query('email', ''),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'min:3', 'max:30', 'unique:users,username', 'regex:/^[a-zA-Z0-9_]+$/'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'username' => $data['username'],
            'email'    => $data['email'],
            'password' => $data['password'],
            'role'     => 'USER',
        ]);

        Auth::login($user);

        // Link via invite token if present in session
        $token = Session::pull('invite_token');
        if ($token) {
            $player = GroupPlayer::where('invite_token', $token)->first();
            if ($player && ! $player->user_id) {
                $player->update(['user_id' => $user->id]);
                $this->ensureGroupMember($player->group_id, $user->id);
                return redirect()->route('groups.show', $player->group_id)
                    ->with('success', "Welcome! You've been linked to your player profile.");
            }
        }

        // Auto-link any group_player records that share this email
        GroupPlayer::where('email', $user->email)->whereNull('user_id')->each(function ($player) use ($user) {
            $player->update(['user_id' => $user->id]);
            $this->ensureGroupMember($player->group_id, $user->id);
        });

        return redirect()->route('dashboard');
    }

    private function ensureGroupMember(string $groupId, string $userId): void
    {
        if (! GroupMember::where('group_id', $groupId)->where('user_id', $userId)->exists()) {
            GroupMember::create([
                'group_id'  => $groupId,
                'user_id'   => $userId,
                'role'      => 'MEMBER',
                'joined_at' => now(),
            ]);
        }
    }
}
