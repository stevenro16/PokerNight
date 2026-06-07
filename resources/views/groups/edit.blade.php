@extends('layouts.main')
@section('title', 'Edit ' . $group->name . ' – Poker Night')

@section('content')
<div class="max-w-xl mx-auto">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('groups.show', $group) }}" class="text-gray-400 hover:text-white text-sm">← Back</a>
        <h1 class="text-2xl font-bold text-white">Edit Group</h1>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 rounded text-sm font-medium" style="background-color: rgba(34,197,94,0.15); border: 1px solid rgba(34,197,94,0.4); color: #86efac;">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('groups.update', $group) }}" method="POST" enctype="multipart/form-data"
          x-data="{ preview: '{{ $group->avatarUrl() }}' }">
        @csrf
        @method('PUT')

        <div class="card p-6 space-y-5">

            {{-- Group Icon / Avatar --}}
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Group Icon</label>

                {{-- Card-style preview --}}
                <div style="width:100%; height:180px; border-radius:0.5rem; overflow:hidden; position:relative; background-color:var(--color-felt); border:1px solid var(--color-border);">
                    <template x-if="preview">
                        <img :src="preview" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;">
                    </template>
                    <template x-if="!preview">
                        <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:3rem;opacity:0.4;">♠</div>
                    </template>
                </div>

                <div class="flex items-center justify-between mt-2">
                    <label class="cursor-pointer inline-block">
                        <span class="btn btn-ghost text-sm">Choose Image</span>
                        <input type="file" name="avatar" accept="image/*" class="hidden"
                               @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : preview">
                    </label>
                    <span class="text-xs text-gray-500">Max 5 MB · JPG, PNG, GIF, WebP</span>
                </div>

                @error('avatar')
                    <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Name --}}
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Group Name</label>
                <input type="text" name="name" value="{{ old('name', $group->name) }}"
                       class="input w-full" maxlength="60" required>
                @error('name')
                    <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Description --}}
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Description</label>
                <textarea name="description" class="input w-full" rows="3" maxlength="500"
                          placeholder="Optional — describe your group">{{ old('description', $group->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn btn-gold">Save Changes</button>
                <a href="{{ route('groups.show', $group) }}" class="btn btn-ghost">Cancel</a>
            </div>

        </div>
    </form>

    {{-- Roster --}}
    <div class="mt-4">
        <a href="{{ route('players.index', $group) }}" class="btn btn-ghost w-full text-center">
            👥 Manage Roster
        </a>
    </div>

</div>
@endsection
