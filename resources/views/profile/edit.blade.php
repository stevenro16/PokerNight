@extends('layouts.main')
@section('title', 'My Profile – Poker Night')

@section('content')
<div class="max-w-xl mx-auto">

    <h1 class="text-2xl font-bold text-white mb-6">My Profile</h1>

    {{-- Account settings --}}
    <div class="card p-6 mb-6">
        <h2 class="font-semibold text-white mb-4">Account Settings</h2>

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data"
            x-data="{
                preview: null,
                onChange(e) {
                    const file = e.target.files[0];
                    if (!file) return;
                    if (file.size > 10 * 1024 * 1024) { alert('Max 10 MB'); return; }
                    const reader = new FileReader();
                    reader.onload = ev => this.preview = ev.target.result;
                    reader.readAsDataURL(file);
                }
            }">
            @csrf

            {{-- Avatar --}}
            <div class="flex items-center gap-4 mb-5">
                <div class="relative shrink-0 cursor-pointer" @click="$refs.avatarInput.click()">
                    <template x-if="preview">
                        <img :src="preview" class="w-20 h-20 rounded-full object-cover">
                    </template>
                    <template x-if="!preview">
                        @if($user->avatar_url)
                            <img src="{{ $user->avatarUrl() }}" class="w-20 h-20 rounded-full object-cover">
                        @else
                            <div class="w-20 h-20 rounded-full flex items-center justify-center text-2xl font-bold"
                                style="background-color: var(--color-felt); color: var(--color-gold);">
                                {{ strtoupper(substr($user->username, 0, 1)) }}
                            </div>
                        @endif
                    </template>
                    <div class="absolute inset-0 rounded-full flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity"
                        style="background:rgba(0,0,0,0.5);">
                        <span class="text-white text-xs">Change</span>
                    </div>
                </div>
                <div>
                    <input x-ref="avatarInput" type="file" name="avatar" accept="image/*" class="hidden" @change="onChange($event)">
                    <button type="button" @click="$refs.avatarInput.click()"
                        class="btn btn-ghost text-sm">Upload Photo</button>
                    <p class="text-xs text-gray-500 mt-1">JPG, PNG, GIF up to 10 MB</p>
                </div>
            </div>

            {{-- Username --}}
            <div class="mb-5">
                <label class="form-label">Username</label>
                <input type="text" name="username" value="{{ old('username', $user->username) }}"
                    class="input w-full @error('username') border-red-500 @enderror"
                    placeholder="your_username" minlength="3" maxlength="30">
                @error('username')
                    <p class="form-error mt-1">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-500 mt-1">Letters, numbers, and underscores only. Min 3 characters.</p>
            </div>

            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>

    {{-- Group profiles --}}
    @if($groupProfiles->isNotEmpty())
    <div class="card p-6">
        <h2 class="font-semibold text-white mb-4">Group Profiles</h2>
        <p class="text-sm text-gray-400 mb-4">Edit your nickname, photo, and role within each group.</p>

        <div class="space-y-3">
            @foreach($groupProfiles as $player)
            <div class="flex items-center gap-3 p-3 rounded-lg" style="background-color: var(--color-felt);">
                @if($player->photo_path)
                    <img src="{{ $player->photoUrl() }}" class="w-10 h-10 rounded-full object-cover shrink-0">
                @else
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold shrink-0"
                        style="background-color: var(--color-felt-dark); color: var(--color-gold);">
                        {{ $player->initial() }}
                    </div>
                @endif

                <div class="flex-1 min-w-0">
                    <div class="text-sm font-semibold text-white truncate">{{ $player->group->name }}</div>
                    <div class="text-xs text-gray-400 truncate">
                        {{ $player->displayName() }}
                        @if($player->nickname)
                            · <span style="color: var(--color-gold);">"{{ $player->nickname }}"</span>
                        @endif
                        · <span class="badge badge-gray text-xs py-0">{{ $player->role }}</span>
                    </div>
                </div>

                <a href="{{ route('players.edit', [$player->group, $player]) }}"
                    class="btn btn-ghost text-xs py-1 px-2.5 shrink-0">Edit</a>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
