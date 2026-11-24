@extends('layouts.app')
@section('content')
<section class="home-intro">
    <div class="home-intro-copy">
    <p class="eyebrow">IT0049 / WEB SYSTEM TECHNOLOGIES</p>
    <h1>Point-of-sale &amp;<br><span class="heading-accent">inventory</span><br>system.</h1>
    <p class="intro-description">Manage store inventory, customer records, and sales transactions. Choose an account type below to get started.</p>
    @auth
        <a class="button" href="{{ auth()->user()->isActiveStaff() ? route('dashboard') : route('account') }}">Open {{ auth()->user()->isActiveStaff() ? 'dashboard' : 'my account' }} <span>↗</span></a>
    @else
        <div class="intro-actions"><a class="button accent-button" href="{{ route('register') }}">Create an account <span>↗</span></a><a class="text-link" href="#access-title">Choose your account type</a></div>
    @endauth
    <div class="intro-caption"><span></span> Products / People / Transactions</div>
    </div>
    <figure class="checkout-visual">
        <img src="{{ asset('images/checkout-hero.png') }}" alt="A stationery store checkout with a payment terminal, shopping bag, notebooks, and pens" width="1024" height="768" fetchpriority="high">
        <figcaption><span class="visual-mark">T.</span><div><strong>TAMS POS</strong><small>Store management &amp; sales</small></div><span class="visual-arrow" aria-hidden="true">↗</span></figcaption>
    </figure>
</section>
<section class="access-section" aria-labelledby="access-title">
    <div class="section-heading"><h2 id="access-title">Account access</h2><span class="mono muted">01 / GET STARTED</span></div>
    <div class="access-row">
        <span class="row-number">01</span>
        <div><h3>User account</h3><p>View available products and your own recorded purchases. User accounts cannot access store management.</p></div>
        <div class="access-links"><a class="button secondary" href="{{ route('login') }}">User sign in</a><a class="text-link" href="{{ route('register') }}">Create user account ↗</a></div>
    </div>
    <div class="access-row">
        <span class="row-number">02</span>
        <div><h3>Staff account</h3><p>Manage products, customers, and staff. Record sales and review transaction history. New registrations require staff approval.</p></div>
        <div class="access-links"><a class="button secondary" href="{{ route('staff.login') }}">Staff sign in</a><a class="text-link" href="{{ route('staff.register') }}">Create staff account ↗</a></div>
    </div>
</section>
<section class="system-section">
    <div class="section-heading"><h2>System modules</h2><a class="text-link" href="{{ route('about') }}">Read the system guide ↗</a></div>
    <div class="module-list">
        <div><span>01 / INVENTORY</span><h3>Product management</h3><p>Maintain product records and the quantities available for sale.</p><ul class="detail-tags" aria-label="Product features"><li>Product images</li><li>Pricing</li><li>Stock levels</li><li>Archiving</li></ul></div>
        <div><span>02 / RECORDS</span><h3>Customer management</h3><p>Keep contact records and connect registered users to their purchases.</p><ul class="detail-tags" aria-label="Customer features"><li>Contact details</li><li>User accounts</li><li>Purchase history</li></ul></div>
        <div><span>03 / ACCESS</span><h3>Staff management</h3><p>Manage staff profiles and approve new staff registrations.</p><ul class="detail-tags" aria-label="Staff features"><li>Staff profiles</li><li>Avatars</li><li>Approval</li></ul></div>
        <div><span>04 / TRANSACTIONS</span><h3>Sales recording</h3><p>Record a sale with stock validation and automatic inventory deductions.</p><ul class="detail-tags" aria-label="Sales features"><li>Quantity validation</li><li>Sale totals</li><li>Transaction history</li></ul></div>
    </div>
</section>
@endsection
