@extends('layouts.app')
@section('content')
<section class="page-heading"><p class="eyebrow">PROJECT DOCUMENTATION</p><h1>How the system works</h1><p>TAMS POS is a group project for IT0049. It uses Laravel, Blade, and MySQL to manage products, customer records, staff accounts, and sales.</p></section>
<div class="guide-layout">
    <section><h2>1. Create an account</h2><p><a href="{{ route('register') }}">Create a user account</a> to view the catalog and your own purchase history. A user account does not have management permissions.</p><p><a href="{{ route('staff.register') }}">Create a staff account</a> to request management access. An existing approved staff member reviews the registration in Staff and selects Approve.</p><p>The initial staff account for a fresh installation is created by the database seeder. Setup commands are included in the repository README.</p></section>
    <section><h2>2. Set up store records</h2><p>Approved staff can add products with prices, quantities, and images; add customer contact details; and create or update staff profiles with passwords and avatars.</p><p>Registering a user creates a linked customer record. Staff should select that record when recording a sale for the user.</p></section>
    <section><h2>3. Record a sale</h2><p>Open Sales → Record sale, select a product, choose an optional customer, enter the quantity, and submit. The server calculates the total and reduces stock in the same database transaction.</p><p>If the requested quantity exceeds available stock, the sale is rejected with a message and no inventory change.</p></section>
    <section><h2>4. Review transactions</h2><p>Staff can view all transactions in Sales history. Users can view only purchases linked to their own customer record in My account.</p><p>Historical product, customer, and staff names remain on sales after later profile changes or product archiving.</p></section>
</div>
@endsection
