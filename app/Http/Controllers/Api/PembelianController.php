<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pembelian;

class PembelianController extends Controller
{
    public function index()
    {
        return response()->json(Pembelian::latest()->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_name' => 'required|string|max:255',
            'total_price' => 'required|numeric',
            'status' => 'nullable|string'
        ]);

        $p = Pembelian::create($data);

        return response()->json(['success' => true, 'data' => $p], 201);
    }

    public function show($id)
    {
        return response()->json(Pembelian::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $p = Pembelian::findOrFail($id);

        $data = $request->validate([
            'supplier_name' => 'sometimes|required|string|max:255',
            'total_price' => 'sometimes|required|numeric',
            'status' => 'nullable|string'
        ]);

        $p->update($data);

        return response()->json(['success' => true, 'data' => $p]);
    }

    public function destroy($id)
    {
        $p = Pembelian::findOrFail($id);
        $p->delete();

        return response()->json(['success' => true, 'message' => 'Pembelian dihapus']);
    }
}
