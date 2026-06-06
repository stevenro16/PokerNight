<?php

namespace App\Http\Controllers;

use App\Models\GameAttendee;
use App\Models\GroupMember;
use App\Models\PokerGroup;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller
{
    public function show(PokerGroup $group)
    {
        $isMember = GroupMember::where('group_id', $group->id)->where('user_id', Auth::id())->exists();
        if (! $isMember && ! Auth::user()->isAdmin()) {
            abort(403);
        }

        $nightIds = $group->pokerNights()->pluck('id');

        // Aggregate by group_player_id first, fall back to user_id for legacy records
        $rows = DB::table('game_attendees')
            ->whereIn('poker_night_id', $nightIds)
            ->select(
                DB::raw('COALESCE(group_player_id, user_id) as player_key'),
                'group_player_id',
                'user_id',
                DB::raw('COUNT(*) as games_played'),
                DB::raw('SUM(CASE WHEN placement = 1 THEN 1 ELSE 0 END) as wins')
            )
            ->groupBy(DB::raw('COALESCE(group_player_id, user_id)'))
            ->orderByDesc('wins')
            ->orderByDesc('games_played')
            ->get();

        // Resolve display names
        $playerIds = $rows->pluck('group_player_id')->filter()->unique()->values();
        $userIds   = $rows->pluck('user_id')->filter()->unique()->values();

        $players = \App\Models\GroupPlayer::whereIn('id', $playerIds)->get()->keyBy('id');
        $users   = \App\Models\User::whereIn('id', $userIds)->get()->keyBy('id');

        $stats = $rows->map(function ($row) use ($players, $users) {
            $gp = $row->group_player_id ? ($players[$row->group_player_id] ?? null) : null;
            $u  = $row->user_id ? ($users[$row->user_id] ?? null) : null;

            return (object) [
                'display_name' => $gp ? $gp->displayName() : ($u?->username ?? 'Unknown'),
                'photo_url'    => $gp?->photoUrl(),
                'initial'      => $gp ? $gp->initial() : strtoupper(substr($u?->username ?? '?', 0, 1)),
                'role'         => $gp?->role,
                'is_linked'    => $gp ? $gp->isLinked() : true,
                'wins'         => (int) $row->wins,
                'games_played' => (int) $row->games_played,
            ];
        });

        return view('groups.leaderboard', compact('group', 'stats'));
    }
}
