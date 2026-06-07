<?php

namespace App\Http\Controllers;

use App\Models\GroupMember;
use App\Models\GroupPlayer;
use App\Models\PokerGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupController extends Controller
{
    public function index()
    {
        $groups = PokerGroup::whereHas('memberships', fn ($q) => $q->where('user_id', Auth::id()))
            ->where('isActive', true)
            ->with(['memberships', 'pokerNights' => fn ($q) => $q->limit(8)->with('coverImage', 'winner.user')])
            ->get();

        return view('groups.index', compact('groups'));
    }

    public function create()
    {
        return view('groups.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $group = PokerGroup::create([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'owner_id'    => Auth::id(),
        ]);

        GroupMember::create([
            'group_id'  => $group->id,
            'user_id'   => Auth::id(),
            'role'      => 'OWNER',
            'joined_at' => now(),
        ]);

        // Auto-add the owner as a Core player on the roster
        $user = Auth::user();
        GroupPlayer::create([
            'group_id' => $group->id,
            'user_id'  => $user->id,
            'name'     => $user->username,
            'role'     => 'CORE',
            'email'    => $user->email,
        ]);

        return redirect()->route('groups.show', $group)->with('success', 'Group created!');
    }

    public function show(PokerGroup $group)
    {
        $this->authorizeGroupAccess($group);

        $nights = $group->pokerNights()
            ->with(['images', 'attendees.groupPlayer', 'attendees.user'])
            ->orderByDesc('scheduled_at')
            ->get();

        $members = $group->members()->where('isActive', true)->get();
        $players = $group->players()
            ->withCount(['gameAttendances as wins' => fn($q) => $q->where('placement', 1)])
            ->get()
            ->sortBy(fn($p) => [$p->wins * -1, $p->name])
            ->values();

        return view('groups.show', compact('group', 'nights', 'members', 'players'));
    }

    public function edit(PokerGroup $group)
    {
        $this->authorizeOwner($group);
        return view('groups.edit', compact('group'));
    }

    public function update(Request $request, PokerGroup $group)
    {
        $this->authorizeOwner($group);

        $data = $request->validate([
            'name'           => ['required', 'string', 'max:60'],
            'description'    => ['nullable', 'string', 'max:500'],
            'avatar'         => ['nullable', 'image', 'max:5120'],
            'invite_enabled' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store("groups/{$group->id}", 'public');
            $data['avatar_path'] = $path;
        }

        $group->update([
            'name'           => $data['name'],
            'description'    => $data['description'] ?? null,
            'avatar_path'    => $data['avatar_path'] ?? $group->avatar_path,
            'invite_enabled' => $request->boolean('invite_enabled'),
        ]);

        return redirect()->route('groups.show', $group)->with('success', 'Group updated!');
    }

    public function joinForm(string $code)
    {
        $group = PokerGroup::where('invite_code', strtoupper($code))->where('isActive', true)->firstOrFail();

        if (! $group->invite_enabled) {
            abort(403, 'Invitations for this group are currently disabled.');
        }

        $alreadyMember = GroupMember::where('group_id', $group->id)->where('user_id', Auth::id())->exists();

        return view('groups.join', compact('group', 'alreadyMember'));
    }

    public function join(string $code)
    {
        $group = PokerGroup::where('invite_code', strtoupper($code))->where('isActive', true)->firstOrFail();

        if (! $group->invite_enabled) {
            abort(403, 'Invitations for this group are currently disabled.');
        }

        $alreadyMember = GroupMember::where('group_id', $group->id)->where('user_id', Auth::id())->exists();
        if ($alreadyMember) {
            return redirect()->route('groups.show', $group);
        }

        GroupMember::create([
            'group_id'  => $group->id,
            'user_id'   => Auth::id(),
            'role'      => 'MEMBER',
            'joined_at' => now(),
        ]);

        // Add to player roster if not already there (e.g. linked from an invite token)
        $user = Auth::user();
        $alreadyOnRoster = GroupPlayer::where('group_id', $group->id)->where('user_id', $user->id)->exists();
        if (! $alreadyOnRoster) {
            GroupPlayer::create([
                'group_id' => $group->id,
                'user_id'  => $user->id,
                'name'     => $user->username,
                'role'     => 'CORE',
                'email'    => $user->email,
            ]);
        }

        return redirect()->route('groups.show', $group)->with('success', "Welcome to {$group->name}!");
    }

    private function authorizeGroupAccess(PokerGroup $group): void
    {
        $isMember = GroupMember::where('group_id', $group->id)->where('user_id', Auth::id())->exists();
        if (! $isMember && ! Auth::user()->isAdmin()) {
            abort(403);
        }
    }

    private function authorizeOwner(PokerGroup $group): void
    {
        if (Auth::id() !== $group->owner_id && ! Auth::user()->isAdmin()) {
            abort(403);
        }
    }
}
