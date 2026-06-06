<?php

namespace App\Http\Controllers;

use App\Models\GameAttendee;
use App\Models\GroupMember;
use App\Models\GroupPlayer;
use App\Models\PokerGroup;
use App\Models\PokerNight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GameAttendeeController extends Controller
{
    public function store(Request $request, PokerGroup $group, PokerNight $night)
    {
        abort_if($night->group_id !== $group->id, 404);

        $isMember = GroupMember::where('group_id', $group->id)->where('user_id', Auth::id())->exists();
        if (! $isMember && ! Auth::user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'placements'   => ['present', 'array'],
            'placements.*' => ['string', 'exists:group_players,id'],
        ]);

        $placements = $request->input('placements', []);

        // Preserve existing RSVPs before wiping
        $existingRsvps = GameAttendee::where('poker_night_id', $night->id)
            ->whereNotNull('rsvp')
            ->get()
            ->keyBy(fn($a) => $a->group_player_id ?? $a->user_id);

        // Collect RSVP-only records not in the placement list (keep their RSVPs)
        $rsvpOnly = GameAttendee::where('poker_night_id', $night->id)
            ->whereNotNull('rsvp')
            ->whereNotIn('group_player_id', $placements)
            ->get();

        GameAttendee::where('poker_night_id', $night->id)->delete();

        // Insert placed players in order — index 0 = 1st place (winner)
        foreach ($placements as $index => $groupPlayerId) {
            $player = GroupPlayer::find($groupPlayerId);
            if (! $player) continue;

            $rsvp = $existingRsvps[$groupPlayerId]?->rsvp
                ?? ($player->user_id ? ($existingRsvps[$player->user_id]?->rsvp ?? null) : null);

            GameAttendee::create([
                'poker_night_id'  => $night->id,
                'group_player_id' => $player->id,
                'user_id'         => $player->user_id,
                'placement'       => $index + 1,
                'rsvp'            => $rsvp,
            ]);
        }

        // Re-insert RSVP-only attendees who didn't attend (preserve their RSVP record)
        foreach ($rsvpOnly as $att) {
            if ($att->group_player_id) {
                GameAttendee::create([
                    'poker_night_id'  => $night->id,
                    'group_player_id' => $att->group_player_id,
                    'user_id'         => $att->user_id,
                    'rsvp'            => $att->rsvp,
                ]);
            }
        }

        if (count($placements) > 0 && $night->status === 'SCHEDULED') {
            $night->update(['status' => 'COMPLETED', 'played_at' => now()]);
        }

        return back()->with('success', 'Results saved!');
    }
}
