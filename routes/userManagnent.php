<?php 

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserManagement\RoleController;
use App\Http\Controllers\UserManagement\PermissionController;
use App\Http\Controllers\UserManagement\UserController;


Route::middleware(['XSS','auth'])->prefix('user-management')->group(function () {
    //Role Routes
    Route::get('role',[RoleController::class, 'index'])->name('role.list')->middleware('can:role-list');
    Route::post('role/ajax',[RoleController::class, 'showAll'])->name('role.show.ajax')->middleware('can:role-show');
    Route::get('role/create',[RoleController::class, 'store'])->name('role.create')->middleware('can:role-create');
    Route::post('role',[RoleController::class, 'store'])->name('role.store')->middleware('can:role-store');
    Route::get('role/{role}',[RoleController::class, 'show'])->name('role.show')->middleware('can:role-show');
    Route::put('role/{role}',[RoleController::class, 'update'])->name('role.update')->middleware('can:role-update');
    Route::get('role/{role}/destroy',[RoleController::class, 'destroy'])->name('role.destroy')->middleware('can:role-destroy');
    Route::get('role/{role}/edit',[RoleController::class, 'edit'])->name('role.edit')->middleware('can:role-edit');
    Route::get('role/{role}/assign-permission',[RoleController::class, 'assginPermssion'])->name('role.assginPermssion')->middleware('can:role-edit');
    Route::post('role/{role}/assign-permission',[RoleController::class, 'assginPermssionUpdate'])->name('role.assginPermssion.post')->middleware('can:role-edit');
    
    //Permission Route
    Route::get('permission',[PermissionController::class, 'index'])->name('permission.list')->middleware('can:permission-list');
    Route::post('permission/ajax',[PermissionController::class, 'showAll'])->name('permission.show.ajax')->middleware('can:permission-show');
    Route::get('permission/create',[PermissionController::class, 'store'])->name('permission.create')->middleware('can:permission-create');
    Route::post('permission',[PermissionController::class, 'store'])->name('permission.store')->middleware('can:permission-store');
    Route::get('permission/{permission}',[PermissionController::class, 'show'])->name('permission.show')->middleware('can:permission-show');
    Route::put('permission/{permission}',[PermissionController::class, 'update'])->name('permission.update')->middleware('can:permission-update');
    Route::get('permission/{permission}/destroy',[PermissionController::class, 'destroy'])->name('permission.destroy')->middleware('can:permission-destroy');
    Route::get('permission/{permission}/edit',[PermissionController::class, 'edit'])->name('permission.edit')->middleware('can:permission-edit');

    //User Role Route
    Route::get('user',[UserController::class, 'index'])->name('user.list')->middleware('can:user-list');
    Route::post('user/ajax',[UserController::class, 'showAll'])->name('user.show.ajax')->middleware('can:user-show');
    Route::get('user/create',[UserController::class, 'store'])->name('user.create')->middleware('can:user-create');
    Route::post('user',[UserController::class, 'store'])->name('user.store')->middleware('can:user-store');
    Route::get('user/{user}',[UserController::class, 'show'])->name('user.show')->middleware('can:user-show');
    Route::put('user/{user}',[UserController::class, 'update'])->name('user.update')->middleware('can:user-update');
    Route::get('user/{user}/destroy',[UserController::class, 'destroy'])->name('user.destroy')->middleware('can:user-destroy');
    Route::get('user/{user}/edit',[UserController::class, 'edit'])->name('user.edit')->middleware('can:user-edit');
});