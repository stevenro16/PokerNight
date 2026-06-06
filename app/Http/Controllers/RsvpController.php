<?php

namespace App\Http\Controllers;

use App\Models\GameAttendee;
use App\Models\GroupMember;
use App\Models\GroupPlayer;
use App\Models\PokerGroup;
use App\Models\PokerNight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RsvpController extends Controller
{
    public function update(Request $request, PokerGroup $group, PokerNight $night)
    {
        abort_if($night->group_id !== $group->id, 404);

        $isMember = GroupMember::where('group_id', $group->id)->where('user_id', Auth::id())->exists();
        if (! $isMember) {
            abort(403);
        }

        $request->validate([
            'rsvp' => ['required', 'in:GOING,NOT_GOING,MAYBE'],
        ]);

        // Find or auto-create the group_player record linked to this user in this group
        $user = Auth::user();
        $player = GroupPlayer::where('group_id', $group->id)->where('user_id', $user->id)->first();
        if (! $player) {
            $player = GroupPlayer::create([
                'group_id' => $group->id,
                'user_id'  => $user->id,
                'name'     => $user->username,
                'role'     => 'CORE',
                'email'    => $user->email,
            ]);
        }

        // Look up existing attendee record by group_player_id or user_id
        $attendee = GameAttendee::where('poker_night_id', $night->id)
            ->where(function ($q) use ($player) {
                if ($player) {
                    $q->where('group_player_id', $player->id)
                      ->orWhere('user_id', Auth::id());
                } else {
                    $q->where('user_id', Auth::id());
                }
            })
            ->first();

        if ($attendee) {
            $attendee->update(['rsvp' => $request->rsvp]);
        } else {
            GameAttendee::create([
                'poker_night_id'  => $night->id,
                'group_player_id' => $player?->id,
                'user_id'         => Auth::id(),
                'rsvp'            => $request->rsvp,
            ]);
        }

        return back();
    }
}
