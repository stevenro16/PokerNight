<?php

namespace App\Http\Controllers;

use App\Models\GroupPlayer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $groupProfiles = GroupPlayer::where('user_id', $user->id)->with('group')->get();
        return view('profile.edit', compact('user', 'groupProfiles'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'username' => [
                'required', 'string', 'min:3', 'max:30',
                'regex:/^[a-zA-Z0-9_]+$/',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'avatar' => ['nullable', 'image', 'max:10240'],
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar_url) {
                Storage::disk('public')->delete($user->avatar_url);
            }
            $user->avatar_url = $request->file('avatar')->store('avatars', 'public');
        }

        $user->username = $data['username'];
        $user->save();

        return back()->with('success', 'Profile updated!');
    }
}
