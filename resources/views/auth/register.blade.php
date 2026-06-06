@extends('layouts.app')
@section('title', 'Create Account – Poker Night')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-sm">
        <div class="text-center mb-8">
            <div class="text-5xl space-x-1 mb-3">
                <span class="suit suit-diamond">♦</span>
                <span class="suit suit-club">♣</span>
            </div>
            <h1 class="text-2xl font-bold" style="color: var(--color-gold);">Create Account</h1>
            <p class="text-gray-400 text-sm mt-1">Join the table — it's free</p>
        </div>

        @if(session('invite_info'))
        <div class="card p-3 mb-4 flex items-center gap-2" style="border-color: var(--color-felt-light);">
            <span class="text-green-400">✓</span>
            <p class="text-sm text-gray-300">{{ session('invite_info') }}</p>
        </div>
        @endif

        <div class="card p-6">
            <form method="POST" action="{{ route('register') }}" x-data="{ loading: false }" @submit="loading = true">
                @csrf

                <div class="mb-4">
                    <label class="label" for="username">Username</label>
                    <input id="username" name="username" type="text" class="input @error('username') border-red-500 @enderror"
                        value="{{ old('username') }}" placeholder="pokerking99" autocomplete="username" autofocus>
                    @error('username')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Letters, numbers, underscores only.</p>
                </div>

                <div class="mb-4">
                    <label class="label" for="email">Email</label>
                    <input id="email" name="email" type="email" class="input @error('email') border-red-500 @enderror"
                        value="{{ old('email', $prefillEmail ?? '') }}" placeholder="you@example.com" autocomplete="email">
                    @error('email')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="label" for="password">Password</label>
                    <input id="password" name="password" type="password" class="input @error('password') border-red-500 @enderror"
                        placeholder="Min. 8 characters" autocomplete="new-password">
                    @error('password')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label class="label" for="password_confirmation">Confirm Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" class="input"
                        placeholder="••••••••" autocomplete="new-password">
                </div>

                <button type="submit" class="btn btn-gold w-full" :disabled="loading">
                    <span x-show="!loading">Create Account</span>
                    <span x-show="loading" x-cloak>Creating…</span>
                </button>
            </form>
        </div>

        <p class="text-center text-sm text-gray-400 mt-4">
            Already have an account? <a href="{{ route('login') }}" style="color: var(--color-gold);" class="hover:underline">Sign in</a>
        </p>
    </div>
</div>
@endsection
