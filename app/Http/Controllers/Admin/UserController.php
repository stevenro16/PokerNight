<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class UserController extends Controller
{
    public function dashboard()
    {
        $userCount   = User::count();
        $adminCount  = User::whereIn('role', ['ADMIN', 'SUPERADMIN'])->count();
        $activeCount = User::where('isActive', true)->count();
        return view('admin.dashboard', compact('userCount', 'adminCount', 'activeCount'));
    }

    public function index()
    {
        $users = User::orderBy('createdAt', 'desc')->paginate(30);
        return view('admin.users', compact('users'));
    }

    public function toggle(User $user)
    {
        $user->update(['isActive' => ! $user->isActive]);
        return back()->with('success', 'User status updated.');
    }

    public function setRole(User $user)
    {
        request()->validate(['role' => ['required', 'in:USER,ADMIN,SUPERADMIN']]);
        $user->update(['role' => request('role')]);
        return back()->with('success', 'Role updated.');
    }
}
