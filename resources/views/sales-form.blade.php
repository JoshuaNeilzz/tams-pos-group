@extends('layouts.app')
@section('content')
<div class="section-heading page-heading"><div><p class="eyebrow">AT THE COUNTER</p><h1>Record sale<span class="pink-text">.</span></h1><p>Select a snack and number of packs for a new counter sale. For a reserved pickup, complete it through Orders.</p></div><a class="button secondary" href="{{ route('sales.index') }}">Sales history ↗</a></div>
<div class="form-layout"><div class="form-card">@if($products->isEmpty())<div class="empty-state"><h2>No snacks in stock</h2><p>Add a snack or restock an existing snack before recording a sale.</p><a class="button" href="{{ route('products.create') }}">Add product ↗</a></div>@else<form method="POST" action="{{ route('sales.store') }}" id="sale-form">@csrf<label for="product_id">Choose a product</label><select id="product_id" name="product_id" required><option value="">Select from your catalog</option>@foreach($products as $product)<option value="{{ $product->id }}" data-price="{{ $product->price }}" data-stock="{{ $product->stock_quantity }}" @selected(old('product_id', request('product')) == $product->id)>{{ $product->name }} · ₱{{ number_format($product->price, 2) }} · {{ $product->stock_quantity }} available</option>@endforeach</select><label for="customer_id">Customer <span class="muted">(optional)</span></label><select id="customer_id" name="customer_id"><option value="">Walk-in customer</option>@foreach($customers as $customer)<option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>{{ $customer->full_name }} · {{ $customer->email }}</option>@endforeach</select><label for="quantity">Quantity</label><input id="quantity" name="quantity" type="number" min="1" step="1" value="{{ old('quantity', 1) }}" required><p class="form-hint" id="stock-hint">Select a product to see availability.</p><div class="form-actions"><button class="button" type="submit">Confirm & record sale ↗</button><a href="{{ route('dashboard') }}">Cancel</a></div></form>@endif</div><aside class="sale-summary"><p class="eyebrow">YOUR SALE AT A GLANCE</p><h2>Sale summary</h2><div class="summary-line"><span>Product</span><strong id="summary-product">No product selected</strong></div><div class="summary-line"><span>Quantity</span><strong id="summary-quantity">—</strong></div><div class="summary-total"><span>Total amount</span><strong id="summary-total">₱0.00</strong></div><p>Recorded by <strong>{{ auth()->user()->full_name }}</strong></p><small>Stock updates automatically when you confirm the sale.</small></aside></div>
<script>
const product = document.getElementById('product_id');
const quantity = document.getElementById('quantity');
if(product && quantity) {
    const update = () => {
        const selected = product.selectedOptions[0];
        const stock = Number(selected.dataset.stock || 0);
        const packs = Math.max(0, Number(quantity.value) || 0);
        document.getElementById('summary-product').textContent = product.value ? selected.textContent.split(' · ')[0] : 'No product selected';
        document.getElementById('summary-quantity').textContent = packs;
        document.getElementById('summary-total').textContent = new Intl.NumberFormat('en-PH', {style:'currency',currency:'PHP'}).format(Number(selected.dataset.price || 0) * packs);
        document.getElementById('stock-hint').textContent = product.value ? stock + ' packs available' : 'Select a product to see availability.';
        if(product.value) quantity.max = stock; else quantity.removeAttribute('max');
    };
    product.addEventListener('change', update); quantity.addEventListener('input', update); update();
}
</script>
@endsection
