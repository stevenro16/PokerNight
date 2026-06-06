<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Poker Night')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex flex-col" style="background-color: var(--color-surface);">

    {{-- Navbar --}}
    <nav style="background-color: var(--color-felt-dark); border-bottom: 1px solid var(--color-border);">
        <div class="max-w-7xl mx-auto px-4 h-14 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                <span class="suit suit-spade text-2xl">♠</span>
                <span class="font-bold text-lg" style="color: var(--color-gold);">Poker Night</span>
                <span class="suit suit-heart text-2xl">♥</span>
            </a>

            <div class="flex items-center gap-3">
                @auth
                    <a href="{{ route('groups.index') }}" class="text-sm text-gray-300 hover:text-white transition-colors">My Groups</a>
                    <a href="{{ route('dashboard') }}" class="text-sm text-gray-300 hover:text-white transition-colors">Dashboard</a>
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="text-sm" style="color: var(--color-gold);">Admin</a>
                    @endif
                    @if(auth()->user()->isSuperAdmin())
                        <a href="{{ route('superadmin.dashboard') }}" class="text-xs text-purple-400 hover:text-purple-300 transition-colors">Super</a>
                    @endif
                    <a href="{{ route('profile.show') }}" class="flex items-center gap-1.5 text-sm text-gray-300 hover:text-white transition-colors">
                        @if(auth()->user()->avatar_url)
                            <img src="{{ auth()->user()->avatarUrl() }}" alt="" class="w-6 h-6 rounded-full object-cover">
                        @else
                            <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold"
                                style="background-color: var(--color-felt); color: var(--color-gold);">
                                {{ strtoupper(substr(auth()->user()->username, 0, 1)) }}
                            </div>
                        @endif
                        {{ auth()->user()->username }}
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="btn btn-ghost text-xs py-1 px-3">Sign Out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-ghost text-sm py-1 px-3">Sign In</a>
                    <a href="{{ route('register') }}" class="btn btn-gold text-sm py-1 px-3">Join Free</a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 mt-4">
            <div class="rounded-lg px-4 py-3 text-sm font-medium" style="background-color: #14532d; color: #86efac; border: 1px solid #166534;">
                {{ session('success') }}
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="max-w-7xl mx-auto px-4 mt-4">
            <div class="rounded-lg px-4 py-3 text-sm font-medium" style="background-color: #7f1d1d; color: #fca5a5; border: 1px solid #991b1b;">
                {{ session('error') }}
            </div>
        </div>
    @endif

    <main class="flex-1 max-w-7xl mx-auto w-full px-4 py-6">
        @yield('content')
    </main>

    <footer class="text-center text-xs py-6" style="color: var(--color-muted); border-top: 1px solid var(--color-border);">
        <span class="suit suit-club mr-1">♣</span> Poker Night
        <span class="suit suit-diamond mx-1">♦</span>
    </footer>

    @stack('scripts')
</body>
</html>
