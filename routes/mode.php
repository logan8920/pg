<?php 

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Mode\ModeController;


Route::middleware(['XSS','auth'])->prefix('mode')->group(function () {
    //mode Routes
    Route::get('/list',[ModeController::class, 'index'])->name('mode.list')->middleware('can:mode-list');
    Route::post('/ajax',[ModeController::class, 'showAll'])->name('mode.show.ajax')->middleware('can:mode-show');
    Route::get('/create',[ModeController::class, 'store'])->name('mode.create')->middleware('can:mode-create');
    Route::post('/store',[ModeController::class, 'store'])->name('mode.store')->middleware('can:mode-store');
    Route::get('/{mode}',[ModeController::class, 'show'])->name('mode.show')->middleware('can:mode-show');
    Route::put('/{mode}',[ModeController::class, 'update'])->name('mode.update')->middleware('can:mode-update');
    Route::get('/{mode}/destroy',[ModeController::class, 'destroy'])->name('mode.destroy')->middleware('can:mode-destroy');
    Route::get('/{mode}/edit',[ModeController::class, 'edit'])->name('mode.edit')->middleware('can:mode-edit');
});