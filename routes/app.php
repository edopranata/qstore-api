<?php

use App\Http\Controllers\Api\Data\AreaController;
use App\Http\Controllers\Api\Data\CarController;
use App\Http\Controllers\Api\Data\CustomerController;
use App\Http\Controllers\Api\Data\DriverController;
use App\Http\Controllers\Api\Management\MenuController;
use App\Http\Controllers\Api\Management\PermissionController;
use App\Http\Controllers\Api\Management\RoleController;
use App\Http\Controllers\Api\Management\UserController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'management', 'as' => 'management.'], function (){
    Route::group(['prefix' => 'users', 'as' => 'users.'], function (){
        Route::get('/', [UserController::class, 'index'])->name('index')->middleware('permission:app.management.users.index');
        Route::post('/', [UserController::class, 'store'])->name('createUser')->middleware('permission:app.management.users.createUser');
        Route::patch('/{user}', [UserController::class, 'update'])->name('updateUser')->middleware('permission:app.management.users.updateUser');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('deleteUser')->middleware('permission:app.management.users.deleteUser');
        Route::post('/{user}', [UserController::class, 'update'])->name('resetPassword')->middleware('permission:app.management.users.resetPassword');
    });

    Route::group(['prefix' => 'permissions', 'as' => 'permissions.'], function (){
        Route::get('/', [PermissionController::class, 'index'])->name('index')->middleware('permission:app.management.permissions.index');
        Route::post('/', [PermissionController::class, 'sync'])->name('syncPermissions')->middleware('permission:app.management.permissions.syncPermissions');
        Route::get('/{id}/view', [PermissionController::class, 'view'])->name('viewPermission')->middleware('permission:app.management.permissions.viewPermission');
        Route::post('/{id}/view', [PermissionController::class, 'viewRolesUsers']);
    });

    Route::group(['prefix' => 'roles', 'as' => 'roles.'], function (){
        Route::get('/', [RoleController::class, 'index'])->name('index')->middleware('permission:app.management.roles.index');
        Route::get('/{role}/view', [RoleController::class, 'show'])->name('viewRole')->middleware('permission:app.management.roles.viewRole');
        Route::post('/{role}/view', [RoleController::class, 'showDetails']);
        Route::patch('/{role}/view', [RoleController::class, 'addPermissionsToRole'])->name('addPermissionsToRole')->middleware('permission:app.management.roles.addPermissionsToRole');

        Route::post('/', [RoleController::class, 'store'])->name('createRole')->middleware('permission:app.management.roles.createRole');
        Route::patch('/{role}', [RoleController::class, 'update'])->name('updateRole')->middleware('permission:app.management.roles.updateRole');
        Route::delete('/{role}', [RoleController::class, 'destroy'])->name('deleteRole')->middleware('permission:app.management.roles.deleteRole');
    });
});
Route::group(['prefix' => 'masterData', 'as' => 'masterData.'], function (){
    Route::group(['prefix' => 'cars', 'as' => 'cars.'], function (){
        Route::get('', [CarController::class, 'index'])->name('index')->middleware('permission:app.masterData.cars.index');
        Route::post('/', [CarController::class, 'store'])->name('createCar')->middleware('permission:app.masterData.cars.createCar');
        Route::patch('/{car}', [CarController::class, 'update'])->name('updateCar')->middleware('permission:app.masterData.cars.updateCar');
        Route::delete('/{car}', [CarController::class, 'destroy'])->name('deleteCar')->middleware('permission:app.masterData.cars.deleteCar');
    });
    Route::group(['prefix' => 'drivers', 'as' => 'drivers.'], function (){
        Route::get('', [DriverController::class, 'index'])->name('index')->middleware('permission:app.masterData.drivers.index');
        Route::post('/', [DriverController::class, 'store'])->name('createDriver')->middleware('permission:app.masterData.drivers.createDriver');
        Route::patch('/{driver}', [DriverController::class, 'update'])->name('updateDriver')->middleware('permission:app.masterData.drivers.updateDriver');
        Route::delete('/{driver}', [DriverController::class, 'destroy'])->name('deleteDriver')->middleware('permission:app.masterData.drivers.deleteDriver');
    });
    Route::group(['prefix' => 'customers', 'as' => 'customers.'], function (){
        Route::get('', [CustomerController::class, 'index'])->name('index')->middleware('permission:app.masterData.customers.index');
        Route::post('/', [CustomerController::class, 'store'])->name('createCustomer')->middleware('permission:app.masterData.customers.createCustomer');
        Route::patch('/{customer}', [CustomerController::class, 'update'])->name('updateCustomer')->middleware('permission:app.masterData.customers.updateCustomer');
        Route::delete('/{customer}', [CustomerController::class, 'destroy'])->name('deleteCustomer')->middleware('permission:app.masterData.customers.deleteCustomer');
    });
    Route::group(['prefix' => 'areas', 'as' => 'areas.'], function () {
        Route::get('', [AreaController::class, 'index'])->name('index')->middleware('permission:app.masterData.areas.index');
        Route::post('/', [AreaController::class, 'store'])->name('createArea')->middleware('permission:app.masterData.areas.createArea');
        Route::patch('/{area}', [AreaController::class, 'update'])->name('updateArea')->middleware('permission:app.masterData.areas.updateArea');
        Route::delete('/{area}', [AreaController::class, 'destroy'])->name('deleteArea')->middleware('permission:app.masterData.areas.deleteArea');
    });
});

Route::group(['prefix' => 'settings', 'as' => 'settings.'], function (){
    Route::group(['prefix' => 'menu', 'as' => 'menu.'], function (){
        Route::get('/', [MenuController::class, 'index'])->name('index')->middleware('permission:app.settings.menu.index');

    });
});


