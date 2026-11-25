<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        return view('account', [
            'title' => 'My account',
            'account' => $user,
            'orders' => Order::where('user_id', $user->id)->latest('id')->paginate(8, ['*'], 'orders_page'),
            'products' => Product::orderBy('name')->paginate(8, ['*'], 'catalog_page'),
            'sales' => Sale::where('customer_id', $user->customer_id)->when(! $user->customer_id, fn ($q) => $q->whereRaw('1 = 0'))->latest('id')->paginate(10, ['*'], 'history_page'),
        ]);
    }
}
