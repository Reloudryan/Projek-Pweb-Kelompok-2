<?php

use App\Http\Controllers\Admin\DailyQuotaController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use Illuminate\Support\Facades\Route;

// Halaman Beranda Sementara (Nanti diisi katalog wisata oleh Anggota 2/3)
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Rute Tamu (Belum Login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

// Rute Logout
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Grup Rute Khusus Admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', UserController::class)->except(['show']);
    // Modul Kuota Harian (Ulfatun)
    Route::resource('daily-quotas', DailyQuotaController::class)
        ->except(['show'])
        ->parameters(['daily-quotas' => 'daily_quota']);
    // Anggota 2, 3, 4 menambahkan route admin mereka di sini
});

// Grup Rute Khusus Pengunjung
Route::middleware(['auth', 'role:pengunjung'])->group(function () {
    // Modul Pemesanan Tiket (Ulfatun)
    // cek-kuota harus didaftarkan sebelum resource supaya tidak dianggap {booking}
    Route::get('pesanan/cek-kuota', [BookingController::class, 'cekKuota'])->name('bookings.cek-kuota');
    Route::resource('pesanan', BookingController::class)
        ->parameters(['pesanan' => 'booking'])
        ->names('bookings')
        ->except(['destroy']);
    Route::patch('pesanan/{booking}/batal', [BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::post('pesanan/{booking}/bukti-bayar', [BookingController::class, 'uploadBukti'])->name('bookings.bukti');
});