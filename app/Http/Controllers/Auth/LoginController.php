<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\GroupMember;
use App\Models\GroupPlayer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $login = $request->input('login');
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [$field => $login, 'password' => $request->password];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            if (! $user->isActive) {
                Auth::logout();
                return back()->withErrors(['login' => 'Your account has been disabled.']);
            }

            $request->session()->regenerate();

            // Link any roster entries whose email matches this user's email
            GroupPlayer::where('email', $user->email)->whereNull('user_id')
                ->each(function ($player) use ($user) {
                    $player->update(['user_id' => $user->id]);
                    if (! GroupMember::where('group_id', $player->group_id)->where('user_id', $user->id)->exists()) {
                        GroupMember::create([
                            'group_id'  => $player->group_id,
                            'user_id'   => $user->id,
                            'role'      => 'MEMBER',
                            'joined_at' => now(),
                        ]);
                    }
                });

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors(['login' => 'Invalid credentials.'])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }
}
