<?php

namespace App\Http\Controllers;

use App\Models\PokerNight;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $groupIds = $user->memberships()->pluck('group_id');

        $recentNights = PokerNight::whereIn('group_id', $groupIds)
            ->with(['group', 'coverImage', 'winner.groupPlayer', 'winner.user'])
            ->orderByDesc('scheduled_at')
            ->limit(5)
            ->get();

        $groupCount = $groupIds->count();

        return view('dashboard', compact('recentNights', 'groupCount'));
    }
}
