<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\CallbackController;
use App\Http\Controllers\Api\KeranjangApiController;
use App\Http\Controllers\Api\ProdukApiController;
use App\Http\Controllers\Api\TransaksiApiController;
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

Route::post('xendit/ewallet/expired', function (Request $request) {
    return response()->json([
        'status' => '200',
        'message' => 'Pembayaran Anda Sudah Kadalwarsa'
    ]);
});

Route::post('simulate-va-payment/{externalId}', [TransaksiApiController::class, 'simulateVAPayment']);
Route::post('xendit/qr_code/callback', [TransaksiApiController::class, 'handleQRCodeCallback']);
Route::post('xendit/ewallet/callback', [TransaksiApiController::class, 'handleEwalletCallback']);
Route::get('xendit/ewallet/success', function (Request $request) {
    return response()->json([
        "status" => true,
        "message" => "Pembayaran Selesai"
    ]);
});

Route::post('payment/success', function (Request $request) {
    return response()->json([
        "status" => true,
        "message" => "Pembayaran Berhasil",
        "data" => $request->all()
    ]);
});
Route::post('payment/failure', function (Request $request) {
    return response()->json([
        "status" => true,
        "message" => "Pembayaran gagal",
        "data" => $request->all()
    ]);
});

Route::post('/transaksi/callback', [TransaksiApiController::class, 'handleVirtualAccountCallback']);
Route::post('/transaksi/callback/unpaid', [TransaksiApiController::class, 'handleVirtualAccountUpdateToUnpaid']);
// Route::post('/transaksi/callback/failure', [TransaksiApiController::class, 'paymentCallbackFailure']);

Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('produk')->group(function () {
        Route::get('/', [ProdukApiController::class, 'getAll']);
        Route::post('/', [ProdukApiController::class, 'store']);
        Route::get('/{id}', [ProdukApiController::class, 'getById']);
        Route::put('/{id}', [ProdukApiController::class, 'update']);
        Route::put('/{id}/status', [ProdukApiController::class, 'changeStatus']);
        Route::get('/{id}/stok', [ProdukApiController::class, 'changeStatus']);
        Route::put('/stok/add', [ProdukApiController::class, 'tambahQty']);
        Route::put('/{id}/stok/status', [ProdukApiController::class, 'changeStatusStok']);
    });

    Route::prefix('keranjang')->group(function () {
        Route::get('/', [KeranjangApiController::class, 'getAll']);
        Route::post('/', [KeranjangApiController::class, 'addCart']);
        Route::delete('/', [KeranjangApiController::class, 'delete']);
        Route::post('/', [KeranjangApiController::class, 'addCart']);
        Route::delete('/', [KeranjangApiController::class, 'delete']);
        Route::get('/{id}/detail', [KeranjangApiController::class, 'getDetail']);
        Route::put('/{produkId}', [KeranjangApiController::class, 'updateQty']);
        Route::get('/{produkId}/produk', [KeranjangApiController::class, 'getDetailKeranjangByProdukId']);
        Route::put('/{produkId}', [KeranjangApiController::class, 'updateQty']);
        Route::get('/{produkId}/produk', [KeranjangApiController::class, 'getDetailKeranjangByProdukId']);
    });

    Route::prefix('/transaksi')->group(function () {
        Route::post('/checkout', [TransaksiApiController::class, 'checkout']);
        Route::get('/riwayat', [TransaksiApiController::class, 'riwayatTransaksi']);
        Route::get('/pending', [TransaksiApiController::class, 'getPendingTransaction']);
        Route::get('/{id}/status', [TransaksiApiController::class, 'checkPaymentStatus']);
    });

    Route::prefix('/karyawan/profil')->group(function () {
        Route::get('/', [AuthApiController::class, 'profile']);
        Route::put('/{id}/ubah', [AuthApiController::class, 'update']);
        Route::put('/{id}/password/ubah', [AuthApiController::class, 'updatePassword']);
        Route::put('/{id}/gambar/ubah', [AuthApiController::class, 'updateGambar']);
    });

    Route::prefix('/admin/profil')->group(function () {
        Route::get('/', [AuthApiController::class, 'profileMitra']);
    });

    Route::post('/logout', [AuthApiController::class, 'logout']);
});

Route::post('/signin', [AuthApiController::class, 'signIn']);
Route::get('/cek-autentikasi', [AuthApiController::class, 'checkAuth']);
