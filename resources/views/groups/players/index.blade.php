@extends('layouts.main')
@section('title', 'Roster – ' . $group->name)

@section('content')
<div class="flex items-center gap-2 mb-1 text-sm text-gray-400">
    <a href="{{ route('groups.show', $group) }}" class="hover:text-white transition-colors">{{ $group->name }}</a>
    <span>›</span>
    <span class="text-white">Roster</span>
</div>

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-white">Player Roster</h1>
        <p class="text-gray-400 text-sm mt-1">{{ $players->count() }} {{ Str::plural('player', $players->count()) }}</p>
    </div>
    <a href="{{ route('players.create', $group) }}" class="btn btn-gold">+ Add Player</a>
</div>

@if($players->isNotEmpty())
    {{-- Core Members --}}
    @php $core = $players->where('role', 'CORE'); $guests = $players->where('role', 'GUEST'); @endphp

    @if($core->isNotEmpty())
    <h2 class="text-sm font-semibold uppercase tracking-wider mb-3" style="color: var(--color-gold);">Core Members</h2>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        @foreach($core as $player)
            @include('groups.players._card', ['player' => $player, 'group' => $group])
        @endforeach
    </div>
    @endif

    @if($guests->isNotEmpty())
    <h2 class="text-sm font-semibold uppercase tracking-wider mb-3 text-gray-400">Guests</h2>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($guests as $player)
            @include('groups.players._card', ['player' => $player, 'group' => $group])
        @endforeach
    </div>
    @endif
@else
    <div class="card p-12 text-center">
        <div class="text-5xl mb-4 suit suit-diamond opacity-30">♦</div>
        <h3 class="text-lg font-semibold mb-2 text-white">No players yet</h3>
        <p class="text-gray-400 text-sm mb-4">Add players to build your group's roster. They don't need an account.</p>
        <a href="{{ route('players.create', $group) }}" class="btn btn-gold">Add First Player</a>
    </div>
@endif
@endsection
