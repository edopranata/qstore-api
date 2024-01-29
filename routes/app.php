<?php

use App\Http\Controllers\Api\Data\AreaController;
use App\Http\Controllers\Api\Data\CarController;
use App\Http\Controllers\Api\Data\CustomerController;
use App\Http\Controllers\Api\Data\DriverController;
use App\Http\Controllers\Api\Data\LandController;
use App\Http\Controllers\Api\Management\MenuController;
use App\Http\Controllers\Api\Management\PermissionController;
use App\Http\Controllers\Api\Management\RoleController;
use App\Http\Controllers\Api\Management\UserController;
use App\Http\Controllers\Api\Transaction\DeliveryOrderController;
use App\Http\Controllers\Api\Transaction\TradeBuyController;
use App\Http\Controllers\Api\Transaction\TradeBuyDetailsController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'management', 'as' => 'management.'], function () {
    Route::group(['prefix' => 'users', 'as' => 'users.'], function () {
        Route::get('/', [UserController::class, 'index'])->name('index')->middleware('permission:app.management.users.index');
        Route::post('/', [UserController::class, 'store'])->name('createUser')->middleware('permission:app.management.users.createUser');
        Route::patch('/{user}', [UserController::class, 'update'])->name('updateUser')->middleware('permission:app.management.users.updateUser');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('deleteUser')->middleware('permission:app.management.users.deleteUser');
        Route::post('/{user}', [UserController::class, 'update'])->name('resetPassword')->middleware('permission:app.management.users.resetPassword');
    });

    Route::group(['prefix' => 'permissions', 'as' => 'permissions.'], function () {
        Route::get('/', [PermissionController::class, 'index'])->name('index')->middleware('permission:app.management.permissions.index');
        Route::post('/', [PermissionController::class, 'sync'])->name('syncPermissions')->middleware('permission:app.management.permissions.syncPermissions');
        Route::get('/{id}/view', [PermissionController::class, 'view'])->name('viewPermission')->middleware('permission:app.management.permissions.viewPermission');
        Route::post('/{id}/view', [PermissionController::class, 'viewRolesUsers']);
    });

    Route::group(['prefix' => 'roles', 'as' => 'roles.'], function () {
        Route::get('/', [RoleController::class, 'index'])->name('index')->middleware('permission:app.management.roles.index');
        Route::get('/{role}/view', [RoleController::class, 'show'])->name('viewRole')->middleware('permission:app.management.roles.viewRole');
        Route::post('/{role}/view', [RoleController::class, 'showDetails']);
        Route::patch('/{role}/view', [RoleController::class, 'addPermissionsToRole'])->name('addPermissionsToRole')->middleware('permission:app.management.roles.addPermissionsToRole');

        Route::post('/', [RoleController::class, 'store'])->name('createRole')->middleware('permission:app.management.roles.createRole');
        Route::patch('/{role}', [RoleController::class, 'update'])->name('updateRole')->middleware('permission:app.management.roles.updateRole');
        Route::delete('/{role}', [RoleController::class, 'destroy'])->name('deleteRole')->middleware('permission:app.management.roles.deleteRole');
    });
});
Route::group(['prefix' => 'masterData', 'as' => 'masterData.'], function () {
    Route::group(['prefix' => 'mobil', 'as' => 'mobil.'], function () {
        Route::get('', [CarController::class, 'index'])->name('index')->middleware('permission:app.masterData.mobil.index');
        Route::post('/', [CarController::class, 'store'])->name('createCar')->middleware('permission:app.masterData.mobil.createCar');
        Route::patch('/{car}', [CarController::class, 'update'])->name('updateCar')->middleware('permission:app.masterData.mobil.updateCar');
        Route::delete('/{car}', [CarController::class, 'destroy'])->name('deleteCar')->middleware('permission:app.masterData.mobil.deleteCar');
    });
    Route::group(['prefix' => 'supir', 'as' => 'supir.'], function () {
        Route::get('', [DriverController::class, 'index'])->name('index')->middleware('permission:app.masterData.supir.index');
        Route::post('/', [DriverController::class, 'store'])->name('createDriver')->middleware('permission:app.masterData.supir.createDriver');
        Route::patch('/{driver}', [DriverController::class, 'update'])->name('updateDriver')->middleware('permission:app.masterData.supir.updateDriver');
        Route::delete('/{driver}', [DriverController::class, 'destroy'])->name('deleteDriver')->middleware('permission:app.masterData.supir.deleteDriver');
    });
    Route::group(['prefix' => 'pelanggan', 'as' => 'pelanggan.'], function () {
        Route::get('', [CustomerController::class, 'index'])->name('index')->middleware('permission:app.masterData.pelanggan.index');
        Route::post('/', [CustomerController::class, 'store'])->name('createCustomer')->middleware('permission:app.masterData.pelanggan.createCustomer');
        Route::patch('/{customer}', [CustomerController::class, 'update'])->name('updateCustomer')->middleware('permission:app.masterData.pelanggan.updateCustomer');
        Route::delete('/{customer}', [CustomerController::class, 'destroy'])->name('deleteCustomer')->middleware('permission:app.masterData.pelanggan.deleteCustomer');
    });
    Route::group(['prefix' => 'wilayah', 'as' => 'wilayah.'], function () {
        Route::get('', [AreaController::class, 'index'])->name('index')->middleware('permission:app.masterData.wilayah.index');
        Route::post('/', [AreaController::class, 'store'])->name('createArea')->middleware('permission:app.masterData.wilayah.createArea');
        Route::patch('/{area}', [AreaController::class, 'update'])->name('updateArea')->middleware('permission:app.masterData.wilayah.updateArea');
        Route::delete('/{area}', [AreaController::class, 'destroy'])->name('deleteArea')->middleware('permission:app.masterData.wilayah.deleteArea');
    });
    Route::group(['prefix' => 'lahan', 'as' => 'lahan.'], function () {
        Route::get('', [LandController::class, 'index'])->name('index')->middleware('permission:app.masterData.lahan.index');
        Route::post('/', [LandController::class, 'store'])->name('createLand')->middleware('permission:app.masterData.lahan.createLand');
        Route::patch('/{land}', [LandController::class, 'update'])->name('updateLand')->middleware('permission:app.masterData.lahan.updateLand');
        Route::delete('/{land}', [LandController::class, 'destroy'])->name('deleteLand')->middleware('permission:app.masterData.lahan.deleteLand');
    });
});
Route::group(['prefix' => 'transaction', 'as' => 'transaction.'], function () {
    Route::group(['prefix' => 'pembelianSawit', 'as' => 'pembelianSawit.'], function () {
        Route::get('', [TradeBuyController::class, 'index'])->name('index')->middleware('permission:app.transaction.pembelianSawit.index');
        Route::post('/', [TradeBuyController::class, 'store'])->name('createTransaction')->middleware('permission:app.transaction.pembelianSawit.createTransaction');
        Route::patch('/{trade}', [TradeBuyController::class, 'update'])->name('updateTransaction')->middleware('permission:app.transaction.pembelianSawit.updateTransaction');
        Route::delete('/{trade}', [TradeBuyController::class, 'destroy'])->name('deleteTransaction')->middleware('permission:app.transaction.pembelianSawit.deleteTransaction');

        Route::get('/{trade}/details', [TradeBuyController::class, 'show'])->name('viewDetailsTransaction')->middleware('permission:app.transaction.pembelianSawit.viewDetailsTransaction');
        Route::post('/{trade}/details', [TradeBuyDetailsController::class, 'store'])->name('createDetailsTransaction')->middleware('permission:app.transaction.pembelianSawit.createDetailsTransaction');
        Route::delete('/{trade}/details/{details}', [TradeBuyDetailsController::class, 'destroy'])->scopeBindings()->name('deleteDetailsTransaction')->middleware('permission:app.transaction.pembelianSawit.deleteDetailsTransaction');
        Route::patch('/{trade}/details/{details}', [TradeBuyDetailsController::class, 'update'])->scopeBindings()->name('updateDetailsTransaction')->middleware('permission:app.transaction.pembelianSawit.updateDetailsTransaction');
    });
    Route::group(['prefix' => 'deliveryOrders', 'as' => 'deliveryOrders.'], function () {
        Route::get('', [DeliveryOrderController::class, 'index'])->name('index')->middleware('permission:app.transaction.deliveryOrders.index');
        Route::post('/', [DeliveryOrderController::class, 'store'])->name('createDeliveryOrder')->middleware('permission:app.transaction.deliveryOrders.createDeliveryOrder');
        Route::patch('/{delivery}', [DeliveryOrderController::class, 'update'])->name('updateDeliveryOrder')->middleware('permission:app.transaction.deliveryOrders.updateDeliveryOrder');
        Route::delete('/{delivery}', [DeliveryOrderController::class, 'destroy'])->name('deleteDeliveryOrder')->middleware('permission:app.transaction.deliveryOrders.deleteDeliveryOrder');
    });
});
Route::group(['prefix' => 'settings', 'as' => 'settings.'], function () {
    Route::group(['prefix' => 'menu', 'as' => 'menu.'], function () {
        Route::get('/', [MenuController::class, 'index'])->name('index')->middleware('permission:app.settings.menu.index');

    });
});


