@extends('layouts.main')
@section('title', 'Join Group – Poker Night')

@section('content')
<div class="max-w-md mx-auto text-center py-12">
    <div class="text-5xl mb-4"><span class="suit suit-heart">♥</span></div>
    <h1 class="text-2xl font-bold text-white mb-2">Join {{ $group->name }}</h1>
    <p class="text-gray-400 text-sm mb-6">
        {{ $group->memberships->count() }} {{ Str::plural('member', $group->memberships->count()) }} already in this group.
    </p>

    @if($alreadyMember)
        <div class="card p-4 mb-4" style="border-color: var(--color-felt-light);">
            <p class="text-green-400 text-sm">You're already a member of this group.</p>
        </div>
        <a href="{{ route('groups.show', $group) }}" class="btn btn-gold">Go to Group</a>
    @else
        <div class="card p-6 mb-4">
            @if($group->description)
                <p class="text-gray-300 text-sm mb-4">{{ $group->description }}</p>
            @endif
            <form method="POST" action="{{ route('groups.join.post', $group->invite_code) }}">
                @csrf
                <button type="submit" class="btn btn-gold w-full">Join This Group</button>
            </form>
        </div>
        <a href="{{ route('groups.index') }}" class="text-sm text-gray-400 hover:text-white transition-colors">Cancel</a>
    @endif
</div>
@endsection
