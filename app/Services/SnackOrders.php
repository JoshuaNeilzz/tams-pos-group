<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SnackOrders
{
    public function reserve(User $buyer, array $data): Order
    {
        return DB::transaction(function () use ($buyer, $data) {
            // Serializes a buyer's retries as well as concurrent orders for the same stock.
            $buyer = User::lockForUpdate()->findOrFail($buyer->id);
            if ($existing = Order::where('user_id', $buyer->id)->where('request_key', $data['request_key'])->first()) {
                if ($existing->product_id != $data['product_id'] || $existing->quantity != $data['quantity']) {
                    throw ValidationException::withMessages(['quantity' => 'This checkout was already used. Reload the catalog to place another order.']);
                }

                return $existing;
            }
            $product = Product::lockForUpdate()->find($data['product_id']);
            if (! $product) {
                throw ValidationException::withMessages(['product_id' => 'This snack is no longer available.']);
            }
            if ($data['quantity'] > $product->stock_quantity) {
                throw ValidationException::withMessages(['quantity' => 'Only '.$product->stock_quantity.' packs of '.$product->name.' are available.']);
            }
            $cents = (int) str_replace('.', '', $product->price) * (int) $data['quantity'];
            if ($cents > 9999999999) {
                throw ValidationException::withMessages(['quantity' => 'This order exceeds the allowed total. Please reduce the quantity.']);
            }
            $order = Order::create([
                'user_id' => $buyer->id, 'customer_id' => $buyer->customer_id,
                'product_id' => $product->id, 'request_key' => $data['request_key'],
                'product_name' => $product->name, 'customer_name' => $buyer->full_name,
                'quantity' => $data['quantity'], 'unit_price' => $product->price,
                'total_price' => sprintf('%d.%02d', intdiv($cents, 100), $cents % 100), 'status' => 'pending',
            ]);
            $product->decrement('stock_quantity', $order->quantity);

            return $order;
        }, 3);
    }

    public function complete(Order $order, User $staff): void
    {
        DB::transaction(function () use ($order, $staff) {
            $order = Order::lockForUpdate()->findOrFail($order->id);
            if ($order->status === 'completed') {
                return;
            }
            if ($order->status !== 'pending') {
                throw ValidationException::withMessages(['order' => 'Only a pending order can be completed.']);
            }
            // Packs were reserved at checkout. Completion must not deduct them again.
            $sale = Sale::create([
                'product_id' => $order->product_id, 'customer_id' => $order->customer_id,
                'sold_by' => $staff->id, 'quantity' => $order->quantity, 'total_price' => $order->total_price,
                'product_name' => $order->product_name, 'customer_name' => $order->customer_name,
                'staff_name' => $staff->full_name,
            ]);
            $order->update(['status' => 'completed', 'sale_id' => $sale->id]);
        }, 3);
    }

    public function cancel(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $order = Order::lockForUpdate()->findOrFail($order->id);
            if ($order->status === 'cancelled') {
                return;
            }
            if ($order->status !== 'pending') {
                throw ValidationException::withMessages(['order' => 'Completed sales cannot be cancelled from the order list.']);
            }
            $product = Product::withTrashed()->lockForUpdate()->findOrFail($order->product_id);
            if ($product->stock_quantity + $order->quantity > 1000000000) {
                throw ValidationException::withMessages(['order' => 'The stock limit would be exceeded. Ask staff to correct the stock before cancelling.']);
            }
            $product->increment('stock_quantity', $order->quantity);
            $order->update(['status' => 'cancelled']);
        }, 3);
    }
}
