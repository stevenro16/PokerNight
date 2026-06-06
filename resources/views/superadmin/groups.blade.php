@extends('layouts.main')
@section('title', 'All Groups – Super Admin')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-white">All Groups</h1>
</div>

<div class="card overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr style="border-bottom: 1px solid var(--color-border);">
                <th class="text-left px-4 py-3 text-xs uppercase tracking-wider text-gray-500">Group</th>
                <th class="text-left px-4 py-3 text-xs uppercase tracking-wider text-gray-500">Owner</th>
                <th class="text-center px-4 py-3 text-xs uppercase tracking-wider text-gray-500">Members</th>
                <th class="text-center px-4 py-3 text-xs uppercase tracking-wider text-gray-500">Nights</th>
                <th class="text-center px-4 py-3 text-xs uppercase tracking-wider text-gray-500">Status</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($groups as $group)
            <tr style="border-bottom: 1px solid var(--color-border);">
                <td class="px-4 py-3">
                    <div class="font-medium text-white">{{ $group->name }}</div>
                    @if($group->description)
                        <div class="text-xs text-gray-500 truncate max-w-xs">{{ $group->description }}</div>
                    @endif
                </td>
                <td class="px-4 py-3 text-gray-400">{{ $group->owner->username ?? 'N/A' }}</td>
                <td class="px-4 py-3 text-center text-gray-300">{{ $group->memberships_count }}</td>
                <td class="px-4 py-3 text-center text-gray-300">{{ $group->poker_nights_count }}</td>
                <td class="px-4 py-3 text-center">
                    <span class="badge {{ $group->isActive ? 'badge-green' : 'badge-red' }}">
                        {{ $group->isActive ? 'Active' : 'Removed' }}
                    </span>
                </td>
                <td class="px-4 py-3 text-right">
                    <form method="POST" action="{{ route('superadmin.groups.toggle', $group) }}">
                        @csrf
                        <button type="submit" class="btn {{ $group->isActive ? 'btn-danger' : 'btn-ghost' }} text-xs py-1 px-3"
                            onclick="return confirm('{{ $group->isActive ? 'Take down this group?' : 'Restore this group?' }}')">
                            {{ $group->isActive ? 'Take Down' : 'Restore' }}
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $groups->links() }}</div>
@endsection
