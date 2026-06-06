<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\Orders;
use App\Models\OrderItem;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Http\Resources\OrderCollection;
use Illuminate\Support\Facades\DB;
use Exception;

class OrdersController extends Controller
{
    public function index()
    {
        $orders = Orders::with('user', 'items.produk')->latest()->get();
        return new OrderCollection($orders);
    }

    public function store(StoreOrderRequest $request)
    {
        DB::beginTransaction();
        
        try {
            $Orders = Orders::create([
                'user_id' => $request->user_id,
                'order_code' => 'ORD' . time(),
                'total_price' => 0,
                'shipping_address' => $request->shipping_address,
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'pelanggan_id' => $request->pelanggan_id,
                'status' => 'pending'
            ]);

            $total = 0;

            foreach ($request->items as $item) {
                $produk = Produk::findOrFail($item['produk_id']);
                $subtotal = $produk->harga * $item['quantity'];
                $total += $subtotal;

                OrderItem::create([
                    'order_id' => $Orders->id,
                    'produk_id' => $produk->id,
                    'quantity' => $item['quantity'],
                    'price' => $produk->harga,
                    'subtotal' => $subtotal
                ]);
            }

            $Orders->update([
                'total_price' => $total
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Orders berhasil dibuat',
                'data' => new OrderResource($Orders->load('user', 'items.produk'))
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat Orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $Orders = Orders::with('user', 'items.produk')->findOrFail($id);
        return new OrderResource($Orders);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,paid,shipped,complete,cancelled,selesai,dibayar,menunggu,batal'
        ]);

        $Orders = Orders::findOrFail($id);
        
        $Orders->update([
            'status' => $request->status
        ]);

        return response()->json([
            'message' => 'Status berhasil diupdate',
            'data' => $Orders
        ]);
    }

    public function destroy($id)
    {
        $Orders = Orders::findOrFail($id);
        $Orders->delete();
        
        return response()->json([
            'message' => 'Orders berhasil dihapus'
        ]);
    }
}