<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    OtpController,
    PgReceiptController
};
use App\Http\Controllers\ApiPartner\ApiPartnerController;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return redirect('dashboard');
})->middleware(['auth', 'verified', 'can:dashboard'])->name('dashboard');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified', 'can:dashboard'])->name('dashboard.extra');


Route::post('/dashboard-transaction-data',[ApiPartnerController::class, 'dashboardTxnData'])->middleware(['auth', 'verified', 'can:dashboard'])->name("dashboard.transaction.data");

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->prefix('otp')->group(function () {
    Route::post('/generate', [OtpController::class, 'create'])->middleware(['auth', 'can:dashboard'])->name('generate.otp');
    Route::post('/verify', [OtpController::class, 'verify'])->middleware(['auth', 'can:dashboard'])->name('verify.otp');

});

Route::post('/whitelist-ip', [OtpController::class, 'ipWhiteList'])->middleware(['auth', 'can:dashboard'])->name('ip.whiteList');
Route::get("/gateway-pg-receipt", [PgReceiptController::class, "index"]);

Route::get('/download-receipt/{filename}', function ($filename) {
    // Validate signature
    if (!request()->hasValidSignature()) {
        abort(403, 'Unauthorized or expired link.');
    }
   
    if (!Storage::disk('public')->exists("receipts/{$filename}")) {
        abort(404, 'Receipt not found.');
    }

    return response()->download(storage_path("app/public/receipts/{$filename}"));

})->name('download.receipt');

// Route::get('/generate-otpss', [OtpController::class, 'create']);
require __DIR__ . '/auth.php';
require __DIR__ . '/userManagnent.php';
require __DIR__ . '/apiPartner.php';
require __DIR__ . '/pgCompany.php';
require __DIR__ . '/mode.php';

