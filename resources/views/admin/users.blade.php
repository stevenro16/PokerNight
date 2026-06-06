@extends('layouts.main')
@section('title', 'Manage Users – Admin')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-white">Manage Users</h1>
</div>

<div class="card overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr style="border-bottom: 1px solid var(--color-border);">
                <th class="text-left px-4 py-3 text-xs uppercase tracking-wider text-gray-500">User</th>
                <th class="text-left px-4 py-3 text-xs uppercase tracking-wider text-gray-500">Email</th>
                <th class="text-center px-4 py-3 text-xs uppercase tracking-wider text-gray-500">Role</th>
                <th class="text-center px-4 py-3 text-xs uppercase tracking-wider text-gray-500">Status</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr style="border-bottom: 1px solid var(--color-border);">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0"
                            style="background-color: var(--color-felt); color: var(--color-gold);">
                            {{ strtoupper(substr($user->username, 0, 1)) }}
                        </div>
                        <span class="text-white font-medium">{{ $user->username }}</span>
                    </div>
                </td>
                <td class="px-4 py-3 text-gray-400">{{ $user->email }}</td>
                <td class="px-4 py-3 text-center">
                    <form method="POST" action="{{ route('admin.users.role', $user) }}" class="flex items-center justify-center gap-1">
                        @csrf
                        <select name="role" onchange="this.form.submit()"
                            class="text-xs rounded px-2 py-1 border"
                            style="background-color: var(--color-card-bg); color: #e5e7eb; border-color: var(--color-border);">
                            @foreach(['USER','ADMIN','SUPERADMIN'] as $r)
                                <option value="{{ $r }}" {{ $user->role === $r ? 'selected' : '' }}>{{ $r }}</option>
                            @endforeach
                        </select>
                    </form>
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="badge {{ $user->isActive ? 'badge-green' : 'badge-red' }}">
                        {{ $user->isActive ? 'Active' : 'Disabled' }}
                    </span>
                </td>
                <td class="px-4 py-3 text-right">
                    @if($user->id !== auth()->id())
                    <form method="POST" action="{{ route('admin.users.toggle', $user) }}">
                        @csrf
                        <button type="submit" class="btn {{ $user->isActive ? 'btn-danger' : 'btn-ghost' }} text-xs py-1 px-3">
                            {{ $user->isActive ? 'Disable' : 'Enable' }}
                        </button>
                    </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $users->links() }}</div>
@endsection
