<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RestockController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:1000000000'], 'note' => ['nullable', 'string', 'max:200'], 'request_key' => ['required', 'uuid']]);
        DB::transaction(function () use ($request, $product, $data) {
            User::lockForUpdate()->findOrFail($request->user()->id);
            $existing = DB::table('stock_movements')->where('user_id', $request->user()->id)->where('request_key', $data['request_key'])->first();
            if ($existing) {
                if ($existing->product_id != $product->id || $existing->quantity != $data['quantity']) {
                    throw ValidationException::withMessages(['quantity' => 'This delivery form was already used. Reload before adding a different delivery.']);
                }

                return;
            }
            $product = Product::lockForUpdate()->findOrFail($product->id);
            $balance = $product->stock_quantity + $data['quantity'];
            if ($balance > 1000000000) {
                throw ValidationException::withMessages(['quantity' => 'This restock exceeds the stock limit.']);
            }
            $product->increment('stock_quantity', $data['quantity']);
            DB::table('stock_movements')->insert([
                'product_id' => $product->id, 'user_id' => $request->user()->id,
                'quantity' => $data['quantity'], 'balance_after' => $balance, 'request_key' => $data['request_key'], 'note' => $data['note'] ?? null, 'created_at' => now(),
            ]);
        }, 3);

        return to_route('products.index')->with('success', $data['quantity'].' packs added to '.$product->name.'.');
    }
}
