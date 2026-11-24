@extends('layouts.app')
@section('content')
<section class="page-heading"><p class="eyebrow">{{ strtoupper($account->role) }} ACCOUNT / {{ strtoupper($account->status) }}</p><h1>My account</h1><p>{{ $account->full_name }} · {{ $account->email }}</p></section>
@if($account->role === 'staff')
    @if($account->status === 'pending')<div class="notice"><h2>Staff approval pending</h2><p>Your registration has been received. An existing staff member must open Staff and approve your account before you can access management pages.</p></div>@else<div class="notice"><p>Your staff account is active.</p><a class="button" href="{{ route('dashboard') }}">Open staff dashboard</a></div>@endif
@else
    <section><div class="section-heading"><h2>Purchase history</h2><span class="mono muted">YOUR TRANSACTIONS</span></div><p class="muted">Purchases appear when staff records a sale using your linked customer record.</p>
        <div class="table-card"><table><thead><tr><th>Sale</th><th>Product</th><th>Quantity</th><th>Total</th><th>Date</th></tr></thead><tbody>@forelse($sales as $sale)<tr><td>#{{ str_pad($sale->id, 5, '0', STR_PAD_LEFT) }}</td><td>{{ $sale->product_name }}</td><td>{{ $sale->quantity }}</td><td>₱{{ number_format($sale->total_price, 2) }}</td><td>{{ $sale->created_at->format('M d, Y · g:i A') }}</td></tr>@empty<tr><td colspan="5">No purchases have been recorded for this account.</td></tr>@endforelse</tbody></table></div><div class="pagination">{{ $sales->links('pagination::simple-default') }}</div>
    </section>
    <section class="system-section"><div class="section-heading"><h2>Product catalog</h2><span class="mono muted">VIEW ONLY</span></div><div class="product-grid">@forelse($products as $product)<article class="product-card"><div class="product-image">@if($product->image)<img src="{{ url('media/'.$product->image) }}" alt="{{ $product->name }}" loading="lazy">@else<span class="product-placeholder">No image</span>@endif</div><div class="product-info"><h3>{{ $product->name }}</h3><strong>₱{{ number_format($product->price, 2) }}</strong><small class="muted">{{ $product->stock_quantity ? 'In stock' : 'Out of stock' }}</small></div></article>@empty<p class="muted">No products are available.</p>@endforelse</div><div class="pagination">{{ $products->links('pagination::simple-default') }}</div></section>
@endif
@endsection
