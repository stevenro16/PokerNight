<?php

namespace App\Http\Controllers;

use App\Models\GroupMember;
use App\Models\GroupPlayer;
use App\Models\PokerGroup;
use App\Models\PokerNight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PokerNightController extends Controller
{
    public function create(PokerGroup $group)
    {
        $this->authorizeMember($group);
        $members = $group->members()->where('isActive', true)->get();
        return view('groups.nights.create', compact('group', 'members'));
    }

    public function store(Request $request, PokerGroup $group)
    {
        $this->authorizeMember($group);

        $data = $request->validate([
            'title'        => ['required', 'string', 'max:100'],
            'scheduled_at' => ['required', 'date'],
            'buy_in'       => ['nullable', 'numeric', 'min:0'],
            'notes'        => ['nullable', 'string', 'max:1000'],
        ]);

        $night = PokerNight::create([
            'group_id'     => $group->id,
            'created_by'   => Auth::id(),
            'title'        => $data['title'],
            'scheduled_at' => $data['scheduled_at'],
            'buy_in'       => $data['buy_in'] ?? null,
            'notes'        => $data['notes'] ?? null,
            'status'       => 'SCHEDULED',
        ]);

        return redirect()->route('nights.show', [$group, $night])->with('success', 'Poker night scheduled!');
    }

    public function show(PokerGroup $group, PokerNight $night)
    {
        $this->authorizeNightAccess($group, $night);
        $night->load(['attendees.groupPlayer', 'attendees.user', 'images', 'creator', 'comments.user']);
        $members = $group->members()->where('isActive', true)->get();
        $players = $group->players()->get();

        // Self-heal: link any attendees that have user_id but no group_player_id
        $healed = false;
        $night->attendees->whereNull('group_player_id')->whereNotNull('user_id')->each(function ($att) use ($group, &$healed) {
            $player = GroupPlayer::firstOrCreate(
                ['group_id' => $group->id, 'user_id' => $att->user_id],
                ['name' => $att->user?->username ?? 'Unknown', 'role' => 'CORE', 'email' => $att->user?->email]
            );
            $att->update(['group_player_id' => $player->id]);
            $att->setRelation('groupPlayer', $player);
            $healed = true;
        });
        // Reload the roster so any newly-created GroupPlayers are included
        if ($healed) {
            $players = $group->players()->get();
        }

        // Current user's RSVP: find attendee by group_player_id (if linked) or user_id
        $userId = auth()->id();
        $myPlayerIds = $group->players()->where('user_id', $userId)->pluck('id');
        $myRsvp = $night->attendees
            ->first(fn($a) => in_array($a->group_player_id, $myPlayerIds->all()) || $a->user_id === $userId)
            ?->rsvp;

        // Build attended list: placed first (in order), then GOING RSVPs, then self-reported (no placement, no rsvp)
        $seen = [];
        $attended = [];
        $sources = [
            $night->attendees->whereNotNull('placement')->sortBy('placement'),
            $night->attendees->where('rsvp', 'GOING'),
            $night->attendees->whereNull('placement')->whereNull('rsvp'),
        ];
        foreach ($sources as $collection) {
            foreach ($collection as $att) {
                $p = $att->groupPlayer;
                if ($p && ! in_array($p->id, $seen)) {
                    $seen[] = $p->id;
                    $attended[] = [
                        'id'        => $p->id,
                        'name'      => $p->displayName(),
                        'photo_url' => $p->photoUrl(),
                        'initial'   => $p->initial(),
                    ];
                }
            }
        }

        // Everyone else on the roster is in the "absent" pool for the owner to pull from
        $absent = $players
            ->filter(fn($p) => ! in_array($p->id, $seen))
            ->map(fn($p) => [
                'id'        => $p->id,
                'name'      => $p->displayName(),
                'photo_url' => $p->photoUrl(),
                'initial'   => $p->initial(),
            ])->values()->all();

        $myPlayer   = $players->first(fn($p) => $p->user_id === $userId);
        $myAttendee = $myPlayer
            ? $night->attendees->first(fn($a) => $a->group_player_id === $myPlayer->id)
            : null;

        return view('groups.nights.show', compact('group', 'night', 'members', 'players', 'myRsvp', 'attended', 'absent', 'myPlayer', 'myAttendee'));
    }

    public function edit(PokerGroup $group, PokerNight $night)
    {
        $this->authorizeNightAccess($group, $night);
        $members = $group->members()->where('isActive', true)->get();
        return view('groups.nights.edit', compact('group', 'night', 'members'));
    }

    public function update(Request $request, PokerGroup $group, PokerNight $night)
    {
        $this->authorizeNightAccess($group, $night);

        $data = $request->validate([
            'title'        => ['required', 'string', 'max:100'],
            'scheduled_at' => ['required', 'date'],
            'played_at'    => ['nullable', 'date'],
            'status'       => ['required', 'in:SCHEDULED,COMPLETED,CANCELLED'],
            'buy_in'       => ['nullable', 'numeric', 'min:0'],
            'notes'        => ['nullable', 'string', 'max:1000'],
        ]);

        $night->update($data);

        return redirect()->route('nights.show', [$group, $night])->with('success', 'Night updated!');
    }

    public function destroy(PokerGroup $group, PokerNight $night)
    {
        abort_if($night->group_id !== $group->id, 404);
        if ($group->owner_id !== Auth::id() && ! Auth::user()->isAdmin()) {
            abort(403);
        }

        $night->load('images');
        foreach ($night->images as $image) {
            Storage::disk('public')->delete($image->file_path);
        }

        // Delete related records (no DB cascades defined)
        $night->images()->delete();
        $night->attendees()->delete();
        $night->comments()->delete();
        $night->delete();

        return redirect()->route('groups.show', $group)->with('success', 'Game night deleted.');
    }

    private function authorizeMember(PokerGroup $group): void
    {
        $isMember = GroupMember::where('group_id', $group->id)->where('user_id', Auth::id())->exists();
        if (! $isMember && ! Auth::user()->isAdmin()) {
            abort(403);
        }
    }

    private function authorizeNightAccess(PokerGroup $group, PokerNight $night): void
    {
        abort_if($night->group_id !== $group->id, 404);
        $this->authorizeMember($group);
    }
}
