<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        return view('customers', [
            'title' => 'Customer Accounts',
            'customers' => Customer::orderBy('id')->get(),
        ]);
    }

    public function create()
    {
        return view('customers-form', [
            'title' => 'Add Customer',
            'customer' => null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        Customer::create($data);

        return redirect()->route('customers.index')
            ->with('success', 'Customer added successfully.');
    }

    public function edit(Customer $customer)
    {
        return view('customers-form', [
            'title' => 'Edit Customer',
            'customer' => $customer,
        ]);
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $customer->update($data);

        return redirect()->route('customers.index')
            ->with('success', 'Customer updated successfully.');
    }
        public function show(Customer $customer)
    {
        return view('customers-show', [
            'title' => 'Customer Details',
            'customer' => $customer,
        ]);
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }
}