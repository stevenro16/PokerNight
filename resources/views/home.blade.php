@extends('layouts.app')
@section('title', 'Poker Night – Track Your Games')

@section('content')
<style>
    @keyframes floatSuit {
        0%, 100% { transform: translateY(0) rotate(-8deg);   opacity: 0.06; }
        50%       { transform: translateY(-26px) rotate(8deg); opacity: 0.12; }
    }
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(22px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes shimmerGold {
        0%   { background-position: -200% center; }
        100% { background-position:  200% center; }
    }
    .suit-float { position: absolute; user-select: none; pointer-events: none; }
    .sf1 { font-size: 8rem;  top:  5%; left:  2%; color: #c9a227; animation: floatSuit  8s ease-in-out infinite 0s;   }
    .sf2 { font-size: 7rem;  top: 62%; left: 78%; color: #ef4444; animation: floatSuit 11s ease-in-out infinite 2s;   }
    .sf3 { font-size: 9rem;  top: 72%; left:  5%; color: #c9a227; animation: floatSuit  9s ease-in-out infinite 1s;   }
    .sf4 { font-size: 8rem;  top: 10%; left: 86%; color: #c9a227; animation: floatSuit 13s ease-in-out infinite 3.5s; }
    .sf5 { font-size: 5rem;  top: 42%; left: 52%; color: #ef4444; animation: floatSuit 10s ease-in-out infinite 5s;   }

    .fi1 { animation: fadeUp 0.65s ease-out 0.00s both; }
    .fi2 { animation: fadeUp 0.65s ease-out 0.12s both; }
    .fi3 { animation: fadeUp 0.65s ease-out 0.24s both; }
    .fi4 { animation: fadeUp 0.65s ease-out 0.38s both; }
    .fi5 { animation: fadeUp 0.65s ease-out 0.52s both; }

    .gold-text {
        background: linear-gradient(90deg, #b8860b 0%, #f5d87a 30%, #c9a227 55%, #f0c040 80%, #b8860b 100%);
        background-size: 250% auto;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        animation: shimmerGold 5s linear infinite;
    }
    .feature-icon {
        width: 2.25rem; height: 2.25rem; border-radius: 0.625rem;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0; font-size: 1rem;
        background: rgba(201,162,39,0.1);
        border: 1px solid rgba(201,162,39,0.2);
    }
</style>

<div class="min-h-screen flex flex-col"
    style="background: radial-gradient(ellipse 90% 70% at 10% 40%, rgba(15,34,16,0.8) 0%, #141414 60%);">

    {{-- Floating suit decorations (full-page layer) --}}
    <div class="fixed inset-0 overflow-hidden pointer-events-none" style="z-index:0;">
        <span class="suit-float sf1">♠</span>
        <span class="suit-float sf2">♥</span>
        <span class="suit-float sf3">♦</span>
        <span class="suit-float sf4">♣</span>
        <span class="suit-float sf5">♥</span>
    </div>

    {{-- ── HERO ──────────────────────────────────────────── --}}
    <div class="relative flex-1 flex items-center py-16 px-6" style="z-index:1;">
        <div class="w-full max-w-6xl mx-auto">

            {{-- Two-column grid: copy left, auth card right --}}
            <div style="display:grid; grid-template-columns: 1fr 380px; gap: 5rem; align-items: center;">

                {{-- LEFT: Landing copy --}}
                <div style="min-width:0;">

                    {{-- Wordmark --}}
                    <div class="fi1 flex items-center gap-2.5 mb-8">
                        <span class="text-2xl leading-none" style="color:#c9a227;">♠</span>
                        <span class="text-2xl leading-none" style="color:#ef4444;">♥</span>
                        <span class="text-xl font-bold tracking-widest uppercase" style="color:#c9a227; letter-spacing:0.15em;">Poker Night</span>
                    </div>

                    {{-- Headline --}}
                    <h1 class="fi2 font-black leading-none mb-6" style="font-size: clamp(3rem, 6vw, 5rem);">
                        <span class="gold-text">Deal In.</span><br>
                        <span class="text-white">Track Everything.</span>
                    </h1>

                    <p class="fi3 mb-10 leading-relaxed" style="font-size: 1.125rem; color: #9ca3af; max-width: 28rem;">
                        Your private poker group, organized. Schedule nights, upload photos, and crown the season champion.
                    </p>

                    {{-- Feature list --}}
                    <div class="fi4 space-y-5">
                        <div class="flex items-start gap-3.5">
                            <div class="feature-icon mt-0.5"><span style="color:#c9a227;">♠</span></div>
                            <div>
                                <div class="text-white font-semibold text-sm">Private Groups &amp; Invite Codes</div>
                                <div class="text-gray-500 text-sm mt-0.5">Your crew only. Share a link or a code — they're in instantly.</div>
                            </div>
                        </div>
                        <div class="flex items-start gap-3.5">
                            <div class="feature-icon mt-0.5"><span style="color:#ef4444;">♥</span></div>
                            <div>
                                <div class="text-white font-semibold text-sm">Game Night Records &amp; Photo Galleries</div>
                                <div class="text-gray-500 text-sm mt-0.5">Attendees, buy-ins, results — every night immortalized.</div>
                            </div>
                        </div>
                        <div class="flex items-start gap-3.5">
                            <div class="feature-icon mt-0.5"><span style="color:#c9a227;">♦</span></div>
                            <div>
                                <div class="text-white font-semibold text-sm">Live Season Leaderboard</div>
                                <div class="text-gray-500 text-sm mt-0.5">Win-count rankings updated after every game. No spreadsheets.</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RIGHT: Auth card --}}
                <div class="fi5" style="width:100%;" x-data="{ tab: '{{ $errors->any() && old('_form') === 'login' ? 'login' : 'register' }}' }">

                    @if(auth()->check())
                    {{-- Logged-in state --}}
                    <div class="auth-card p-8 text-center" style="background: var(--color-card-bg);">
                        @if(auth()->user()->avatar_url)
                            <img src="{{ auth()->user()->avatarUrl() }}" class="w-16 h-16 rounded-full object-cover mx-auto mb-4">
                        @else
                            <div class="w-16 h-16 rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4"
                                style="background: var(--color-felt); color: var(--color-gold);">
                                {{ strtoupper(substr(auth()->user()->username, 0, 1)) }}
                            </div>
                        @endif
                        <p class="text-gray-400 text-sm mb-1">Signed in as</p>
                        <p class="text-white font-bold text-lg mb-6">{{ auth()->user()->username }}</p>
                        <a href="{{ route('dashboard') }}" class="btn btn-gold w-full mb-3">Go to Dashboard →</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-ghost w-full text-sm">Sign Out</button>
                        </form>
                    </div>

                    @else

                    {{-- Auth card --}}
                    <div style="
                        background: linear-gradient(160deg, #1a1a2e 0%, #0f0f1c 100%);
                        border-radius: 1rem;
                        border: 1px solid rgba(201,162,39,0.2);
                        border-top: 2px solid rgba(201,162,39,0.5);
                        box-shadow: 0 32px 64px rgba(0,0,0,0.7), inset 0 1px 0 rgba(255,255,255,0.04);
                        padding: 2rem;
                    ">
                        {{-- ── Login form ── --}}
                        <div x-show="tab === 'login'">
                            <h2 style="font-size:1.4rem;font-weight:700;color:white;margin:0 0 0.25rem;">Sign In</h2>
                            <p style="font-size:0.8rem;color:#4b5563;margin:0 0 1.5rem;">Welcome back to the table.</p>

                            @if(session('login_error'))
                                <div style="margin-bottom:1rem;padding:0.6rem 0.75rem;border-radius:0.5rem;font-size:0.8rem;background:rgba(239,68,68,0.1);color:#fca5a5;border:1px solid rgba(239,68,68,0.2);">
                                    {{ session('login_error') }}
                                </div>
                            @endif

                            <form method="POST" action="{{ route('login') }}" x-data="{ loading:false }" @submit="loading=true">
                                @csrf
                                <input type="hidden" name="_form" value="login">

                                <div style="margin-bottom:1rem;">
                                    <label for="home_login" style="display:block;font-size:0.7rem;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#6b7280;margin-bottom:0.4rem;">Email or Username</label>
                                    <input id="home_login" name="login" type="text"
                                        class="input @error('login') border-red-500 @enderror"
                                        value="{{ old('login') }}" placeholder="you@example.com"
                                        autocomplete="username" autofocus
                                        style="width:100%;box-sizing:border-box;">
                                    @error('login')<p class="form-error">{{ $message }}</p>@enderror
                                </div>

                                <div style="margin-bottom:1.25rem;">
                                    <label for="home_pass" style="display:block;font-size:0.7rem;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#6b7280;margin-bottom:0.4rem;">Password</label>
                                    <input id="home_pass" name="password" type="password"
                                        class="input @error('password') border-red-500 @enderror"
                                        placeholder="••••••••" autocomplete="current-password"
                                        style="width:100%;box-sizing:border-box;">
                                    @error('password')<p class="form-error">{{ $message }}</p>@enderror
                                </div>

                                <div style="display:flex;align-items:center;margin-bottom:1.5rem;">
                                    <input id="remember_home" name="remember" type="checkbox" style="margin-right:0.5rem;accent-color:#c9a227;width:14px;height:14px;">
                                    <label for="remember_home" style="font-size:0.8rem;color:#6b7280;cursor:pointer;">Remember me</label>
                                </div>

                                <button type="submit" class="btn btn-gold" style="width:100%;padding:0.7rem;font-size:0.9rem;" :disabled="loading">
                                    <span x-show="!loading">Sign In →</span>
                                    <span x-show="loading" x-cloak>Signing in…</span>
                                </button>

                                <p style="text-align:center;margin-top:1.25rem;font-size:0.8rem;color:#4b5563;">
                                    No account?
                                    <button type="button" @click="tab = 'register'"
                                        style="background:none;border:none;cursor:pointer;font-size:0.8rem;font-weight:600;padding:0;color:#c9a227;text-decoration:underline;text-underline-offset:3px;">
                                        Create one free
                                    </button>
                                </p>
                            </form>
                        </div>

                        {{-- ── Register form ── --}}
                        <div x-show="tab === 'register'" x-cloak>
                            <h2 style="font-size:1.4rem;font-weight:700;color:white;margin:0 0 0.25rem;">Create Account</h2>
                            <p style="font-size:0.8rem;color:#4b5563;margin:0 0 1.5rem;">Join the table for free.</p>

                            @if(session('invite_info'))
                                <div style="margin-bottom:1rem;padding:0.6rem 0.75rem;border-radius:0.5rem;font-size:0.8rem;background:rgba(22,101,52,0.25);color:#86efac;border:1px solid rgba(22,101,52,0.4);">
                                    {{ session('invite_info') }}
                                </div>
                            @endif

                            <form method="POST" action="{{ route('register') }}"
                                x-data="{
                                    loading: false,
                                    un: '{{ old('username') }}',
                                    unStatus: '{{ old('username') ? 'checking' : '' }}',
                                    unTimer: null,
                                    checkUrl: '{{ route('username.check') }}',
                                    checkUsername() {
                                        clearTimeout(this.unTimer);
                                        const u = this.un.trim();
                                        if (u.length < 3) { this.unStatus = u.length ? 'short' : ''; return; }
                                        if (!/^[a-zA-Z0-9_]+$/.test(u)) { this.unStatus = 'invalid'; return; }
                                        this.unStatus = 'checking';
                                        this.unTimer = setTimeout(() => {
                                            fetch(this.checkUrl + '?username=' + encodeURIComponent(u))
                                                .then(r => r.json())
                                                .then(d => { this.unStatus = d.available ? 'available' : 'taken'; })
                                                .catch(() => { this.unStatus = ''; });
                                        }, 500);
                                    }
                                }"
                                @submit="loading=true"
                                x-init="un && checkUsername()">
                                @csrf
                                <input type="hidden" name="_form" value="register">

                                <div style="margin-bottom:0.875rem;">
                                    <label for="reg_username" style="display:block;font-size:0.7rem;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#6b7280;margin-bottom:0.4rem;">Username</label>
                                    <input id="reg_username" name="username" type="text"
                                        class="input @error('username') border-red-500 @enderror"
                                        x-model="un" @input="checkUsername()"
                                        placeholder="ace_player" autocomplete="username"
                                        style="width:100%;box-sizing:border-box;" autofocus>
                                    {{-- Availability indicator --}}
                                    <p x-show="unStatus === 'checking'" style="font-size:0.72rem;color:#6b7280;margin-top:0.3rem;" x-cloak>Checking…</p>
                                    <p x-show="unStatus === 'available'" style="font-size:0.72rem;color:#86efac;margin-top:0.3rem;" x-cloak>✓ Username available</p>
                                    <p x-show="unStatus === 'taken'" style="font-size:0.72rem;color:#fca5a5;margin-top:0.3rem;" x-cloak>✗ Already taken</p>
                                    <p x-show="unStatus === 'invalid'" style="font-size:0.72rem;color:#fca5a5;margin-top:0.3rem;" x-cloak>✗ Letters, numbers, and underscores only</p>
                                    <p x-show="unStatus === 'short'" style="font-size:0.72rem;color:#6b7280;margin-top:0.3rem;" x-cloak>Minimum 3 characters</p>
                                    @error('username')<p class="form-error">{{ $message }}</p>@enderror
                                </div>

                                <div style="margin-bottom:0.875rem;">
                                    <label for="reg_email" style="display:block;font-size:0.7rem;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#6b7280;margin-bottom:0.4rem;">Email</label>
                                    <input id="reg_email" name="email" type="email"
                                        class="input @error('email') border-red-500 @enderror"
                                        value="{{ old('email', $prefillEmail ?? '') }}"
                                        placeholder="you@example.com" autocomplete="email"
                                        style="width:100%;box-sizing:border-box;">
                                    @error('email')<p class="form-error">{{ $message }}</p>@enderror
                                </div>

                                <div style="margin-bottom:0.875rem;">
                                    <label for="reg_pw" style="display:block;font-size:0.7rem;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#6b7280;margin-bottom:0.4rem;">Password <span style="color:#4b5563;font-weight:400;text-transform:none;letter-spacing:0;">(min 8 chars)</span></label>
                                    <input id="reg_pw" name="password" type="password"
                                        class="input @error('password') border-red-500 @enderror"
                                        placeholder="••••••••" autocomplete="new-password"
                                        style="width:100%;box-sizing:border-box;">
                                    @error('password')<p class="form-error">{{ $message }}</p>@enderror
                                </div>

                                <div style="margin-bottom:1.5rem;">
                                    <label for="reg_pw2" style="display:block;font-size:0.7rem;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#6b7280;margin-bottom:0.4rem;">Confirm Password</label>
                                    <input id="reg_pw2" name="password_confirmation" type="password"
                                        class="input" placeholder="••••••••" autocomplete="new-password"
                                        style="width:100%;box-sizing:border-box;">
                                </div>

                                <button type="submit" class="btn btn-gold"
                                    style="width:100%;padding:0.7rem;font-size:0.9rem;"
                                    :disabled="loading || unStatus === 'taken' || unStatus === 'invalid'">
                                    <span x-show="!loading">Create Account →</span>
                                    <span x-show="loading" x-cloak>Creating…</span>
                                </button>

                                <p style="text-align:center;margin-top:1.25rem;font-size:0.8rem;color:#4b5563;">
                                    Already have an account?
                                    <button type="button" @click="tab = 'login'"
                                        style="background:none;border:none;cursor:pointer;font-size:0.8rem;font-weight:600;padding:0;color:#c9a227;text-decoration:underline;text-underline-offset:3px;">
                                        Sign in
                                    </button>
                                </p>
                            </form>
                        </div>

                    </div>{{-- end auth card --}}

                    @endif{{-- end auth()->check() --}}

                    <p style="text-align:center;margin-top:0.875rem;font-size:0.7rem;color:rgba(107,114,128,0.45);">
                        ♠ Your game nights, your records, your table.
                    </p>
                </div>{{-- end auth card --}}

            </div>
        </div>
    </div>{{-- end hero --}}

    {{-- ── FEATURE CARDS ───────────────────────────────── --}}
    <div class="relative py-12 px-6" style="z-index:1; background: var(--color-surface); border-top: 1px solid var(--color-border);">
        <div class="max-w-6xl mx-auto" style="display:grid; grid-template-columns:repeat(3,1fr); gap:1.25rem;">

            <div style="background:linear-gradient(145deg,#1a1a2e,#12121e); border:1px solid rgba(201,162,39,0.15); border-top:2px solid rgba(201,162,39,0.4); border-radius:1rem; padding:2rem 1.75rem; text-align:center; box-shadow:0 8px 32px rgba(0,0,0,0.4);">
                <div style="font-size:2.5rem; margin-bottom:1rem; filter:drop-shadow(0 2px 8px rgba(201,162,39,0.3));">🏆</div>
                <div style="font-size:1rem; font-weight:700; color:white; margin-bottom:0.5rem;">Season Champions</div>
                <div style="width:2rem; height:2px; background:linear-gradient(90deg,transparent,#c9a227,transparent); margin:0 auto 0.875rem;"></div>
                <div style="font-size:0.8rem; line-height:1.6; color:#6b7280;">Rivalries tracked across every game night all year long. Who's dominating the table?</div>
            </div>

            <div style="background:linear-gradient(145deg,#1a1a2e,#12121e); border:1px solid rgba(201,162,39,0.15); border-top:2px solid rgba(201,162,39,0.4); border-radius:1rem; padding:2rem 1.75rem; text-align:center; box-shadow:0 8px 32px rgba(0,0,0,0.4);">
                <div style="font-size:2.5rem; margin-bottom:1rem; filter:drop-shadow(0 2px 8px rgba(201,162,39,0.3));">📸</div>
                <div style="font-size:1rem; font-weight:700; color:white; margin-bottom:0.5rem;">Photo Galleries</div>
                <div style="width:2rem; height:2px; background:linear-gradient(90deg,transparent,#c9a227,transparent); margin:0 auto 0.875rem;"></div>
                <div style="font-size:0.8rem; line-height:1.6; color:#6b7280;">Every game night gets its own gallery. Upload, browse, and relive the best moments.</div>
            </div>

            <div style="background:linear-gradient(145deg,#1a1a2e,#12121e); border:1px solid rgba(201,162,39,0.15); border-top:2px solid rgba(201,162,39,0.4); border-radius:1rem; padding:2rem 1.75rem; text-align:center; box-shadow:0 8px 32px rgba(0,0,0,0.4);">
                <div style="font-size:2.5rem; margin-bottom:1rem; filter:drop-shadow(0 2px 8px rgba(201,162,39,0.3));">🃏</div>
                <div style="font-size:1rem; font-weight:700; color:white; margin-bottom:0.5rem;">Invite Anyone</div>
                <div style="width:2rem; height:2px; background:linear-gradient(90deg,transparent,#c9a227,transparent); margin:0 auto 0.875rem;"></div>
                <div style="font-size:0.8rem; line-height:1.6; color:#6b7280;">Share a code or a link. No friction, no gatekeeping — your crew is in within seconds.</div>
            </div>

        </div>
    </div>

    <footer class="relative text-center text-xs py-4" style="z-index:1; color:var(--color-muted); background:var(--color-surface);">
        <span style="color:var(--color-gold);">♠</span>
        Poker Night
        <span style="color:var(--color-gold); margin-left:4px;">♦</span>
    </footer>

</div>
@endsection
