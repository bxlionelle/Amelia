<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{

    public function index()
    {
        $user = auth()->user();
        $orders = $user->orders()->with('order_items.product.brand', 'order_items.product.category')->get();

        return inertia('User/Dashboard', [
            'orders' => $orders,
            'balance' => $user->balance ?? 0,
        ]);
    }
}
