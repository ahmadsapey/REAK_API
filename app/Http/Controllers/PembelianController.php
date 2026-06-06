<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pembelian;
use App\Models\PembelianItem;
use App\Models\Produk;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PembelianController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Pembelian::with(['supplier', 'user', 'items.produk'])
            ->orderBy('tanggal_pembelian', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('no_pembelian', 'like', "%{$search}%")
                    ->orWhereHas('supplier', fn($s) => $s->where('nama', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('dari_tanggal')) {
            $query->whereDate('tanggal_pembelian', '>=', $request->dari_tanggal);
        }

        if ($request->filled('sampai_tanggal')) {
            $query->whereDate('tanggal_pembelian', '<=', $request->sampai_tanggal);
        }

        $pembelians = $query->paginate(15);
        return response()->json($pembelians);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'tanggal_pembelian' => 'required|date',
            'keterangan' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.produk_id' => 'required|exists:produks,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.harga_beli' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $noPembelian = 'PBL-' . date('Ymd') . '-' . str_pad(
                Pembelian::whereDate('created_at', today())->count() + 1,
                4,
                '0',
                STR_PAD_LEFT
            );

            $totalHarga = 0;
            $itemsData = [];

            foreach ($validated['items'] as $item) {
                $subtotal = $item['quantity'] * $item['harga_beli'];
                $totalHarga += $subtotal;
                $itemsData[] = [
                    'produk_id' => $item['produk_id'],
                    'quantity' => $item['quantity'],
                    'harga_beli' => $item['harga_beli'],
                    'subtotal' => $subtotal,
                ];
            }

            $pembelian = Pembelian::create([
                'no_pembelian' => $noPembelian,
                'supplier_id' => $validated['supplier_id'],
                'user_id' => $request->user()->id,
                'tanggal_pembelian' => $validated['tanggal_pembelian'],
                'total_harga' => $totalHarga,
                'status' => 'pending',
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            foreach ($itemsData as $item) {
                $pembelian->items()->create($item);
            }

            DB::commit();

            return response()->json([
                'data' => $pembelian->load(['supplier', 'user', 'items.produk']),
                'message' => 'Pembelian berhasil dibuat',
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal membuat pembelian: ' . $e->getMessage()], 500);
        }
    }

    public function show(Pembelian $pembelian): JsonResponse
    {
        return response()->json([
            'data' => $pembelian->load(['supplier', 'user', 'items.produk']),
        ]);
    }

    public function updateStatus(Request $request, Pembelian $pembelian): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,diterima,dibatalkan',
        ]);

        if ($pembelian->status === 'diterima') {
            return response()->json(['message' => 'Pembelian yang sudah diterima tidak dapat diubah statusnya'], 422);
        }

        DB::beginTransaction();

        try {
            $pembelian->update(['status' => $validated['status']]);

            if ($validated['status'] === 'diterima') {
                foreach ($pembelian->items as $item) {
                    $produk = Produk::find($item->produk_id);
                    if ($produk) {
                        $produk->increment('stok', $item->quantity);
                    }
                }
            }

            DB::commit();
            return response()->json([
                'data' => $pembelian->fresh(['supplier', 'user', 'items.produk']),
                'message' => 'Status pembelian berhasil diperbarui',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal update status: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Pembelian $pembelian): JsonResponse
    {
        if ($pembelian->status === 'diterima') {
            return response()->json(['message' => 'Pembelian yang sudah diterima tidak dapat dihapus'], 422);
        }

        $pembelian->delete();
        return response()->json(['message' => 'Pembelian berhasil dihapus']);
    }
}
