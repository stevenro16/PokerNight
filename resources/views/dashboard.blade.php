@extends('layouts.main')
@section('title', 'Dashboard – Poker Night')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-white">Welcome back, <span style="color: var(--color-gold);">{{ auth()->user()->username }}</span></h1>
        <p class="text-gray-400 text-sm mt-1">You're in {{ $groupCount }} {{ Str::plural('group', $groupCount) }}</p>
    </div>
    <a href="{{ route('groups.create') }}" class="btn btn-primary">+ New Group</a>
</div>

<div class="grid md:grid-cols-2 gap-4 mb-8">
    <a href="{{ route('groups.index') }}" class="card p-5 hover:border-yellow-600 transition-colors group">
        <div class="text-3xl suit suit-spade mb-2 group-hover:scale-110 transition-transform">♠</div>
        <div class="font-semibold text-white">My Groups</div>
        <div class="text-gray-400 text-sm">{{ $groupCount }} {{ Str::plural('group', $groupCount) }}</div>
    </a>
    <div class="card p-5">
        <div class="text-3xl suit suit-heart mb-2">♥</div>
        <div class="font-semibold text-white">Recent Activity</div>
        <div class="text-gray-400 text-sm">{{ $recentNights->count() }} recent nights</div>
    </div>
</div>

@if($recentNights->isNotEmpty())
<h2 class="text-lg font-semibold mb-3 text-white">Recent Poker Nights</h2>
<div class="space-y-3">
    @foreach($recentNights as $night)
    <a href="{{ route('nights.show', [$night->group_id, $night]) }}" class="card p-4 flex items-center gap-4 hover:border-yellow-600 transition-colors">
        @if($night->coverImage)
            <img src="{{ $night->coverImage->url() }}" alt="" class="w-16 h-16 object-cover rounded-lg shrink-0">
        @else
            <div class="w-16 h-16 rounded-lg flex items-center justify-center shrink-0 text-2xl suit suit-club" style="background-color: var(--color-felt);">♣</div>
        @endif
        <div class="flex-1 min-w-0">
            <div class="font-semibold text-white truncate">{{ $night->title }}</div>
            <div class="text-sm text-gray-400">{{ $night->group->name }} · {{ $night->scheduled_at->format('M j, Y') }}</div>
            @if($night->winner)
                @php $winnerName = $night->winner->groupPlayer?->displayName() ?? $night->winner->user?->username; @endphp
                @if($winnerName)
                <div class="text-xs mt-1">
                    <span class="badge badge-gold">🏆 {{ $winnerName }}</span>
                </div>
                @endif
            @endif
        </div>
        <span class="badge {{ $night->status === 'COMPLETED' ? 'badge-green' : ($night->status === 'CANCELLED' ? 'badge-red' : 'badge-gray') }}">
            {{ $night->status }}
        </span>
    </a>
    @endforeach
</div>
@else
<div class="card p-12 text-center">
    <div class="text-5xl mb-4 space-x-2">
        <span class="suit suit-spade">♠</span><span class="suit suit-heart">♥</span>
    </div>
    <h3 class="text-lg font-semibold mb-2 text-white">No nights recorded yet</h3>
    <p class="text-gray-400 text-sm mb-4">Create a group and schedule your first poker night.</p>
    <a href="{{ route('groups.create') }}" class="btn btn-gold">Create a Group</a>
</div>
@endif
@endsection
