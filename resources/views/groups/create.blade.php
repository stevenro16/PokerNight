@extends('layouts.main')
@section('title', 'Create Group – Poker Night')

@section('content')
<div class="max-w-lg mx-auto">
    <h1 class="text-2xl font-bold mb-6 text-white">
        <span class="suit suit-spade mr-2">♠</span>Create a Poker Group
    </h1>

    <div class="card p-6">
        <form method="POST" action="{{ route('groups.store') }}">
            @csrf

            <div class="mb-4">
                <label class="label" for="name">Group Name</label>
                <input id="name" name="name" type="text" class="input @error('name') border-red-500 @enderror"
                    value="{{ old('name') }}" placeholder="Friday Night Fellas" autofocus>
                @error('name')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="mb-6">
                <label class="label" for="description">Description <span class="text-gray-500">(optional)</span></label>
                <textarea id="description" name="description" class="input @error('description') border-red-500 @enderror"
                    rows="3" placeholder="A little bit about the group...">{{ old('description') }}</textarea>
                @error('description')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="flex gap-3">
                <button type="submit" class="btn btn-gold">Create Group</button>
                <a href="{{ route('groups.index') }}" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
