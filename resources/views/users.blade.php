@extends('layouts.app')
@section('content')
<div class="section-heading page-heading">
    <div><p class="eyebrow">STAFF MANAGEMENT</p><h1>Staff accounts</h1><p>Manage staff profiles, sign-in access, and registration requests.</p></div>
    <a class="button" href="{{ route('users.create') }}">+ Add staff member</a>
</div>
<div class="table-card"><table>
    <thead><tr><th>Staff member</th><th>Username</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
    @forelse($users as $user)
        <tr>
            <td><div class="person-cell">
                @if($user->avatar)<img class="avatar" src="{{ url('media/'.$user->avatar) }}" alt="{{ $user->full_name }}">@else<span class="initial-avatar">{{ mb_substr($user->full_name, 0, 1) }}</span>@endif
                <div><strong>{{ $user->full_name }}</strong><small>{{ $user->email ?: 'No email added' }}</small></div>
            </div></td>
            <td>{{ $user->username }}</td>
            <td><span class="badge {{ $user->status === 'pending' || !$user->password ? 'warning' : '' }}">{{ $user->status === 'pending' ? 'Awaiting approval' : ($user->password ? 'Active' : 'Password not set') }}</span></td>
            <td><div class="row-actions">
                @if($user->status === 'pending')<form method="POST" action="{{ route('users.approve', $user) }}">@csrf @method('PATCH')<button class="approve-button" type="submit">Approve</button></form>@endif
                <a href="{{ route('users.show', $user) }}">View</a>
                <a href="{{ route('users.edit', $user) }}">Edit</a>
                @if(auth()->id() !== $user->id)
                    <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Remove this staff account and their sign-in access?');">@csrf @method('DELETE')<button class="text-button" type="submit">Remove</button></form>
                @else<span class="muted">You</span>@endif
            </div></td>
        </tr>
    @empty<tr><td colspan="4">No staff records.</td></tr>@endforelse
    </tbody>
</table></div>
<p class="record-count">{{ $users->count() }} staff records. Pending accounts cannot open management pages.</p>
@endsection
