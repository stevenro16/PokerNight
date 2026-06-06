@extends('layouts.main')
@section('title', $night->title . ' – Poker Night')

@section('content')
<div class="flex items-center gap-2 mb-1 text-sm text-gray-400">
    <a href="{{ route('groups.show', $group) }}" class="hover:text-white transition-colors">{{ $group->name }}</a>
    <span>›</span>
    <span class="text-white">{{ $night->title }}</span>
</div>

<div class="flex flex-wrap items-start justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-white">{{ $night->title }}</h1>
        <p class="text-gray-400 text-sm mt-1">
            {{ $night->scheduled_at->format('l, F j, Y \a\t g:i A') }}
            @if($night->buy_in) · ${{ number_format($night->buy_in, 2) }} buy-in @endif
        </p>
    </div>
    <div class="flex gap-2 items-center">
        <a href="{{ route('nights.edit', [$group, $night]) }}" class="btn btn-ghost text-sm">Edit</a>
        @if(auth()->id() === $group->owner_id || auth()->user()->isAdmin())
        <form method="POST" action="{{ route('nights.destroy', [$group, $night]) }}"
            onsubmit="return confirm('Delete this game night? This cannot be undone.')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-ghost text-sm" style="color:#f87171;">Delete</button>
        </form>
        @endif
        <span class="badge {{ $night->status === 'COMPLETED' ? 'badge-green' : ($night->status === 'CANCELLED' ? 'badge-red' : 'badge-gray') }} self-center">
            {{ $night->status }}
        </span>
    </div>
</div>

@if($night->notes)
    <div class="card p-4 mb-6">
        <p class="text-gray-300 text-sm">{{ $night->notes }}</p>
    </div>
@endif

{{-- RSVP bar (only for SCHEDULED nights) --}}
@if($night->status === 'SCHEDULED')
<div class="card p-4 mb-6 flex flex-wrap items-center gap-4">
    <div class="flex-1 min-w-0">
        <p class="text-sm font-semibold text-white mb-1">Are you going?</p>
        @php
            $goingCount    = $night->attendees->where('rsvp', 'GOING')->count();
            $notGoingCount = $night->attendees->where('rsvp', 'NOT_GOING')->count();
            $maybeCount    = $night->attendees->where('rsvp', 'MAYBE')->count();
        @endphp
        <p class="text-xs text-gray-400">
            <span class="text-green-400 font-semibold">{{ $goingCount }} going</span>
            · <span class="text-red-400 font-semibold">{{ $notGoingCount }} not going</span>
            · <span class="text-yellow-400 font-semibold">{{ $maybeCount }} maybe</span>
        </p>
    </div>
    <div class="flex gap-2">
        @foreach(['GOING' => ['Going', 'btn-primary', '✓'], 'MAYBE' => ['Maybe', 'btn-ghost', '?'], 'NOT_GOING' => ["Can't Go", 'btn-danger', '✗']] as $value => [$label, $cls, $icon])
        <form method="POST" action="{{ route('rsvp.update', [$group, $night]) }}">
            @csrf
            <input type="hidden" name="rsvp" value="{{ $value }}">
            <button type="submit"
                class="btn {{ $cls }} text-xs py-1.5 px-3 {{ $myRsvp === $value ? 'ring-2 ring-yellow-400' : '' }}">
                {{ $icon }} {{ $label }}
            </button>
        </form>
        @endforeach
    </div>
</div>
@endif

