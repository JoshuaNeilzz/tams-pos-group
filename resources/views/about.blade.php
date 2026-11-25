@extends('layouts.app')
@section('content')
<section class="page-heading"><p class="eyebrow"><span class="tiny-square"></span> TAMS POS / THE SARI-SARI STORE GUIDE</p><h1>Snacks, stock, and suki.</h1><p>A classroom sari-sari store for Philippine chips and packaged snacks. Shop for pickup as a customer, or keep the shelves running as approved staff. Prices are sample prices for evaluation.</p></section>
<div class="guide-layout">
<section><p class="eyebrow">01 / CUSTOMERS</p><h2>Buy your merienda.</h2><p><a href="{{ route('register') }}">Create a user account</a> or <a href="{{ route('login') }}">sign in</a>. In My account, find Product catalog, choose the number of packs, and select Buy / reserve.</p><p>We reserve the available packs immediately and show your order total. Pay in cash when you pick up at the store. No card or online payment is collected.</p></section>
<section><p class="eyebrow">02 / STAFF &amp; STOCK</p><h2>Keep the shelves ready.</h2><p><a href="{{ route('staff.login') }}">Sign in as approved staff</a>. Open Snacks &amp; stock, then Add snack for a new product. Enter its name, selling price per pack, and starting stock.</p><p>For a supplier delivery, open + Add stock on the snack card. Enter only the new packs received; they are added to the current available quantity. Edits can correct a price or stock count. Uploaded photos override the built-in packet sketches.</p></section>
<section><p class="eyebrow">03 / CASH AT PICKUP</p><h2>From order to sale.</h2><p>Staff opens Orders. After collecting cash and handing over the snacks, select Paid &amp; collected. This adds a completed sale to Sales history and the shopper's Purchase history.</p><p>A pending order is a reservation, not paid revenue. Customers can cancel their own pending orders; staff can cancel a no-show. Cancellation returns reserved packs to stock. Completion never deducts the same packs twice.</p></section>
<section><p class="eyebrow">04 / WALK-IN CUSTOMERS</p><h2>Sell at the counter, too.</h2><p>Use Sales &rarr; Record sale for a new walk-in purchase. Select a snack, optional customer, and number of packs. Totals are calculated from the stored selling price; stock updates when you confirm.</p><p>For an existing pickup order, complete it through Orders instead of recording a second counter sale. Orders and counter sales both reject quantities above available stock. New staff registrations need approval before they can manage the store.</p></section>
</div>
<section class="team-section" id="team" aria-labelledby="team-title">
    <div class="team-intro">
        <p class="eyebrow"><span class="tiny-square"></span> THE PEOPLE BEHIND TAMS POS</p>
        <h2 id="team-title">Meet CipherStack.</h2>
        <p>Four minds. One shared project. Built together for IT0049 Web System Technologies.</p>
    </div>
    <ul class="team-members" aria-label="CipherStack team members">
        <li><span aria-hidden="true">01</span>Joshua Neil Biglete</li>
        <li><span aria-hidden="true">02</span>Leah Buenaventura</li>
        <li><span aria-hidden="true">03</span>Gabrielle Crisostomo</li>
        <li><span aria-hidden="true">04</span>Kurt Airry Wenceslao</li>
    </ul>
</section>
@endsection
