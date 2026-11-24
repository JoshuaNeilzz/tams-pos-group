@extends('layouts.app')
@section('content')
<section class="page-heading">
    <p class="eyebrow">STAFF / PROFILE</p>
    <h1>{{ $user->full_name }}</h1>
    <p>Staff profile and account status.</p>
</section>
<div class="form-card">
    <div class="person-cell">
        @if($user->avatar)<img class="avatar" src="{{ url('media/'.$user->avatar) }}" alt="{{ $user->full_name }}">@else<span class="initial-avatar">{{ mb_substr($user->full_name, 0, 1) }}</span>@endif
        <strong>{{ $user->full_name }}</strong>
    </div>
    <div class="summary-line"><span>Username</span><strong>{{ $user->username }}</strong></div>
    <div class="summary-line"><span>Email</span><strong>{{ $user->email ?: 'Not provided' }}</strong></div>
    <div class="summary-line"><span>Workspace access</span><span class="badge">{{ $user->status === 'pending' ? 'Pending approval' : ($user->password ? 'Active' : 'Password not set') }}</span></div>
    <div class="summary-line"><span>Added</span><strong>{{ $user->created_at->format('M d, Y') }}</strong></div>
    <div class="form-actions"><a class="button" href="{{ route('users.edit', $user) }}">Edit staff member</a><a href="{{ route('users.index') }}">Back to staff</a></div>
</div>
@endsection
