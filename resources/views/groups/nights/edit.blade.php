@extends('layouts.main')
@section('title', 'Edit Night – ' . $night->title)

@section('content')
<div class="max-w-2xl mx-auto">
    <a href="{{ route('nights.show', [$group, $night]) }}" class="text-gray-400 hover:text-white text-sm transition-colors">← {{ $night->title }}</a>
    <h1 class="text-2xl font-bold text-white mt-2 mb-6">Edit Night</h1>

    <div class="card p-6">
        <form method="POST" action="{{ route('nights.update', [$group, $night]) }}">
            @csrf
            @method('PUT')

            <div class="grid sm:grid-cols-2 gap-4 mb-4">
                <div class="sm:col-span-2">
                    <label class="label" for="title">Night Title</label>
                    <input id="title" name="title" type="text" class="input @error('title') border-red-500 @enderror"
                        value="{{ old('title', $night->title) }}">
                    @error('title')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="label" for="scheduled_at">Scheduled Date</label>
                    <input id="scheduled_at" name="scheduled_at" type="datetime-local"
                        class="input @error('scheduled_at') border-red-500 @enderror"
                        value="{{ old('scheduled_at', $night->scheduled_at->format('Y-m-d\TH:i')) }}">
                    @error('scheduled_at')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="label" for="played_at">Played Date <span class="text-gray-500">(optional)</span></label>
                    <input id="played_at" name="played_at" type="datetime-local"
                        class="input @error('played_at') border-red-500 @enderror"
                        value="{{ old('played_at', $night->played_at?->format('Y-m-d\TH:i')) }}">
                    @error('played_at')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="label" for="buy_in">Buy-In ($)</label>
                    <input id="buy_in" name="buy_in" type="number" step="0.01" min="0"
                        class="input @error('buy_in') border-red-500 @enderror"
                        value="{{ old('buy_in', $night->buy_in) }}" placeholder="20.00">
                    @error('buy_in')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="label" for="status">Status</label>
                    <select id="status" name="status" class="input @error('status') border-red-500 @enderror">
                        @foreach(['SCHEDULED', 'COMPLETED', 'CANCELLED'] as $s)
                            <option value="{{ $s }}" {{ old('status', $night->status) === $s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                    @error('status')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mb-6">
                <label class="label" for="notes">Notes</label>
                <textarea id="notes" name="notes" class="input @error('notes') border-red-500 @enderror"
                    rows="3">{{ old('notes', $night->notes) }}</textarea>
                @error('notes')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="flex gap-3">
                <button type="submit" class="btn btn-gold">Save Changes</button>
                <a href="{{ route('nights.show', [$group, $night]) }}" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
