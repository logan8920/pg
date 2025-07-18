<?php 

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PgCompany\PgCompanyController;


Route::middleware(['XSS','auth'])->prefix('pg-company')->group(function () {
    //pg-company Routes
    Route::get('/list',[PgCompanyController::class, 'index'])->name('pg-company.list')->middleware('can:pg-company-list');
    Route::post('/ajax',[PgCompanyController::class, 'showAll'])->name('pg-company.show.ajax')->middleware('can:pg-company-show');
    Route::get('/create',[PgCompanyController::class, 'store'])->name('pg-company.create')->middleware('can:pg-company-create');
    Route::post('/store',[PgCompanyController::class, 'store'])->name('pg-company.store')->middleware('can:pg-company-store');
    Route::get('/{pgCompany}',[PgCompanyController::class, 'show'])->name('pg-company.show')->middleware('can:pg-company-show');
    Route::put('/{pgCompany}',[PgCompanyController::class, 'update'])->name('pg-company.update')->middleware('can:pg-company-update');
    Route::get('/{pgCompany}/destroy',[PgCompanyController::class, 'destroy'])->name('pg-company.destroy')->middleware('can:pg-company-destroy');
    Route::get('/{pgCompany}/edit',[PgCompanyController::class, 'edit'])->name('pg-company.edit')->middleware('can:pg-company-edit');

    Route::get('/{pgCompany}/default-config',[PgCompanyController::class, 'defaultConfig'])->name('pg-company.default.config')->middleware('can:pg-company-default-config');

    Route::post('/{pgCompany}/default-config',[PgCompanyController::class, 'defaultConfigUpdate'])->name('pg-company.default.config.post')->middleware('can:pg-company-default-config');
});