@extends('layouts.main')
@section('title', $group->name . ' Leaderboard – Poker Night')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <a href="{{ route('groups.show', $group) }}" class="text-gray-400 hover:text-white text-sm transition-colors">← {{ $group->name }}</a>
        <h1 class="text-2xl font-bold text-white mt-1">🏆 Season Leaderboard</h1>
    </div>
</div>

@if($stats->isNotEmpty())
<div class="card overflow-hidden">
    <table class="w-full">
        <thead>
            <tr style="border-bottom: 1px solid var(--color-border);">
                <th class="text-left px-4 py-3 text-xs uppercase tracking-wider text-gray-500 w-10">#</th>
                <th class="text-left px-4 py-3 text-xs uppercase tracking-wider text-gray-500">Player</th>
                <th class="text-center px-4 py-3 text-xs uppercase tracking-wider text-gray-500">Wins</th>
                <th class="text-center px-4 py-3 text-xs uppercase tracking-wider text-gray-500">Played</th>
                <th class="text-center px-4 py-3 text-xs uppercase tracking-wider text-gray-500">Win %</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stats as $i => $row)
            <tr style="border-bottom: 1px solid var(--color-border);" class="{{ $i === 0 ? 'bg-yellow-900/10' : '' }}">
                <td class="px-4 py-3 text-sm font-bold {{ $i === 0 ? 'text-yellow-400' : 'text-gray-500' }}">
                    @if($i === 0) 🥇 @elseif($i === 1) 🥈 @elseif($i === 2) 🥉 @else {{ $i + 1 }} @endif
                </td>
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        @if($row->photo_url)
                            <img src="{{ $row->photo_url }}" class="w-8 h-8 rounded-full object-cover shrink-0">
                        @else
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold shrink-0"
                                style="background-color: var(--color-felt); color: var(--color-gold);">
                                {{ $row->initial }}
                            </div>
                        @endif
                        <div>
                            <span class="font-medium text-white">{{ $row->display_name }}</span>
                            @if($row->role === 'GUEST')
                                <span class="text-xs text-gray-500 ml-1">(guest)</span>
                            @endif
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="font-bold text-lg {{ $i === 0 ? 'text-yellow-400' : 'text-white' }}">{{ $row->wins }}</span>
                </td>
                <td class="px-4 py-3 text-center text-gray-400 text-sm">{{ $row->games_played }}</td>
                <td class="px-4 py-3 text-center text-gray-400 text-sm">
                    {{ $row->games_played > 0 ? number_format(($row->wins / $row->games_played) * 100, 0) : 0 }}%
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@else
<div class="card p-12 text-center">
    <div class="text-5xl mb-4 suit suit-spade opacity-30">♠</div>
    <h3 class="font-semibold text-white mb-2">No data yet</h3>
    <p class="text-gray-400 text-sm">Record some poker nights and mark winners to build the leaderboard.</p>
</div>
@endif
@endsection
