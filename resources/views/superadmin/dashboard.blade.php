@extends('layouts.main')
@section('title', 'Super Admin – Poker Night')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold">
        <span class="text-purple-400">Super Admin</span> Dashboard
    </h1>
</div>

<div class="grid sm:grid-cols-3 gap-4 mb-6">
    <div class="card p-5 text-center">
        <div class="text-3xl font-bold text-white">{{ $total }}</div>
        <div class="text-gray-400 text-sm mt-1">Total Groups</div>
    </div>
    <div class="card p-5 text-center">
        <div class="text-3xl font-bold text-green-400">{{ $active }}</div>
        <div class="text-gray-400 text-sm mt-1">Active</div>
    </div>
    <div class="card p-5 text-center">
        <div class="text-3xl font-bold text-red-400">{{ $removed }}</div>
        <div class="text-gray-400 text-sm mt-1">Removed</div>
    </div>
</div>

<div class="flex gap-3">
    <a href="{{ route('superadmin.groups.index') }}" class="btn btn-primary">Moderate Groups →</a>
    <a href="{{ route('admin.dashboard') }}" class="btn btn-ghost">Admin Panel</a>
</div>
@endsection
