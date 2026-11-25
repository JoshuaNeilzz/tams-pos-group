@extends('layouts.app')
@section('content')
<section class="page-heading"><p class="eyebrow"><span class="tiny-square"></span> A LITTLE GUIDANCE GOES A LONG WAY</p><h1>A good day, step by step.</h1><p>Get to know your store's everyday workspace. Products, people, and sales, with a place for everything.</p></section>
<div class="guide-layout">
<section><p class="eyebrow">01 / MAKE YOURSELF AT HOME</p><h2>Start with the right account.</h2><p><a href="{{ route('register') }}">Create a user account</a> to browse the catalog and see your own purchase history.</p><p>Working in the store? <a href="{{ route('staff.register') }}">Create a staff account</a>. An existing approved staff member will review your request before you can access management tools.</p></section>
<section><p class="eyebrow">02 / A PLACE FOR EVERYTHING</p><h2>Get your store in order.</h2><p>Approved staff can add products with prices, stock levels, and images, keep customer details organized, and manage staff profiles.</p><p>User registration creates a linked customer record. Choose that customer when recording their sale so they can find it in their purchase history.</p></section>
<section><p class="eyebrow">03 / READY FOR THE NEXT SALE</p><h2>You sell. We keep count.</h2><p>Open Sales, choose Record sale, select a product, add an optional customer, and enter the quantity. The system calculates the total and updates your stock together.</p><p>If there isn't enough stock, you'll see a message before the sale goes through. Walk-in customers are welcome, too.</p></section>
<section><p class="eyebrow">04 / THE FULL PICTURE</p><h2>Look back. Move forward.</h2><p>Staff can review all recorded transactions in Sales history. Users see only purchases linked to their own customer record in My account.</p><p>Sale records preserve the product, customer, and staff names from the time of purchase, even when those details change later.</p></section>
</div>
@endsection
