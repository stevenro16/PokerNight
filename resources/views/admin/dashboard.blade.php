@extends('layouts.main')
@section('title', 'Admin Dashboard – Poker Night')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-white">
        <span style="color: var(--color-gold);">Admin</span> Dashboard
    </h1>
</div>

<div class="grid sm:grid-cols-3 gap-4 mb-6">
    <div class="card p-5 text-center">
        <div class="text-3xl font-bold text-white">{{ $userCount }}</div>
        <div class="text-gray-400 text-sm mt-1">Total Users</div>
    </div>
    <div class="card p-5 text-center">
        <div class="text-3xl font-bold" style="color: var(--color-gold);">{{ $adminCount }}</div>
        <div class="text-gray-400 text-sm mt-1">Admins</div>
    </div>
    <div class="card p-5 text-center">
        <div class="text-3xl font-bold text-green-400">{{ $activeCount }}</div>
        <div class="text-gray-400 text-sm mt-1">Active</div>
    </div>
</div>

<a href="{{ route('admin.users.index') }}" class="btn btn-primary">Manage Users →</a>
@endsection
