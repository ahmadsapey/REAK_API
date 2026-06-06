<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pelanggan;

class PelangganController extends Controller
{
    public function index()
    {
        return response()->json(Pelanggan::latest()->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'no_hp' => 'required|string|unique:pelanggan,no_hp',
            'alamact' => 'nullable|string'
        ]);

        $pelanggan = Pelanggan::create($data);

        return response()->json(['success' => true, 'data' => $pelanggan], 201);
    }

    public function show($id)
    {
        return response()->json(Pelanggan::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $pelanggan = Pelanggan::findOrFail($id);

        $data = $request->validate([
            'nama' => 'sometimes|required|string|max:255',
            'no_hp' => 'sometimes|required|string|unique:pelanggan,no_hp,' . $id,
            'alamact' => 'nullable|string'
        ]);

        $pelanggan->update($data);

        return response()->json(['success' => true, 'data' => $pelanggan]);
    }

    public function destroy($id)
    {
        $pelanggan = Pelanggan::findOrFail($id);
        $pelanggan->delete();

        return response()->json(['success' => true, 'message' => 'Pelanggan dihapus']);
    }

    public function getByPhone($no_hp)
    {
        $pelanggan = Pelanggan::where('no_hp', $no_hp)->first();

        if (!$pelanggan) {
            return response()->json(['success' => false, 'message' => 'Pelanggan tidak ditemukan'], 404);
        }

        return response()->json(['success' => true, 'data' => $pelanggan]);
    }
}
