@extends('layouts.main')
@section('title', 'Schedule Night – ' . $group->name)

@section('content')
<div class="max-w-2xl mx-auto">
    <a href="{{ route('groups.show', $group) }}" class="text-gray-400 hover:text-white text-sm transition-colors">← {{ $group->name }}</a>
    <h1 class="text-2xl font-bold text-white mt-2 mb-6">
        <span class="suit suit-club mr-2">♣</span>Schedule a Poker Night
    </h1>

    <div class="card p-6">
        <form method="POST" action="{{ route('nights.store', $group) }}">
            @csrf

            <div class="grid sm:grid-cols-2 gap-4 mb-4">
                <div class="sm:col-span-2">
                    <label class="label" for="title">Night Title</label>
                    <input id="title" name="title" type="text" class="input @error('title') border-red-500 @enderror"
                        value="{{ old('title') }}" placeholder="Friday Night Poker" autofocus>
                    @error('title')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="label" for="scheduled_at">Date & Time</label>
                    <input id="scheduled_at" name="scheduled_at" type="datetime-local"
                        class="input @error('scheduled_at') border-red-500 @enderror"
                        value="{{ old('scheduled_at') }}">
                    @error('scheduled_at')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="label" for="buy_in">Buy-In ($) <span class="text-gray-500">(optional)</span></label>
                    <input id="buy_in" name="buy_in" type="number" step="0.01" min="0"
                        class="input @error('buy_in') border-red-500 @enderror"
                        value="{{ old('buy_in') }}" placeholder="20.00">
                    @error('buy_in')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mb-6">
                <label class="label" for="notes">Notes <span class="text-gray-500">(optional)</span></label>
                <textarea id="notes" name="notes" class="input @error('notes') border-red-500 @enderror"
                    rows="3" placeholder="Bring snacks, starts at 7pm...">{{ old('notes') }}</textarea>
                @error('notes')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="flex gap-3">
                <button type="submit" class="btn btn-gold">Schedule Night</button>
                <a href="{{ route('groups.show', $group) }}" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
