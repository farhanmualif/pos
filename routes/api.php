<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\CallbackController;
use App\Http\Controllers\Api\KeranjangApiController;
use App\Http\Controllers\Api\ProdukApiController;
use App\Http\Controllers\Api\TransaksiApiController;
use App\Http\Controllers\Api\XenditPaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post("/callback", [CallbackController::class, "postCallback"]);
Route::post("/callback-data", function () {
    echo "ada";
});

Route::post('/transaksi/callback', [TransaksiApiController::class, 'handleVirtualAccountCallback']);
// Route::post('/transaksi/callback/failure', [TransaksiApiController::class, 'paymentCallbackFailure']);

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('produk')->group(function () {
        Route::get('/', [ProdukApiController::class, 'getAll']);
        Route::get('/{id}', [ProdukApiController::class, 'getById']);
    });

    Route::prefix('keranjang')->group(function () {
        Route::get('/', [KeranjangApiController::class, 'getAll']);
        Route::get('/{id}/detail', [KeranjangApiController::class, 'getDetail']);
        Route::post('/', [KeranjangApiController::class, 'store']);
    });

    Route::prefix('/transaksi')->group(function () {
        Route::post('/checkout', [TransaksiApiController::class, 'checkout']);
        Route::get('/{id}/status', [TransaksiApiController::class, 'checkPaymentStatus']);
    });



    Route::prefix('/karyawan/profil')->group(function () {
        Route::get('/', [AuthApiController::class, 'profile']);
        Route::put('/{id}/ubah', [AuthApiController::class, 'update']);
        Route::put('/{id}/password/ubah', [AuthApiController::class, 'updatePassword']);
        Route::put('/{id}/gambar/ubah', [AuthApiController::class, 'updateGambar']);
    });

    Route::post('/logout', [AuthApiController::class, 'logout']);
});

Route::post('/signin', [AuthApiController::class, 'signIn']);
Route::get('/cek-autentikasi', [AuthApiController::class, 'checkAuth']);
