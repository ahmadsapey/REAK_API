<?php
use App\Http\Controllers\Api\BukuController;
use App\Http\Controllers\Api\OrdersController;
use App\Http\Controllers\Api\ProdukController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\PelangganController;
use App\Http\Controllers\Api\SupplierController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\PembelianController;
use App\Http\Controllers\Api\ProfileController;

use Illuminate\Http\Request;


Route::apiResource('users', UserApiController::class);
Route::apiResource('bukus', BukuController::class);
Route::apiResource('produks', ProdukController::class);
Route::post('/produks/{id}/images', [ProdukController::class, 'uploadImages']);
Route::apiResource('orders', OrdersController::class);
Route::put('orders/{id}/status',[OrdersController::class,'updateStatus']);
Route::get('/pelanggan/phone/{no_hp}', [PelangganController::class, 'getByPhone']);
Route::apiResource('pelanggan', PelangganController::class);
Route::apiResource('suppliers', SupplierController::class);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function() {
    Route::get('/user', function(Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    // Dashboard & profile
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Pembelian resource (purchases)
    Route::apiResource('pembelian', PembelianController::class);

    // Profile endpoints
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/password', [ProfileController::class, 'updatePassword']);
    Route::put('/profile/foto', [ProfileController::class, 'updateFoto']);
});

Route::get('/', function () {
    return 'API sukses';
});