<div class="grid lg:grid-cols-3 gap-6">

    {{-- Left: Photos --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Photo Gallery --}}
        <div>
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-semibold text-white">Photos</h2>
                <button onclick="document.getElementById('upload-form').classList.toggle('hidden')"
                    class="btn btn-ghost text-xs py-1 px-3">+ Upload</button>
            </div>

            <div id="upload-form" class="card p-4 mb-4 hidden" x-data="imageUpload()">
                <form method="POST" action="{{ route('images.store', [$group, $night]) }}"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="border-2 border-dashed rounded-lg p-6 text-center cursor-pointer transition-colors"
                        :class="dragging ? 'border-yellow-500 bg-yellow-900/10' : ''"
                        style="border-color: var(--color-border);"
                        @dragover.prevent="dragging = true"
                        @dragleave="dragging = false"
                        @drop.prevent="onDrop($event)"
                        @click="$refs.fileInput.click()">
                        <p class="text-gray-400 text-sm">Drop images here or click to select</p>
                        <p class="text-gray-600 text-xs mt-1">Max 10 MB per image</p>
                        <input x-ref="fileInput" type="file" name="images[]" multiple accept="image/*" class="hidden"
                            @change="handleFiles($event.target.files)">
                    </div>
                    <div x-show="previews.length > 0" class="flex flex-wrap gap-2 mt-3">
                        <template x-for="(p, i) in previews" :key="i">
                            <div class="relative w-20 h-20">
                                <img :src="p.url" class="w-full h-full object-cover rounded-lg">
                                <button type="button" @click.stop="removePreview(i)"
                                    class="absolute -top-1 -right-1 w-5 h-5 bg-red-600 text-white rounded-full text-xs flex items-center justify-center">×</button>
                            </div>
                        </template>
                    </div>
                    <div class="flex gap-2 mt-3">
                        <button type="submit" class="btn btn-gold text-sm" :disabled="previews.length === 0">Upload</button>
                        <button type="button" onclick="document.getElementById('upload-form').classList.add('hidden')" class="btn btn-ghost text-sm">Cancel</button>
                    </div>
                </form>
            </div>

            @if($night->images->isNotEmpty())
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    @foreach($night->images as $image)
                    <div class="relative group rounded-lg overflow-hidden aspect-square">
                        <img src="{{ $image->url() }}" alt="{{ $image->caption }}"
                            class="w-full h-full object-cover">
                        @if($image->is_cover)
                            <span class="absolute top-1 left-1 badge badge-gold text-xs">Cover</span>
                        @endif
                        <form method="POST" action="{{ route('images.destroy', $image) }}"
                            class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity"
                            onsubmit="return confirm('Remove this photo?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-6 h-6 bg-red-600 text-white rounded text-xs">×</button>
                        </form>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="card p-8 text-center">
                    <p class="text-gray-500 text-sm">No photos yet. Be the first to upload!</p>
                </div>
            @endif
        </div>

        {{-- Chat --}}
        <div>
            <h2 class="font-semibold text-white mb-3">
                Chat
                @if($night->comments->count())
                    <span class="text-gray-500 font-normal text-sm ml-1">({{ $night->comments->count() }})</span>
                @endif
            </h2>

            {{-- Messages --}}
            <div class="space-y-3 mb-4" id="chat-messages">
                @forelse($night->comments as $comment)
                <div class="flex gap-3 group">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5"
                        style="background-color: var(--color-felt); color: var(--color-gold);">
                        {{ strtoupper(substr($comment->user->username ?? '?', 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-baseline gap-2 mb-0.5">
                            <span class="text-sm font-semibold text-white">{{ $comment->user->username ?? 'Unknown' }}</span>
                            <span class="text-xs text-gray-500">{{ $comment->createdAt->diffForHumans() }}</span>
                        </div>
                        <p class="text-sm text-gray-300 break-words">{{ $comment->message }}</p>
                    </div>
                    @if($comment->user_id === auth()->id() || auth()->user()->isAdmin())
                    <form method="POST"
                        action="{{ route('comments.destroy', [$group, $night, $comment]) }}"
                        class="opacity-0 group-hover:opacity-100 transition-opacity self-start mt-1">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-gray-600 hover:text-red-400 transition-colors text-xs px-1">✕</button>
                    </form>
                    @endif
                </div>
                @empty
                <div class="text-center py-6">
                    <p class="text-gray-500 text-sm">No messages yet. Start the conversation!</p>
                </div>
                @endforelse
            </div>

            {{-- Post a message --}}
            <form method="POST" action="{{ route('comments.store', [$group, $night]) }}"
                x-data="{ msg: '', sending: false }" @submit="sending = true"
                class="flex gap-2">
                @csrf
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0"
                    style="background-color: var(--color-felt); color: var(--color-gold);">
                    {{ strtoupper(substr(auth()->user()->username, 0, 1)) }}
                </div>
                <div class="flex-1 flex gap-2">
                    <input type="text" name="message" x-model="msg"
                        class="input flex-1 text-sm"
                        placeholder="Write a message…"
                        maxlength="1000"
                        autocomplete="off">
                    <button type="submit" class="btn btn-primary text-sm px-4"
                        :disabled="sending || msg.trim().length === 0">
                        Send
                    </button>
                </div>
            </form>
            @error('message')
                <p class="form-error mt-1 ml-10">{{ $message }}</p>
            @enderror
        </div>

    </div>

    {{-- Right sidebar: Winner + Attendees + RSVP list --}}
    <div class="space-y-4">

        {{-- Winner callout --}}
        @php $winner = $night->attendees->where('placement', 1)->first(); @endphp
        @if($winner)
        @php $winnerName = $winner->groupPlayer?->displayName() ?? $winner->user?->username ?? 'Unknown'; @endphp
        <div class="card p-5 text-center" style="border-color: var(--color-gold); background: linear-gradient(135deg, #1a1a00 0%, var(--color-card-bg) 100%);">
            <div class="text-4xl mb-2">🏆</div>
            <div class="text-xs uppercase tracking-wider text-gray-400 mb-1">Winner</div>
            <div class="text-xl font-bold" style="color: var(--color-gold);">{{ $winnerName }}</div>
        </div>
        @endif

        {{-- RSVP summary --}}
        @if($night->attendees->whereNotNull('rsvp')->count())
        <div class="card p-4">
            <h3 class="font-semibold text-white mb-3 text-sm">RSVP Status</h3>
            <div class="space-y-1.5">
                @foreach($night->attendees->whereNotNull('rsvp')->sortBy(fn($a) => match($a->rsvp) { 'GOING' => 0, 'MAYBE' => 1, default => 2 }) as $attendee)
                @php
                    $attName    = $attendee->groupPlayer?->displayName() ?? $attendee->user?->username ?? 'Unknown';
                    $attInitial = $attendee->groupPlayer?->initial() ?? strtoupper(substr($attName, 0, 1));
                    $attPhoto   = $attendee->groupPlayer?->photoUrl();
                @endphp
                <div class="flex items-center gap-2">
                    @if($attPhoto)
                        <img src="{{ $attPhoto }}" class="w-6 h-6 rounded-full object-cover shrink-0">
                    @else
                        <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold shrink-0"
                            style="background-color: var(--color-felt); color: var(--color-gold);">
                            {{ $attInitial }}
                        </div>
                    @endif
                    <span class="text-sm text-gray-300 flex-1 truncate">{{ $attName }}</span>
                    <span class="text-xs font-semibold
                        {{ $attendee->rsvp === 'GOING' ? 'text-green-400' : ($attendee->rsvp === 'NOT_GOING' ? 'text-red-400' : 'text-yellow-400') }}">
                        {{ match($attendee->rsvp) { 'GOING' => '✓ Going', 'NOT_GOING' => '✗ Not going', default => '? Maybe' } }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Record Results: mark attendance + drag to order --}}
        @if(auth()->id() === $group->owner_id || auth()->user()->isAdmin())
        <div class="card p-4" x-data="attendeeResults({{ Js::from(['attended' => $attended, 'absent' => $absent]) }})">
            <h3 class="font-semibold text-white mb-0.5 text-sm">Record Results</h3>
            <p class="text-xs text-gray-500 mb-4">Mark who showed up, then drag to set finishing order.</p>

            <form method="POST" action="{{ route('attendees.store', [$group, $night]) }}">
                @csrf

                {{-- Hidden inputs track attended order for submission --}}
                <template x-for="player in attended" :key="'h-' + player.id">
                    <input type="hidden" name="placements[]" :value="player.id">
                </template>

                {{-- Attended (draggable placement list) --}}
                <div class="mb-4">
                    <div class="text-xs font-semibold uppercase tracking-wider mb-2" style="color:var(--color-gold);">
                        Attended <span class="font-normal text-gray-500 normal-case tracking-normal" x-text="'(' + attended.length + ')'"></span>
                    </div>

                    <div class="space-y-1.5">
                        <template x-for="(player, i) in attended" :key="'a-' + player.id">
                            <div
                                draggable="true"
                                @dragstart="dragStart(i)"
                                @dragenter.prevent="dragEnter(i)"
                                @dragover.prevent
                                @dragend="dragEnd"
                                class="flex items-center gap-2 p-2 rounded-lg cursor-grab select-none transition-opacity"
                                :class="{ 'opacity-40': draggingIndex === i }"
                                :style="i === 0
                                    ? 'background:rgba(201,162,39,0.12); border:1px solid rgba(201,162,39,0.35);'
                                    : 'background-color:var(--color-felt); border:1px solid transparent;'"
                            >
                                <div class="w-6 text-center shrink-0">
                                    <span x-show="i === 0">🏆</span>
                                    <span x-show="i > 0" class="text-xs font-bold text-gray-500" x-text="(i+1) + '.'"></span>
                                </div>

                                <template x-if="player.photo_url">
                                    <img :src="player.photo_url" class="w-9 h-9 rounded-full object-cover shrink-0">
                                </template>
                                <template x-if="!player.photo_url">
                                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold shrink-0"
                                        style="background-color:var(--color-felt-dark); color:var(--color-gold);">
                                        <span x-text="player.initial"></span>
                                    </div>
                                </template>

                                <span class="flex-1 text-sm font-medium truncate"
                                    :style="i === 0 ? 'color:var(--color-gold);' : 'color:#d1d5db;'"
                                    x-text="player.name"></span>

                                <span class="text-gray-600 shrink-0 leading-none mr-1">⠿</span>
                                <button type="button" @click="removePlayer(player)"
                                    class="shrink-0 w-5 h-5 rounded-full flex items-center justify-center text-xs transition-colors"
                                    style="color:#6b7280; background:rgba(255,255,255,0.05);"
                                    title="Remove">✕</button>
                            </div>
                        </template>

                        <div x-show="attended.length === 0"
                            class="text-xs text-gray-600 py-2 text-center border border-dashed rounded-lg"
                            style="border-color:var(--color-border);">
                            No one marked as attended yet
                        </div>
                    </div>
                </div>

                {{-- Absent / Not there --}}
                <div x-show="absent.length > 0" class="mb-4">
                    <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2">
                        Not There
                    </div>
                    <div class="space-y-1">
                        <template x-for="player in absent" :key="'b-' + player.id">
                            <div class="flex items-center gap-2 px-2 py-1.5 rounded-lg"
                                style="background-color:var(--color-felt); opacity:0.6;">
                                <div class="w-6 shrink-0"></div>
                                <template x-if="player.photo_url">
                                    <img :src="player.photo_url" class="w-7 h-7 rounded-full object-cover shrink-0">
                                </template>
                                <template x-if="!player.photo_url">
                                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold shrink-0"
                                        style="background-color:var(--color-felt-dark); color:#6b7280;">
                                        <span x-text="player.initial"></span>
                                    </div>
                                </template>
                                <span class="flex-1 text-sm text-gray-500 truncate" x-text="player.name"></span>
                                <button type="button" @click="addPlayer(player)"
                                    class="shrink-0 text-xs px-2 py-0.5 rounded transition-colors"
                                    style="color:var(--color-gold); border:1px solid rgba(201,162,39,0.35);"
                                    title="Mark as attended">+ Attended</button>
                            </div>
                        </template>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-full text-sm" :disabled="attended.length === 0">
                    Save Results
                </button>
            </form>
        </div>
        @endif

    </div>
</div>
@endsection
