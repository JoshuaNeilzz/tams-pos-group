<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\SnackOrders;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        return view('orders', ['title' => 'Pickup orders', 'orders' => Order::orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")->latest('id')->paginate(20)]);
    }

    public function store(Request $request, SnackOrders $orders)
    {
        abort_unless($request->user()->role === 'user' && $request->user()->status === 'active' && $request->user()->customer_id, 403);
        $data = $request->validate([
            'product_id' => ['required', 'integer'], 'quantity' => ['required', 'integer', 'min:1', 'max:1000'],
            'request_key' => ['required', 'uuid'],
        ]);
        $order = $orders->reserve($request->user(), $data);
        if ($order->status !== 'pending') {
            return to_route('account')->with('success', 'This checkout was already '.$order->status.'. Start a new order from the catalog if needed.');
        }

        return to_route('account')->with('success', 'Order #'.$order->id.' reserved. Pay PHP '.$order->total_price.' in cash when you collect your snacks at the store.');
    }

    public function complete(Request $request, Order $order, SnackOrders $orders)
    {
        $orders->complete($order, $request->user());

        return to_route('orders.index')->with('success', 'Order #'.$order->id.' completed and added to Sales history.');
    }

    public function cancel(Request $request, Order $order, SnackOrders $orders)
    {
        abort_unless($request->user()->isActiveStaff() || ($request->user()->role === 'user' && $order->user_id === $request->user()->id), 403);
        $orders->cancel($order);

        return back()->with('success', 'Order #'.$order->id.' cancelled. Reserved packs returned to stock.');
    }
}
