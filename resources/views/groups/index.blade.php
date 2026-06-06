@extends('layouts.main')
@section('title', 'My Groups – Poker Night')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-white">My Groups</h1>
    <a href="{{ route('groups.create') }}" class="btn btn-gold">+ New Group</a>
</div>

@if($groups->isNotEmpty())
<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($groups as $group)
    <a href="{{ route('groups.show', $group) }}" class="card overflow-hidden hover:border-yellow-600 transition-colors block group">
        @php $latest = $group->pokerNights->first(); @endphp
        <div class="h-32 flex items-center justify-center text-5xl" style="background-color: var(--color-felt);">
            @if($latest?->coverImage)
                <img src="{{ $latest->coverImage->url() }}" alt="" class="w-full h-full object-cover">
            @else
                <span class="suit suit-spade opacity-40">♠</span>
            @endif
        </div>
        <div class="p-4">
            <h3 class="font-bold text-white mb-1 group-hover:text-yellow-400 transition-colors">{{ $group->name }}</h3>
            <p class="text-gray-400 text-xs mb-3">{{ $group->memberships->count() }} {{ Str::plural('member', $group->memberships->count()) }}</p>
            @if($latest)
                <div class="text-xs text-gray-500">Last night: {{ $latest->scheduled_at->format('M j') }}
                    @if($latest->winner)
                        · 🏆 {{ $latest->winner->user->username }}
                    @endif
                </div>
            @endif
            <div class="flex items-center gap-2 mt-3">
                <span class="text-xs font-mono px-2 py-0.5 rounded" style="background-color: var(--color-surface); color: var(--color-gold); border: 1px solid var(--color-border);">
                    {{ $group->invite_code }}
                </span>
                <span class="text-xs text-gray-500">invite code</span>
            </div>
        </div>
    </a>
    @endforeach
</div>
@else
<div class="card p-12 text-center">
    <div class="text-5xl mb-4"><span class="suit suit-diamond">♦</span></div>
    <h3 class="text-lg font-semibold mb-2 text-white">No groups yet</h3>
    <p class="text-gray-400 text-sm mb-4">Create one or join with an invite code.</p>
    <div class="flex gap-3 justify-center">
        <a href="{{ route('groups.create') }}" class="btn btn-gold">Create a Group</a>
    </div>
</div>
@endif

{{-- Join by invite code --}}
<div class="card p-5 mt-6" x-data="{ code: '' }">
    <h2 class="font-semibold text-white mb-3">Join with Invite Code</h2>
    <form @submit.prevent="window.location = '/groups/join/' + code.trim().toUpperCase()" class="flex gap-2">
        <input x-model="code" type="text" class="input flex-1" placeholder="Enter 8-char code" maxlength="10">
        <button type="submit" class="btn btn-primary" :disabled="code.trim().length < 3">Join</button>
    </form>
</div>
@endsection
