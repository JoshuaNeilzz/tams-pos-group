<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return view('dashboard', ['title' => 'Overview', 'revenue' => Sale::whereDate('created_at', today())->sum('total_price'),
            'transactions' => Sale::whereDate('created_at', today())->count(), 'products' => Product::count(),
            'customers' => Customer::count(), 'staff' => User::where('role', 'staff')->where('status', 'active')->count(), 'lowStock' => Product::where('stock_quantity', '<=', 5)->orderBy('stock_quantity')->take(5)->get(),
            'recentSales' => Sale::latest('id')->take(5)->get()]);
    }
}
