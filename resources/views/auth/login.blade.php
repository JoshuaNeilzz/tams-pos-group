@extends('layouts.app', ['title' => ($accountType ?? 'user') === 'staff' ? 'Staff sign in' : 'User sign in'])
@section('content')
@php($isStaff = ($accountType ?? 'user') === 'staff')
<div class="auth-layout">
    <section class="auth-description"><p class="eyebrow">TAMS POS / {{ $isStaff ? 'STAFF' : 'USER' }}</p><h1>{{ $isStaff ? 'Staff sign in' : 'User sign in' }}</h1><p>{{ $isStaff ? 'Access products, customer records, staff management, and sales. New staff accounts need approval.' : 'Access the product catalog and your purchase history.' }}</p><a class="text-link" href="{{ $isStaff ? route('login') : route('staff.login') }}">{{ $isStaff ? 'Looking for user sign in?' : 'Looking for staff sign in?' }} &nearr;</a><div class="auth-note"><span class="tiny-square"></span><strong>A little order.<br>A lot of possibility.</strong><small>Your everyday store companion.</small></div></section>
    <section class="auth-form"><h2>Sign in</h2><form method="POST" action="{{ route('login') }}">@csrf
        <input type="hidden" name="account_type" value="{{ $isStaff ? 'staff' : 'user' }}">
        <label for="login">Username or email</label><input id="login" name="login" value="{{ old('login') }}" required autofocus autocomplete="username">
        <label for="password">Password</label><input id="password" type="password" name="password" required autocomplete="current-password">
        <label class="checkbox-label"><input type="checkbox" name="remember" value="1"> Keep me signed in</label>
        <button class="button full" type="submit">Sign in <span>&nearr;</span></button>
    </form><p class="auth-switch">No account yet? <a href="{{ $isStaff ? route('staff.register') : route('register') }}">Create {{ $isStaff ? 'staff' : 'user' }} account</a></p></section>
</div>
@endsection
