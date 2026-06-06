@extends('layouts.main')
@section('title', $group->name . ' – Poker Night')

@section('content')

{{-- Group Header --}}
<div class="flex flex-wrap items-start justify-between gap-4 mb-6">
    <div>
        <h1 class="text-3xl font-bold text-white">{{ $group->name }}</h1>
        @if($group->description)
            <p class="text-gray-400 mt-1">{{ $group->description }}</p>
        @endif
        <div class="flex flex-wrap gap-3 mt-2 text-sm text-gray-400">
            <span>{{ $members->count() }} {{ Str::plural('member', $members->count()) }}</span>
            <span>·</span>
            <span>{{ $nights->count() }} {{ Str::plural('night', $nights->count()) }}</span>
            <span>·</span>
            <span>Invite: <span class="font-mono" style="color: var(--color-gold);">{{ $group->invite_code }}</span></span>
        </div>
    </div>
    <div class="flex gap-2 flex-wrap">
        <a href="{{ route('groups.leaderboard', $group) }}" class="btn btn-ghost text-sm">🏆 Leaderboard</a>
        @if(auth()->id() === $group->owner_id || auth()->user()->isAdmin())
            <a href="{{ route('players.index', $group) }}" class="btn btn-ghost text-sm">👥 Roster</a>
        @endif
        <a href="{{ route('nights.create', $group) }}" class="btn btn-gold text-sm">+ Schedule Night</a>
    </div>
</div>

