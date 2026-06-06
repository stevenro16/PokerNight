<div class="card p-4 flex gap-4">
    {{-- Avatar --}}
    <div class="flex-shrink-0">
        @if($player->photo_path)
            <img src="{{ $player->photoUrl() }}" alt="{{ $player->displayName() }}"
                class="w-16 h-16 rounded-full object-cover">
        @else
            <div class="w-16 h-16 rounded-full flex items-center justify-center text-xl font-bold"
                style="background-color: var(--color-felt); color: var(--color-gold);">
                {{ $player->initial() }}
            </div>
        @endif
    </div>

    {{-- Info --}}
    <div class="flex-1 min-w-0">
        <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
                <div class="font-semibold text-white truncate">{{ $player->name }}</div>
                @if($player->nickname)
                    <div class="text-sm" style="color: var(--color-gold);">"{{ $player->nickname }}"</div>
                @endif
            </div>
            <span class="badge {{ $player->role === 'CORE' ? 'badge-green' : 'badge-gray' }} flex-shrink-0 text-xs">
                {{ $player->role === 'CORE' ? 'Core' : 'Guest' }}
            </span>
        </div>

        <div class="mt-2 space-y-1">
            @if($player->isLinked())
                <div class="flex items-center gap-1 text-xs text-green-400">
                    <span>✓</span>
                    <span>{{ $player->user->username }}</span>
                </div>
            @elseif($player->email)
                <div class="text-xs text-gray-500 truncate">{{ $player->email }}</div>
                <div class="flex items-center gap-1 text-xs text-yellow-500">
                    <span>⏳ No account yet</span>
                </div>
                @if($player->invite_token)
                <div x-data="{ copied: false }" class="mt-1">
                    <button
                        @click="navigator.clipboard.writeText('{{ route('invite.show', $player->invite_token) }}'); copied = true; setTimeout(() => copied = false, 2000)"
                        class="text-xs flex items-center gap-1 transition-colors"
                        style="color: var(--color-muted);"
                        x-bind:style="copied ? 'color: #86efac' : ''">
                        <span x-show="!copied">📋 Copy invite link</span>
                        <span x-show="copied" x-cloak>✓ Copied!</span>
                    </button>
                </div>
                @endif
            @else
                <div class="text-xs text-gray-600">No email / no account</div>
            @endif
        </div>
    </div>

    {{-- Actions --}}
    <div class="flex flex-col gap-1 flex-shrink-0">
        <a href="{{ route('players.edit', [$group, $player]) }}" class="btn btn-ghost text-xs py-1 px-2">Edit</a>
        <form method="POST" action="{{ route('players.destroy', [$group, $player]) }}"
            onsubmit="return confirm('Remove {{ addslashes($player->displayName()) }} from the roster?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger text-xs py-1 px-2 w-full">Remove</button>
        </form>
    </div>
</div>
