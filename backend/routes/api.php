<?php

use App\Http\Controllers\AkunController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController; // Pastikan ini di-import
use App\Http\Controllers\JurnalUmumController;

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

// Rute Publik (tidak perlu login)
Route::post('/login', [AuthController::class, 'login'])->name('login'); // Rute untuk login

// Rute yang Memerlukan Autentikasi (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Rute Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Rute untuk User (dari jawaban sebelumnya)
    Route::prefix('user')->controller(UserController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'destroy');
        Route::post('/import', 'import');
    });

    Route::prefix('akun')->controller(AkunController::class)->group(function () {
        Route::get('/', 'index');          // GET /api/akun (Fetch accounts)
        Route::post('/', 'store');         // POST /api/akun (Add account)
        Route::get('/{id}', 'show');       // GET /api/akun/{id} (Show account)
        Route::put('/{id}', 'update');     // PUT /api/akun/{id} (Update account)
        Route::delete('/{id}', 'destroy'); // DELETE /api/akun/{id} (Delete account)
        Route::post('/import', 'import');  // POST /api/akun/import (Import accounts)
    });

    // Tambahkan rute untuk Jurnal Umum
    Route::prefix('jurnal-umum')->controller(JurnalUmumController::class)->group(function () {
        Route::get('/', 'index');          // GET /api/jurnal-umum (Fetch journal entries)
        Route::post('/', 'store');         // POST /api/jurnal-umum (Add journal entry)
        Route::delete('/{id}', 'destroy'); // DELETE /api/jurnal-umum/{id} (Delete journal entry)
        Route::post('/import', 'import');  // POST /api/jurnal-umum/import (Import journal entries)
    });

    // Rute untuk mendapatkan data user yang sedang login (contoh)
    Route::get('/user-profile', function (Request $request) {
        // Pastikan hanya mengembalikan data yang aman
        return response()->json($request->user()->only(['id', 'name', 'username', 'email', 'role', 'status']));
    })->name('user.profile');

    // Tambahkan rute lain yang memerlukan autentikasi di sini
});

// Fallback route untuk menangani rute yang tidak ditemukan (opsional)
Route::fallback(function(){
    return response()->json(['message' => 'Endpoint tidak ditemukan.'], 404);
});