<div class="flex gap-6 items-start">

    {{-- Poker Night Cards (3 per row) --}}
    <div class="flex-1 min-w-0">
        @if($nights->isNotEmpty())
        <div style="display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:0.75rem;">
            @foreach($nights as $night)
            @php
                $imageUrls  = $night->images->sortBy('sort_order')->map(fn($i) => $i->url())->values();
                $winner     = $night->attendees->where('placement', 1)->first();
                $winnerName = $winner?->groupPlayer?->displayName() ?? $winner?->user?->username;
            @endphp
            <div x-data="imageCarousel({{ Js::from($imageUrls) }})"
                style="position: relative; overflow: hidden; border-radius: 0.75rem; background-color: var(--color-card-bg); border: 1px solid var(--color-border);">

                {{-- Image area --}}
                <div style="height: 360px; overflow: hidden; background-color: var(--color-felt); position: relative;">

                    @if($imageUrls->isNotEmpty())
                        <img src="{{ $imageUrls->first() }}" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:top;" x-cloak>
                        <img :src="images[current]" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:top;" class="transition-opacity duration-700">
                    @else
                        <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;">
                            <span class="suit suit-spade text-4xl opacity-20">♠</span>
                        </div>
                    @endif

                    <span class="absolute top-1.5 left-1.5 badge {{ $night->status === 'COMPLETED' ? 'badge-green' : ($night->status === 'CANCELLED' ? 'badge-red' : 'badge-gray') }} text-xs">
                        {{ $night->status }}
                    </span>

                    <template x-if="images.length > 1">
                        <div style="position:absolute;bottom:3rem;left:50%;transform:translateX(-50%);display:flex;gap:4px;">
                            <template x-for="(_, i) in images" :key="i">
                                <div style="width:4px;height:4px;border-radius:9999px;transition:background-color 0.2s;"
                                    :style="i === current ? 'background:white;' : 'background:rgba(255,255,255,0.35);'"></div>
                            </template>
                        </div>
                    </template>
                </div>

                {{-- Banner: positioned relative to the card, always at the bottom, full width --}}
                <div style="position:absolute;bottom:0;left:0;right:0;padding:6px 10px;background:rgba(14,14,14,0.82);z-index:10;">
                    <h3 style="font-size:0.75rem;font-weight:600;color:white;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;line-height:1.3;">
                        <a href="{{ route('nights.show', [$group, $night]) }}" style="color:inherit;" onmouseover="this.style.color='#facc15'" onmouseout="this.style.color='white'">
                            {{ $night->title }}
                        </a>
                    </h3>
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:4px;margin-top:2px;">
                        <span style="font-size:0.75rem;color:#d1d5db;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;min-width:0;">
                            {{ $night->scheduled_at->format('M j, Y') }}
                            @if($winner && $winnerName) · 🏆 {{ $winnerName }} @endif
                        </span>
                        <div style="display:flex;gap:2px;flex-shrink:0;">
                            <a href="{{ route('nights.show', [$group, $night]) }}" class="btn btn-ghost text-xs py-0 px-1.5">View</a>
                            <a href="{{ route('nights.edit', [$group, $night]) }}" class="btn btn-ghost text-xs py-0 px-1.5">Edit</a>
                            @if(auth()->id() === $group->owner_id || auth()->user()->isAdmin())
                            <form method="POST" action="{{ route('nights.destroy', [$group, $night]) }}"
                                onsubmit="return confirm('Delete this game night? This cannot be undone.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-ghost text-xs py-0 px-1.5" style="color:#f87171;">Del</button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="card p-10 text-center">
            <div class="text-5xl mb-3 suit suit-club opacity-30">♣</div>
            <h3 class="font-semibold text-white mb-2">No nights yet</h3>
            <p class="text-gray-400 text-sm mb-4">Schedule your first poker night to get started.</p>
            <a href="{{ route('nights.create', $group) }}" class="btn btn-gold">Schedule Night</a>
        </div>
        @endif
    </div>

    {{-- Sidebar: Roster --}}
    <div class="w-64 shrink-0">
        <div class="card p-3">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold text-white text-sm">Roster</h3>
                <span class="text-xs text-gray-500">{{ $players->count() }}</span>
            </div>

            @if($players->isNotEmpty())
            <div class="space-y-2">
                @foreach($players as $player)
                <div class="flex items-center gap-2">
                    {{-- Photo or initial --}}
                    @if($player->photo_path)
                        <img src="{{ $player->photoUrl() }}" alt="{{ $player->displayName() }}"
                            class="w-16 h-16 rounded-full object-cover shrink-0">
                    @else
                        <div class="w-16 h-16 rounded-full flex items-center justify-center text-lg font-bold shrink-0"
                            style="background-color: var(--color-felt); color: var(--color-gold);">
                            {{ $player->initial() }}
                        </div>
                    @endif

                    {{-- Name + nickname --}}
                    <div class="min-w-0 flex-1">
                        <div class="text-xs font-semibold text-white truncate">{{ $player->name }}</div>
                        @if($player->nickname)
                            <div class="text-xs truncate" style="color: var(--color-gold);">"{{ $player->nickname }}"</div>
                        @endif
                    </div>

                    {{-- Wins --}}
                    @if($player->wins > 0)
                        <span class="shrink-0 text-xs font-bold" style="color: var(--color-gold);">{{ $player->wins }}W</span>
                    @else
                        <span class="shrink-0 text-xs text-gray-600">—</span>
                    @endif
                </div>
                @endforeach
            </div>
            @else
            <p class="text-xs text-gray-600 text-center py-2">No players yet</p>
            @endif

            @php
                $myPlayer = $players->first(fn($p) => $p->user_id === auth()->id());
            @endphp
            @if($myPlayer)
            <a href="{{ route('players.edit', [$group, $myPlayer]) }}"
                class="block text-center text-xs mt-3 pt-3 transition-colors"
                style="color: var(--color-gold); border-top: 1px solid var(--color-border);">
                Edit my group profile →
            </a>
            @endif
            @if(auth()->id() === $group->owner_id || auth()->user()->isAdmin())
            <a href="{{ route('players.index', $group) }}"
                class="block text-center text-xs {{ $myPlayer ? 'mt-1' : 'mt-3 pt-3' }} transition-colors"
                style="color: var(--color-gold); {{ $myPlayer ? '' : 'border-top: 1px solid var(--color-border);' }}">
                Manage Roster →
            </a>
            @endif
        </div>
    </div>

</div>
@endsection
