@extends('layouts.app')
@section('content')
@php($isStaff = $accountType === 'staff')
<div class="auth-layout">
    <section class="auth-description"><p class="eyebrow">TAMS POS / REGISTRATION</p><h1>Create {{ $isStaff ? 'staff' : 'user' }} account</h1><p>{{ $isStaff ? 'Submit your staff profile. An approved staff member must approve your account before you can use management pages.' : 'Register to buy and reserve snacks for cash-on-pickup and track your own orders and purchases.' }}</p><a class="text-link" href="{{ $isStaff ? route('register') : route('staff.register') }}">{{ $isStaff ? 'Create a user account instead' : 'Need a staff account?' }} ↗</a></section>
    <section class="auth-form"><h2>{{ $isStaff ? 'Staff registration' : 'User registration' }}</h2>
        <form method="POST" action="{{ $isStaff ? route('staff.register') : route('register') }}" enctype="multipart/form-data">@csrf
            <label for="full_name">Full name</label><input id="full_name" name="full_name" value="{{ old('full_name') }}" maxlength="100" required autocomplete="name">
            <label for="username">Username</label><input id="username" name="username" value="{{ old('username') }}" maxlength="50" pattern="[A-Za-z0-9_-]+" required autocomplete="username"><p class="form-hint">Use letters, numbers, underscores, or hyphens.</p>
            <label for="email">Email address</label><input id="email" type="email" name="email" value="{{ old('email') }}" maxlength="100" required autocomplete="email">
            <div class="input-grid"><div><label for="password">Password</label><input id="password" type="password" name="password" minlength="8" maxlength="72" required autocomplete="new-password"></div><div><label for="password_confirmation">Confirm password</label><input id="password_confirmation" type="password" name="password_confirmation" minlength="8" maxlength="72" required autocomplete="new-password"></div></div><p class="form-hint">Use at least 8 characters.</p>
            @if($isStaff)<label for="avatar">Avatar <span class="muted">(optional)</span></label><input id="avatar" name="avatar" type="file" accept=".jpg,.jpeg,.png,.webp"><p class="form-hint">JPG, PNG, or WebP, up to 2 MB. Prepared as a 256 × 256 avatar.</p>@endif
            <button class="button full registration-submit" type="submit">{{ $isStaff ? 'Submit staff registration' : 'Create user account' }} <span>↗</span></button>
        </form>
        <p class="auth-switch">Already registered? <a href="{{ $isStaff ? route('staff.login') : route('login') }}">{{ $isStaff ? 'Staff sign in' : 'User sign in' }}</a></p>
    </section>
</div>
@endsection
