<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BukuApi;
use Illuminate\Http\Request;

class BukuApiController extends Controller
{
 
    public function index()
    {
        return response()->json(BukuApi::all());
    }

    public function store(Request $request)
    {
        $user = BukuApi::create($request->all());
        return response()->json($user, 201);
    }

    public function show($id)
    {
        return response()->json(BukuApi::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $user = BukuApi::findOrFail($id);
        $user->update($request->all());
        return response()->json($user);
    }

    public function destroy($id)
    {
        BukuApi::destroy($id);
        return response()->json(['message' => 'Data dihapus']);
    }

}

