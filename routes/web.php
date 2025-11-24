<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\DashboardAdminController;
use App\Http\Controllers\Admin\BarangController;
use App\Http\Controllers\Admin\TransaksiController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\KeranjangController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RiwayatController;
use App\Http\Controllers\ManualTransaksiController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\Admin\ChatController as AdminChatController;
use App\Http\Controllers\PaymentController;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/


// ===========================
// HALAMAN UTAMA
// ===========================
Route::get('/', fn () => view('home'));
Route::get('/produk', [HomeController::class, 'produk'])->name('produk');
Route::get('/kontak', fn () => view('kontak'))->name('kontak');

// ===========================
// AUTH
// ===========================
Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::get('/register', [LoginController::class, 'showRegister'])->name('register');
Route::post('/register', [LoginController::class, 'register']);
Route::get('/logout', function () {
    Auth::logout();
    return redirect('/');
})->name('logout');

// ===========================
// ADMIN AREA
// ===========================
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {

    Route::get('/dashboard-admin', [DashboardAdminController::class, 'index'])->name('dashboard');

    // Barang
    Route::resource('barang', BarangController::class);

    // Transaksi
    Route::get('/transaksi', [TransaksiController::class, 'index'])->name('transaksi.index');
    Route::put('/transaksi/{id}/konfirmasi', [TransaksiController::class, 'konfirmasi'])->name('transaksi.konfirmasi');
    Route::get('/transaksi/{id}/cetak', [TransaksiController::class, 'cetakPDF'])->name('transaksi.cetak');
    Route::delete('/transaksi/{id}', [TransaksiController::class, 'destroy'])->name('transaksi.destroy');

    // Laporan
    Route::get('/laporan/barang', [LaporanController::class, 'laporanBarang'])->name('laporan.barang');
    Route::get('/laporan/barang-pdf', [LaporanController::class, 'laporanBarangPDF'])->name('laporan.barang_pdf');

    // Manual Transaksi
    Route::get('/manual', fn() => view('admin.manual'))->name('manual');
    Route::post('/manual', [ManualTransaksiController::class, 'store'])->name('manual.store');

    // Voucher
    Route::get('/voucher/create', [DashboardAdminController::class, 'createVoucher'])->name('voucher.create');
    Route::post('/voucher/store', [DashboardAdminController::class, 'storeVoucher'])->name('voucher.store');
    Route::get('/voucher/{voucher}/edit', [DashboardAdminController::class, 'editVoucher'])->name('voucher.edit');
    Route::put('/voucher/{voucher}', [DashboardAdminController::class, 'updateVoucher'])->name('voucher.update');
    Route::delete('/voucher/{voucher}', [DashboardAdminController::class, 'destroyVoucher'])->name('voucher.destroy');

    // Chat Admin
    Route::get('/chat', [AdminChatController::class, 'index'])->name('chat');

    // Pembayaran Admin
    Route::get('/pembayaran', [PembayaranController::class, 'index'])->name('pembayaran.index');
    Route::post('/pembayaran/proses', [PembayaranController::class, 'proses'])->name('pembayaran.proses');
});

// ===========================
// OWNER
// ===========================
Route::middleware('auth')->prefix('owner')->name('owner.')->group(function () {
    Route::get('/dashboard', [OwnerController::class, 'index'])->name('dashboard');
});

// ===========================
// USER ROUTES
// ===========================
Route::middleware('auth')->group(function () {

    // Dashboard / profile
    Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Produk
    Route::get('/produk/{barang}', [HomeController::class, 'detail'])->name('produk.detail');

    // Keranjang
    Route::get('/keranjang', [KeranjangController::class, 'index'])->name('keranjang');
    Route::post('/keranjang/tambah', [KeranjangController::class, 'tambah'])->name('keranjang.tambah');
    Route::post('/keranjang/hapus', [KeranjangController::class, 'hapus'])->name('keranjang.hapus');
    Route::post('/keranjang/update', [KeranjangController::class, 'update'])->name('keranjang.update');

    // === VOUCHER DI KERANJANG ===
    Route::post('/voucher/check', [KeranjangController::class, 'checkVoucher'])->name('voucher.check');


    // Checkout
    Route::get('/checkout', [KeranjangController::class, 'checkout'])->name('keranjang.checkout');
    Route::post('/keranjang/tambah-dan-bayar', [KeranjangController::class, 'tambahDanBayar'])->name('keranjang.tambah-dan-bayar');

    // Pembayaran User
   // Pembayaran User
    Route::get('/pembayaran', [PembayaranController::class, 'index'])->name('pembayaran');
    Route::post('/pembayaran/proses', [PembayaranController::class, 'proses'])->name('pembayaran.proses');
    Route::post('/beli-sekarang', [PembayaranController::class, 'beliSekarang'])->name('beli.sekarang');

// ===========================
// USER ROUTES
// ===========================
Route::post('/index', [PembayaranController::class, 'index'])
    ->name('index');

    // Riwayat User
    Route::get('/riwayat-pesanan', [RiwayatController::class, 'index'])->name('riwayat.pesanan');

    // Chat user
    Route::get('/chat', [ChatController::class, 'index'])->name('chat');
    Route::post('/chat', [ChatController::class, 'store'])->name('chat.store');
});


// ===========================
// MIDTRANS CALLBACK
// ===========================
Route::post('/payment/midtrans-callback', [PaymentController::class, 'midtransCallback']);


// Pelanggan

// routes/web.php
Route::post('/keranjang/apply-promo', [KeranjangController::class, 'applyPromo'])
    ->name('keranjang.applyPromo')
    ->middleware('auth');


// Admin
Route::get('/admin/promo', [DashboardAdminController::class, 'promoIndex'])->name('admin.promo');
Route::post('/admin/promo/store', [DashboardAdminController::class, 'promoStore'])->name('admin.promo.store');
// Tambahkan route ini di bagian keranjang
Route::post('/keranjang/remove-promo', [KeranjangController::class, 'removePromo'])->name('keranjang.removePromo');
Route::delete('admin/promo/{promo}', [DashboardAdminController::class, 'promoDestroy'])->name('admin.promo.destroy');





// Endpoint untuk membuat Snap token dinamis dengan ongkir dan total
Route::post('/create-snap-token', [PembayaranController::class, 'createSnapToken'])->name('create.snap');



