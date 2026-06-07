<?php

namespace App\Http\Controllers;

use App\Models\PokerGroup;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $groups = PokerGroup::whereHas('memberships', fn ($q) => $q->where('user_id', Auth::id()))
            ->where('isActive', true)
            ->with(['memberships', 'pokerNights' => fn ($q) => $q->limit(8)->with('coverImage', 'winner.user')])
            ->get();

        return view('dashboard', compact('groups'));
    }
}
