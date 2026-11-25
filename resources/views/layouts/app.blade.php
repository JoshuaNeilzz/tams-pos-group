<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Home' }} | TAMS POS</title>
    <script>try { document.documentElement.dataset.theme = localStorage.getItem('tams-theme') || 'light'; } catch (e) {}</script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v=7">
    <link rel="stylesheet" href="{{ asset('css/design.css') }}?v=5">
    <link rel="stylesheet" href="{{ asset('css/motion.css') }}?v=1">
</head>
<body>
<a class="skip-link" href="#main-content">Skip to content</a>
<header class="site-header">
    <a class="brand" href="{{ route('home') }}"><svg class="brand-symbol" viewBox="0 0 40 40" fill="none" aria-hidden="true"><rect x="2" y="2" width="36" height="36" rx="10" fill="currentColor"/><path d="M11 14h18M20 14v15M12 23h5m6 0h5" stroke="var(--bg)" stroke-width="3" stroke-linecap="square"/></svg><span class="brand-word">TAMS<small>POINT OF SALE</small></span></a>
    <nav aria-label="Main navigation">
        @auth
            @if(auth()->user()->isActiveStaff())
                @foreach (['dashboard' => 'Dashboard', 'products.index' => 'Snacks & stock', 'customers.index' => 'Customers', 'users.index' => 'Staff', 'sales.index' => 'Sales', 'orders.index' => 'Orders'] as $route => $label)
                    <a href="{{ route($route) }}" class="{{ request()->routeIs(explode('.', $route)[0].'*') ? 'active' : '' }}">{{ $label }}</a>
                @endforeach
            @else
                <a href="{{ route('account') }}" class="{{ request()->routeIs('account') ? 'active' : '' }}">My account</a>
                <a href="{{ route('about') }}">How it works</a>
            @endif
        @else
            <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
            <a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'active' : '' }}">How it works</a>
            <a href="{{ route('staff.login') }}" class="{{ request()->is('staff/*') ? 'active' : '' }}">Staff access</a>
        @endauth
    </nav>
    <div class="header-actions">
        <button class="theme-button" type="button" id="theme-toggle" aria-label="Switch color theme" title="Switch light or dark theme">
            <svg class="theme-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M2 12h2m16 0h2M5 5l1.5 1.5m11 11L19 19M5 19l1.5-1.5m11-11L19 5"/></svg>
            <svg class="theme-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M20.6 14.2A8.8 8.8 0 0 1 9.8 3.4a8.8 8.8 0 1 0 10.8 10.8Z"/></svg>
        </button>
        @auth
            <span class="signed-in">{{ auth()->user()->username }}</span>
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="button secondary small" type="submit">Sign out</button></form>
        @else
            <a class="plain-link" href="{{ route('login') }}">Sign in</a>
            <a class="button small" href="{{ route('register') }}">Create account</a>
        @endauth
    </div>
</header>
<main class="container" id="main-content">
    @if(session('success'))<div class="notice success" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())
        <div class="notice error" role="alert"><strong>Please check your entries.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    @yield('content')
</main>
<footer class="site-footer"><a class="footer-wordmark" href="{{ route('home') }}">tams<span>.</span></a><div class="footer-description">A little order.<br>A lot of possibility.</div><div class="footer-links"><a href="{{ route('about') }}">How it works &nearr;</a><a href="{{ route('staff.login') }}">Staff access &nearr;</a></div><small>&copy; {{ date('Y') }} TAMS POS<br>IT0049 &middot; <a href="{{ route('about') }}#team">CipherStack</a></small></footer>
<script>
    document.getElementById('theme-toggle').addEventListener('click', () => {
        const next = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
        document.documentElement.dataset.theme = next;
        try { localStorage.setItem('tams-theme', next); } catch (e) {}
    });
</script>
</body>
</html>
