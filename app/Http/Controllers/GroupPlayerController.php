<?php

namespace App\Http\Controllers;

use App\Models\GroupMember;
use App\Models\GroupPlayer;
use App\Models\PokerGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GroupPlayerController extends Controller
{
    public function index(PokerGroup $group)
    {
        $this->authorizeOwnerOrAdmin($group);
        $players = $group->players()->with('user')->get();
        return view('groups.players.index', compact('group', 'players'));
    }

    public function create(PokerGroup $group)
    {
        $this->authorizeOwnerOrAdmin($group);
        return view('groups.players.create', compact('group'));
    }

    public function store(Request $request, PokerGroup $group)
    {
        $this->authorizeOwnerOrAdmin($group);

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'nickname' => ['nullable', 'string', 'max:50'],
            'role'     => ['required', 'in:CORE,GUEST'],
            'email'    => ['nullable', 'email', 'max:255'],
            'photo'    => ['nullable', 'image', 'max:3072'],
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store("players/{$group->id}", 'public');
        }

        // If an existing user has this email, link them automatically
        $linkedUserId = null;
        if (! empty($data['email'])) {
            $linkedUserId = \App\Models\User::where('email', $data['email'])->value('id');
        }

        $player = GroupPlayer::create([
            'group_id'  => $group->id,
            'user_id'   => $linkedUserId,
            'name'      => $data['name'],
            'nickname'  => $data['nickname'] ?? null,
            'photo_path'=> $photoPath,
            'role'      => $data['role'],
            'email'     => $data['email'] ?? null,
        ]);

        return redirect()->route('players.index', $group)->with('success', "{$player->displayName()} added to the roster!");
    }

    public function edit(PokerGroup $group, GroupPlayer $player)
    {
        abort_if($player->group_id !== $group->id, 404);
        if ($player->user_id !== Auth::id()) {
            $this->authorizeOwnerOrAdmin($group);
        }
        return view('groups.players.edit', compact('group', 'player'));
    }

    public function update(Request $request, PokerGroup $group, GroupPlayer $player)
    {
        abort_if($player->group_id !== $group->id, 404);
        if ($player->user_id !== Auth::id()) {
            $this->authorizeOwnerOrAdmin($group);
        }

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'nickname' => ['nullable', 'string', 'max:50'],
            'role'     => ['required', 'in:CORE,GUEST'],
            'email'    => ['nullable', 'email', 'max:255'],
            'photo'    => ['nullable', 'image', 'max:3072'],
        ]);

        $photoPath = $player->photo_path;
        if ($request->hasFile('photo')) {
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }
            $photoPath = $request->file('photo')->store("players/{$group->id}", 'public');
        }

        // Refresh link if email changed
        $linkedUserId = $player->user_id;
        if (isset($data['email']) && $data['email'] !== $player->email) {
            $linkedUserId = ! empty($data['email'])
                ? \App\Models\User::where('email', $data['email'])->value('id')
                : null;
        }

        // Generate invite token if email added and none exists
        $inviteToken = $player->invite_token;
        if (! empty($data['email']) && ! $inviteToken) {
            $inviteToken = Str::random(40);
        }

        $player->update([
            'user_id'      => $linkedUserId,
            'name'         => $data['name'],
            'nickname'     => $data['nickname'] ?? null,
            'photo_path'   => $photoPath,
            'role'         => $data['role'],
            'email'        => $data['email'] ?? null,
            'invite_token' => $inviteToken,
        ]);

        return redirect()->route('players.index', $group)->with('success', 'Player updated.');
    }

    public function destroy(PokerGroup $group, GroupPlayer $player)
    {
        abort_if($player->group_id !== $group->id, 404);
        $this->authorizeOwnerOrAdmin($group);

        if ($player->photo_path) {
            Storage::disk('public')->delete($player->photo_path);
        }

        $player->delete();

        return back()->with('success', 'Player removed from roster.');
    }

    private function authorizeOwnerOrAdmin(PokerGroup $group): void
    {
        $isOwner = $group->owner_id === Auth::id();
        if (! $isOwner && ! Auth::user()->isAdmin()) {
            abort(403);
        }
    }
}
