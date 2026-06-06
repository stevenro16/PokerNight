<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\PokerGroup;

class GroupController extends Controller
{
    public function dashboard()
    {
        $total   = PokerGroup::count();
        $active  = PokerGroup::where('isActive', true)->count();
        $removed = PokerGroup::where('isActive', false)->count();
        return view('superadmin.dashboard', compact('total', 'active', 'removed'));
    }

    public function index()
    {
        $groups = PokerGroup::with(['owner', 'memberships'])
            ->withCount(['memberships', 'pokerNights'])
            ->orderBy('createdAt', 'desc')
            ->paginate(30);
        return view('superadmin.groups', compact('groups'));
    }

    public function toggle(PokerGroup $group)
    {
        $group->update(['isActive' => ! $group->isActive]);
        $msg = $group->isActive ? 'Group restored.' : 'Group taken down.';
        return back()->with('success', $msg);
    }
}
