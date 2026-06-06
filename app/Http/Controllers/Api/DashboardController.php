<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\Orders;
use App\Models\User;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $totalProduk = Produk::count();
        $totalOrders = Orders::count();
        $totalUsers = User::count();
        $totalRevenue = Orders::whereIn('status', ['paid', 'completed'])->sum('total_price');

        $recentOrders = Orders::with('user', 'items.produk')->latest()->take(5)->get();
        $lowStock = Produk::where('stok', '<', 10)->take(5)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_produk' => $totalProduk,
                'total_orders' => $totalOrders,
                'total_users' => $totalUsers,
                'total_revenue' => $totalRevenue,
                'recent_orders' => $recentOrders,
                'low_stock' => $lowStock
            ]
        ]);
    }
}
