@extends('layouts.app')
@section('title', 'Sign In – Poker Night')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-sm">
        <div class="text-center mb-8">
            <div class="text-5xl space-x-1 mb-3">
                <span class="suit suit-spade">♠</span>
                <span class="suit suit-heart">♥</span>
            </div>
            <h1 class="text-2xl font-bold" style="color: var(--color-gold);">Sign In</h1>
            <p class="text-gray-400 text-sm mt-1">Welcome back to the table</p>
        </div>

        <div class="card p-6">
            <form method="POST" action="{{ route('login') }}" x-data="{ loading: false }" @submit="loading = true">
                @csrf

                <div class="mb-4">
                    <label class="label" for="login">Email or Username</label>
                    <input id="login" name="login" type="text" class="input @error('login') border-red-500 @enderror"
                        value="{{ old('login') }}" placeholder="you@example.com" autocomplete="username" autofocus>
                    @error('login')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="label" for="password">Password</label>
                    <input id="password" name="password" type="password" class="input @error('password') border-red-500 @enderror"
                        placeholder="••••••••" autocomplete="current-password">
                    @error('password')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center mb-6">
                    <input id="remember" name="remember" type="checkbox" class="mr-2 accent-yellow-500">
                    <label for="remember" class="text-sm text-gray-400">Remember me</label>
                </div>

                <button type="submit" class="btn btn-gold w-full" :disabled="loading">
                    <span x-show="!loading">Sign In</span>
                    <span x-show="loading" x-cloak>Signing in…</span>
                </button>
            </form>
        </div>

        <p class="text-center text-sm text-gray-400 mt-4">
            No account? <a href="{{ route('register') }}" style="color: var(--color-gold);" class="hover:underline">Create one free</a>
        </p>
    </div>
</div>
@endsection
