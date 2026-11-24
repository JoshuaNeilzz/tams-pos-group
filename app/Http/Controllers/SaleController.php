<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    public function index()
    {
        return view('sales', ['title' => 'Sales history', 'sales' => Sale::latest('id')->paginate(20)]);
    }

    public function create()
    {
        return view('sales-form', ['title' => 'Record sale', 'products' => Product::where('stock_quantity', '>', 0)->orderBy('name')->get(), 'customers' => Customer::orderBy('full_name')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000000000'],
        ]);
        $sale = DB::transaction(function () use ($data, $request) {
            $product = Product::lockForUpdate()->find($data['product_id']);
            if (! $product) {
                throw ValidationException::withMessages(['product_id' => 'This product is no longer available.']);
            }
            if ($data['quantity'] > $product->stock_quantity) {
                throw ValidationException::withMessages(['quantity' => 'Insufficient stock. Only '.$product->stock_quantity.' units are available.']);
            }
            // Calculate persisted totals in integer cents.
            $cents = (int) str_replace('.', '', $product->price) * (int) $data['quantity'];
            if ($cents > 9999999999) {
                throw ValidationException::withMessages(['quantity' => 'This sale exceeds the maximum total of PHP 99,999,999.99.']);
            }
            $customer = ! empty($data['customer_id']) ? Customer::lockForUpdate()->findOrFail($data['customer_id']) : null;
            $staff = $request->user();
            $sale = Sale::create([
                'product_id' => $product->id, 'customer_id' => $customer?->id,
                'sold_by' => $staff->id, 'quantity' => $data['quantity'],
                'total_price' => sprintf('%d.%02d', intdiv($cents, 100), $cents % 100),
                'product_name' => $product->name, 'customer_name' => $customer?->full_name, 'staff_name' => $staff->full_name,
            ]);
            $product->decrement('stock_quantity', $data['quantity']);

            return $sale;
        }, 3);

        return to_route('sales.index')->with('success', 'Sale #'.str_pad($sale->id, 5, '0', STR_PAD_LEFT).' recorded. Inventory updated.');
    }
}
