<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterMitraController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EditProfileController;
use App\Http\Controllers\EditProfileKaryawanController;
use App\Http\Controllers\LandingPage\AppController;
use App\Http\Controllers\Master\AkunKaryawanController;
use App\Http\Controllers\Master\AkunMitraController;
use App\Http\Controllers\Master\ProdukController;
use App\Http\Controllers\Pengaturan\PengaturanMitraController;
use App\Http\Controllers\SuperAdmin\KaryawanController as SuperAdminKaryawanController;
use App\Http\Controllers\SuperAdmin\ProdukController as SuperAdminProdukController;
use App\Http\Controllers\Transaksi\LaporanTransaksiController;
use App\Http\Controllers\Transaksi\StokProdukController;
use App\Http\Controllers\Transaksi\TransaksiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// LandingPage

Route::get("/keranjang/{id_produk}", [AppController::class, "keranjang"]);
Route::get("/keranjang/{id_produk}/detail", [AppController::class, "detailProduk"]);
Route::post("/keranjang/{id_produk}/detailQty", [AppController::class, "detailQty"]);
Route::put("/keranjang/{idKeranjangDetail}/update-qty", [AppController::class, "updateQty"]);
Route::post("/checkout", [AppController::class, "checkout"]);

Route::get("/transaksi/{no_invoice}", [AppController::class, "invoice"]);

Route::get("/riwayat-transaksi", [AppController::class, "riwayatTransaksi"]);

Route::get("/pembayaran-cash/{id}", [AppController::class, "pembayaranCash"]);
Route::put("/pembayaran-cash/{id}", [AppController::class, "updateCashAmount"]);

Route::middleware(['guest'])->group(function () {

    Route::prefix('login')->name('login.')->group(function () {
        Route::get('/', [LoginController::class, 'index'])
            ->name('index');
        Route::post('/proccess', [LoginController::class, 'proccess'])
            ->name('proccess');
    });

    Route::get('/register', [RegisterMitraController::class, 'index']);
    Route::post('/register/proccess', [RegisterMitraController::class, 'proccess']);
});

Route::middleware(['auth'])->name('web.')->group(function () {
    Route::get('/logout', LogoutController::class)
        ->name('auth.logout');
});

Route::middleware(['autentikasi'])->group(function () {
    Route::get("/", [AppController::class, "home"]);

    Route::group(['middleware' => ['can:super_admin']], function () {
        Route::prefix('super_admin')->group(function () {
            Route::prefix('profil')->group(function () {
                Route::get('user', [EditProfileController::class, 'index']);
                Route::put('user/{id}', [EditProfileController::class, 'update']);
                Route::put('user/update_password/{id}', [EditProfileController::class, 'UpdatePassword']);
                Route::get('user/edit_password/{id}/edit', [EditProfileController::class, 'editPassword']);
                Route::put('user/update_gambar/{id}', [EditProfileController::class, 'editGambar']);
                Route::get('user/edit_gambar/{id}/edit', [EditProfileController::class, 'updateGambar']);
            });
            Route::prefix('master')->group(function () {
                Route::resource('mitra', AkunMitraController::class);
                Route::get('mitra/{id}/edit', [AkunMitraController::class, 'edit']);
                Route::post('mitra/changeStatus/{id}', [AkunMitraController::class, 'changeStatus']);

                Route::get('produk', [SuperAdminProdukController::class, 'index']);
                Route::get('karyawan', [SuperAdminKaryawanController::class, 'index']);
            });
            Route::get('/transaksi', [TransaksiController::class, 'index']);
            Route::post('transaksi/filterByStatus', [TransaksiController::class, 'filterByStatus']);
            Route::get('/dashboard', [DashboardController::class, 'super_admin']);
        });
    });

    Route::group(['middleware' => ['can:admin']], function () {
        Route::prefix('admin')->group(function () {
            Route::prefix('master')->group(function () {
                Route::resource('produk', ProdukController::class);
                Route::post('produk/changeStatus/{id}', [ProdukController::class, 'changeStatus']);
                Route::get('produk/{id}/edit', [ProdukController::class, 'edit']);
                Route::resource('karyawan', AkunKaryawanController::class);
                Route::post('karyawan/changeStatus/{userId}', [AkunKaryawanController::class, 'changeStatus']);
                Route::get('karyawan/{id}/edit', [AkunKaryawanController::class, 'edit']);
                Route::get('karyawan/{id}/editPassword', [AkunKaryawanController::class, 'editPassword']);
                Route::put('karyawan/updatePassword/{id}', [AkunKaryawanController::class, 'updatePassword']);
            });
            Route::prefix('transaksi')->group(function () {
                Route::prefix('laporan')->group(function () {
                    Route::get('transaksi', [LaporanTransaksiController::class, 'index']);
                    Route::post('transaksi/filterByStatus', [LaporanTransaksiController::class, 'filterByStatus']);
                    Route::get('transaksi/detailTransaksi/{id}', [LaporanTransaksiController::class, 'detailTransaksi']);
                    Route::get('transaksi/exportFilteredPdf', [LaporanTransaksiController::class, 'exportFilteredPdf']);
                });
                Route::get('stok_produk', [StokProdukController::class, 'index']);
                Route::post('tambah_qty', [StokProdukController::class, 'tambahQty']);
                Route::post('changeStatus/{id}', [StokProdukController::class, 'changeStatus']);
            });
            Route::prefix('profil_admin')->group(function () {
                Route::get('user', [EditProfileController::class, 'index']);
                Route::put('user/{id}', [EditProfileController::class, 'update']);
                Route::put('user/update_password/{id}', [EditProfileController::class, 'UpdatePassword']);
                Route::get('user/edit_password/{id}/edit', [EditProfileController::class, 'editPassword']);
                Route::put('user/update_gambar/{id}', [EditProfileController::class, 'editGambar']);
                Route::get('user/edit_gambar/{id}/edit', [EditProfileController::class, 'updateGambar']);
            });
            Route::prefix('pengaturan')->group(function () {
                Route::get('mitra', [PengaturanMitraController::class, 'index']);
                Route::put('mitra/{id}', [PengaturanMitraController::class, 'update']);
                Route::get('mitra/{id}/edit', [PengaturanMitraController::class, 'editGambar']);
                Route::put('mitra/updateGambar/{id}', [PengaturanMitraController::class, 'updateGambar']);
            });
            Route::get('/dashboard', [DashboardController::class, 'admin']);
        });
    });
    Route::group(['middleware' => ['can:karyawan']], function () {
        Route::prefix('karyawan')->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'karyawan']);
            Route::prefix('profile')->group(function () {
                Route::get('/saya', [EditProfileKaryawanController::class, 'index']);
                Route::put('/saya/update/{id}', [EditProfileKaryawanController::class, 'update']);
                Route::get('/saya/update_password/{id}/edit', [EditProfileKaryawanController::class, 'editPassword']);
                Route::put('/saya/update_password/{id}', [EditProfileKaryawanController::class, 'updatePassword']);
                Route::get('/saya/update_gambar/{id}/edit', [EditProfileKaryawanController::class, 'editGambar']);
                Route::put('/saya/update_gambar/{id}', [EditProfileKaryawanController::class, 'updateGambar']);
            });
        });
    });
});
