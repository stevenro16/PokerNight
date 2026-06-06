@extends('layouts.main')
@section('title', 'Edit ' . $player->displayName())

@section('content')
<div class="max-w-lg mx-auto">
    <a href="{{ route('players.index', $group) }}" class="text-gray-400 hover:text-white text-sm transition-colors">← Roster</a>
    <h1 class="text-2xl font-bold text-white mt-2 mb-6">Edit Player</h1>

    <div class="card p-6" x-data="{ photoPreview: null }">
        <form method="POST" action="{{ route('players.update', [$group, $player]) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Photo --}}
            <div class="flex items-center gap-4 mb-6">
                <div class="w-20 h-20 rounded-full overflow-hidden flex items-center justify-center flex-shrink-0"
                    style="background-color: var(--color-felt);">
                    <img x-show="photoPreview" :src="photoPreview" class="w-full h-full object-cover" x-cloak>
                    @if($player->photo_path)
                        <img x-show="!photoPreview" src="{{ $player->photoUrl() }}" class="w-full h-full object-cover">
                    @else
                        <span x-show="!photoPreview" class="text-2xl font-bold" style="color: var(--color-gold);">
                            {{ $player->initial() }}
                        </span>
                    @endif
                </div>
                <div>
                    <label class="btn btn-ghost text-sm cursor-pointer">
                        Change Photo
                        <input type="file" name="photo" accept="image/*" class="hidden"
                            @change="photoPreview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
                    </label>
                    <p class="text-xs text-gray-500 mt-1">Max 3 MB</p>
                    @error('photo')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>

            @if($player->isLinked())
            <div class="card p-3 mb-4 flex items-center gap-2" style="border-color: var(--color-felt-light);">
                <span class="text-green-400 text-sm">✓</span>
                <span class="text-sm text-gray-300">Linked to account: <strong class="text-white">{{ $player->user->username }}</strong></span>
            </div>
            @endif

            <div class="grid sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="label" for="name">Full Name <span class="text-red-400">*</span></label>
                    <input id="name" name="name" type="text" class="input @error('name') border-red-500 @enderror"
                        value="{{ old('name', $player->name) }}">
                    @error('name')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="nickname">Nickname</label>
                    <input id="nickname" name="nickname" type="text" class="input @error('nickname') border-red-500 @enderror"
                        value="{{ old('nickname', $player->nickname) }}" placeholder="Ace">
                    @error('nickname')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mb-4" x-data="{ role: '{{ old('role', $player->role) }}' }">
                <label class="label">Role <span class="text-red-400">*</span></label>
                <div class="flex gap-3">
                    @foreach(['CORE' => ['Core Member', 'Regular player, always invited'], 'GUEST' => ['Guest', 'Occasional player']] as $value => [$label, $desc])
                    <label class="flex-1 card p-3 cursor-pointer transition-colors hover:border-yellow-600"
                        :class="role === '{{ $value }}' ? 'border-yellow-500' : ''">
                        <input type="radio" name="role" value="{{ $value }}" class="hidden"
                            x-model="role">
                        <div class="font-semibold text-sm text-white">{{ $label }}</div>
                        <div class="text-xs text-gray-400 mt-0.5">{{ $desc }}</div>
                    </label>
                    @endforeach
                </div>
                @error('role')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="mb-6">
                <label class="label" for="email">Email</label>
                <input id="email" name="email" type="email" class="input @error('email') border-red-500 @enderror"
                    value="{{ old('email', $player->email) }}" placeholder="john@example.com">
                @if($player->invite_token && ! $player->isLinked())
                <div class="mt-2 flex items-center gap-2" x-data="{ copied: false }">
                    <span class="text-xs text-gray-500">Invite link:</span>
                    <button type="button"
                        @click="navigator.clipboard.writeText('{{ route('invite.show', $player->invite_token) }}'); copied = true; setTimeout(() => copied = false, 2000)"
                        class="text-xs transition-colors"
                        :style="copied ? 'color: #86efac' : 'color: var(--color-gold)'">
                        <span x-show="!copied">📋 Copy</span>
                        <span x-show="copied" x-cloak>✓ Copied!</span>
                    </button>
                </div>
                @endif
                @error('email')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="flex gap-3">
                <button type="submit" class="btn btn-gold">Save Changes</button>
                <a href="{{ route('players.index', $group) }}" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
