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
    @php
        $latest    = $group->pokerNights->first();
        $nightImgs = $group->pokerNights->map(fn($n) => $n->coverImage?->url())->filter()->values()->take(5);
        $imgUrls   = $group->avatarUrl() ? collect([$group->avatarUrl()])->concat($nightImgs) : $nightImgs;
        $hasImages = $imgUrls->isNotEmpty();
    @endphp
    <a href="{{ route('groups.show', $group) }}"
       class="card overflow-hidden hover:border-yellow-600 transition-colors block group relative" style="height: 19.2rem"
       x-data="{ idx: 0 }"
       x-init="if ({{ $imgUrls->count() }} > 1) setInterval(() => idx = (idx + 1) % {{ $imgUrls->count() }}, 3500)">

        {{-- Images rendered server-side; Alpine controls which is visible --}}
        @if($hasImages)
            @foreach($imgUrls as $i => $imgUrl)
                <img src="{{ $imgUrl }}" alt=""
                     class="absolute inset-0 w-full h-full object-cover transition-opacity duration-700"
                     style="{{ $i > 0 ? 'opacity:0' : '' }}"
                     :class="idx === {{ $i }} ? 'opacity-100' : 'opacity-0'">
            @endforeach
        @else
            <div class="absolute inset-0 flex items-center justify-center text-6xl" style="background-color: var(--color-felt);">
                <span class="suit suit-spade opacity-40">♠</span>
            </div>
        @endif

        {{-- Bottom banner: 25% see-through overlay --}}
        <div class="absolute bottom-0 left-0 right-0 px-3 py-2"
             style="background-color: rgba(28, 28, 46, 0.75); backdrop-filter: blur(2px);">
            <div class="flex items-center justify-between gap-2">
                <h3 class="font-bold text-white text-sm truncate group-hover:text-yellow-400 transition-colors">{{ $group->name }}</h3>
                <span class="text-xs font-mono shrink-0 px-1.5 py-0.5 rounded" style="background-color: rgba(20,20,20,0.6); color: var(--color-gold); border: 1px solid var(--color-border);">
                    {{ $group->invite_code }}
                </span>
            </div>
            <p class="text-gray-300 text-xs mt-0.5">
                {{ $group->memberships->count() }} {{ Str::plural('member', $group->memberships->count()) }}
                @if($latest)
                    · Last: {{ $latest->scheduled_at->format('M j') }}
                    @if($latest->winner)
                        · 🏆 {{ $latest->winner->user->username }}
                    @endif
                @endif
            </p>
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
