@extends('layouts.app')
@section('content')
<section class="page-heading"><p class="eyebrow">YOUR CUSTOMERS / CONTACT DETAILS</p><h1>{{ $customer->full_name }}<span class="pink-text">.</span></h1><p>Customer contact information.</p></section>
<div class="form-card"><div class="person-cell"><span class="initial-avatar">{{ mb_substr($customer->full_name, 0, 1) }}</span><strong>Customer #{{ str_pad($customer->id, 4, '0', STR_PAD_LEFT) }}</strong></div><div class="summary-line"><span>Email</span><strong>{{ $customer->email }}</strong></div><div class="summary-line"><span>Phone</span><strong>{{ $customer->phone ?: 'Not provided' }}</strong></div><div class="summary-line"><span>Customer since</span><strong>{{ $customer->created_at->format('M d, Y') }}</strong></div><div class="form-actions"><a class="button" href="{{ route('customers.edit', $customer) }}">Edit customer ↗</a><a href="{{ route('customers.index') }}">Back to directory</a></div></div>
@endsection
