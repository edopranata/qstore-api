<?php

use App\Http\Controllers\Api\Car\CarController;
use App\Http\Controllers\Api\Car\DriverController;
use App\Http\Controllers\Api\DeliveryOrder\CustomerController;
use App\Http\Controllers\Api\DeliveryOrder\DeliveryOrderController;
use App\Http\Controllers\Api\DeliveryOrder\DORecapReportController;
use App\Http\Controllers\Api\DeliveryOrder\DOReportController;
use App\Http\Controllers\Api\Invoice\InvoiceDataController;
use App\Http\Controllers\Api\Invoice\InvoiceDeliveryOrderController;
use App\Http\Controllers\Api\Loan\LoanController;
use App\Http\Controllers\Api\Management\PermissionController;
use App\Http\Controllers\Api\Management\RoleController;
use App\Http\Controllers\Api\Management\UserController;
use App\Http\Controllers\Api\Plantation\AreaController;
use App\Http\Controllers\Api\Plantation\LandController;
use App\Http\Controllers\Api\Plantation\PlantationController;
use App\Http\Controllers\Api\Plantation\PlantationCostController;
use App\Http\Controllers\Api\Report\Car\CarRecapReportController;
use App\Http\Controllers\Api\Report\Car\CarReportController;
use App\Http\Controllers\Api\Report\Plantation\LandReportController;
use App\Http\Controllers\Api\Report\Plantation\PlantationReportController;
use App\Http\Controllers\Api\Report\ReportController;
use App\Http\Controllers\Api\Trading\DataInvoiceCollectorController;
use App\Http\Controllers\Api\Trading\DataInvoiceFarmerController;
use App\Http\Controllers\Api\Trading\FarmerController;
use App\Http\Controllers\Api\Trading\InvoiceFarmersController;
use App\Http\Controllers\Api\Trading\TradingController;
use App\Http\Controllers\Api\Trading\TradingCostController;
use App\Http\Controllers\Api\Trading\TradingDetailsController;
use App\Http\Controllers\Api\Trading\TradingRecapReportController;
use App\Http\Controllers\Api\Trading\TradingReportController;
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

Route::group(['prefix' => 'mobil', 'as' => 'mobil.'], function (){
    Route::group(['prefix' => 'dataMobil', 'as' => 'dataMobil.'], function () {
        Route::get('', [CarController::class, 'index'])->name('index')->middleware('permission:app.mobil.dataMobil.index');
        Route::post('/', [CarController::class, 'store'])->name('simpanDataMobil')->middleware('permission:app.mobil.dataMobil.simpanDataMobil');
        Route::patch('/{car}', [CarController::class, 'update'])->name('updateDataMobil')->middleware('permission:app.mobil.dataMobil.updateDataMobil');
        Route::delete('/{car}', [CarController::class, 'destroy'])->name('hapusDataMobil')->middleware('permission:app.mobil.dataMobil.hapusDataMobil');
    });

    Route::group(['prefix' => 'dataSupir', 'as' => 'dataSupir.'], function () {
        Route::get('', [DriverController::class, 'index'])->name('index')->middleware('permission:app.mobil.dataSupir.index');
        Route::post('/', [DriverController::class, 'store'])->name('simpanDataSupir')->middleware('permission:app.mobil.dataSupir.simpanDataSupir');
        Route::patch('/{driver}', [DriverController::class, 'update'])->name('updateDataSupir')->middleware('permission:app.mobil.dataSupir.updateDataSupir');
        Route::delete('/{driver}', [DriverController::class, 'destroy'])->name('hapusDataSupir')->middleware('permission:app.mobil.dataSupir.hapusDataSupir');
    });

    Route::group(['prefix' => 'dataPinjamanSupir', 'as' => 'dataPinjamanSupir.'], function () {
        Route::get('', [LoanController::class, 'index'])->name('index')->middleware('permission:app.mobil.dataPinjamanSupir.index');
        Route::post('/{loan}/add', [LoanController::class, 'addLoan'])->name('tambahPinjaman')->middleware('permission:app.mobil.dataPinjamanSupir.tambahPinjaman');
        Route::post('/{loan}/installment', [LoanController::class, 'installmentLoan'])->name('angsurPinjaman')->middleware('permission:app.mobil.dataPinjamanSupir.angsurPinjaman');
    });

    Route::group(['prefix' => 'pinjamanBaru', 'as' => 'pinjamanBaru.'], function () {
        Route::get('', [LoanController::class, 'create'])->name('index')->middleware('permission:app.mobil.pinjamanBaru.index');
        Route::post('', [LoanController::class, 'store'])->name('simpanPinjamanBaru')->middleware('permission:app.mobil.pinjamanBaru.simpanPinjamanBaru');
    });

    Route::group(['prefix' => 'biayaMobil', 'as' => 'biayaMobil.'], function () {
        Route::get('/', [\App\Http\Controllers\Api\Car\CarCostController::class, 'index'])->name('index')->middleware('permission:app.mobil.biayaMobil.index');
        Route::post('/', [\App\Http\Controllers\Api\Car\CarCostController::class, 'store'])->name('simpanBiayaMobil')->middleware('permission:app.mobil.biayaMobil.simpanBiayaMobil');
        Route::patch('/{cost}', [\App\Http\Controllers\Api\Car\CarCostController::class, 'update'])->name('ubahBiayaMobil')->middleware('permission:app.mobil.biayaMobil.ubahBiayaMobil');
        Route::delete('/{cost}', [\App\Http\Controllers\Api\Car\CarCostController::class, 'destroy'])->name('hapusBiayaMobil')->middleware('permission:app.mobil.biayaMobil.hapusBiayaMobil');

    });
});

Route::group(['prefix' => 'perkebunan', 'as' => 'perkebunan.'], function (){
    Route::group(['prefix' => 'wilayah', 'as' => 'wilayah.'], function () {
        Route::get('', [AreaController::class, 'index'])->name('index')->middleware('permission:app.perkebunan.wilayah.index');
        Route::post('/', [AreaController::class, 'store'])->name('simpanDataWilayah')->middleware('permission:app.perkebunan.wilayah.simpanDataWilayah');
        Route::patch('/{area}', [AreaController::class, 'update'])->name('updateDataWilayah')->middleware('permission:app.perkebunan.wilayah.updateDataWilayah');
        Route::delete('/{area}', [AreaController::class, 'destroy'])->name('hapusDataWilayah')->middleware('permission:app.perkebunan.wilayah.hapusDataWilayah');
    });
    Route::group(['prefix' => 'lahan', 'as' => 'lahan.'], function () {
        Route::get('', [LandController::class, 'index'])->name('index')->middleware('permission:app.perkebunan.lahan.index');
        Route::post('/', [LandController::class, 'store'])->name('simpanDataLahan')->middleware('permission:app.perkebunan.lahan.simpanDataLahan');
        Route::patch('/{land}', [LandController::class, 'update'])->name('updateDataLahan')->middleware('permission:app.perkebunan.lahan.updateDataLahan');
        Route::delete('/{land}', [LandController::class, 'destroy'])->name('hapusDataLahan')->middleware('permission:app.perkebunan.lahan.hapusDataLahan');
    });

    Route::group(['prefix' => 'hasilKebun', 'as' => 'hasilKebun.'], function () {
        Route::get('', [PlantationController::class, 'index'])->name('index')->middleware('permission:app.perkebunan.hasilKebun.index');
        Route::post('/', [PlantationController::class, 'store'])->name('createHasilKebun')->middleware('permission:app.perkebunan.hasilKebun.createHasilKebun');
        Route::patch('/{plantation}', [PlantationController::class, 'update'])->name('updateHasilKebun')->middleware('permission:app.perkebunan.hasilKebun.updateHasilKebun');
        Route::delete('/{plantation}', [PlantationController::class, 'destroy'])->name('deleteHasilKebun')->middleware('permission:app.perkebunan.hasilKebun.deleteHasilKebun');
    });

    Route::group(['prefix' => 'biayaKebun', 'as' => 'biayaKebun.'], function () {
        Route::get('/', [PlantationCostController::class, 'index'])->name('index')->middleware('permission:app.perkebunan.biayaKebun.index');
        Route::post('/', [PlantationCostController::class, 'store'])->name('simpanBiayaKebun')->middleware('permission:app.perkebunan.biayaKebun.simpanBiayaKebun');
        Route::patch('/{cost}', [PlantationCostController::class, 'update'])->name('ubahBiayaKebun')->middleware('permission:app.perkebunan.biayaKebun.ubahBiayaKebun');
        Route::delete('/{cost}', [PlantationCostController::class, 'destroy'])->name('hapusBiayaKebun')->middleware('permission:app.perkebunan.biayaKebun.hapusBiayaKebun');
    });
});

Route::group(['prefix' => 'jualBeliSawit', 'as' => 'jualBeliSawit.'], function (){
    Route::group(['prefix' => 'dataPetani', 'as' => 'dataPetani.'], function () {
        Route::get('', [FarmerController::class, 'index'])->name('index')->middleware('permission:app.jualBeliSawit.dataPetani.index');
        Route::post('/', [FarmerController::class, 'store'])->name('simpanDataPetani')->middleware('permission:app.jualBeliSawit.dataPetani.simpanDataPetani');
        Route::patch('/{customer}', [FarmerController::class, 'update'])->name('updateDataPetani')->middleware('permission:app.jualBeliSawit.dataPetani.updateDataPetani');
        Route::delete('/{customer}', [FarmerController::class, 'destroy'])->name('hapusDataPetani')->middleware('permission:app.jualBeliSawit.dataPetani.hapusDataPetani');
    });

    Route::group(['prefix' => 'dataPinjamanPetani', 'as' => 'dataPinjamanPetani.'], function () {
        Route::get('', [LoanController::class, 'index'])->name('index')->middleware('permission:app.jualBeliSawit.dataPinjamanPetani.index');
        Route::post('/{loan}/add', [LoanController::class, 'addLoan'])->name('tambahPinjaman')->middleware('permission:app.jualBeliSawit.dataPinjamanPetani.tambahPinjaman');
        Route::post('/{loan}/installment', [LoanController::class, 'installmentLoan'])->name('angsurPinjaman')->middleware('permission:app.jualBeliSawit.dataPinjamanPetani.angsurPinjaman');
    });

    Route::group(['prefix' => 'pinjamanBaru', 'as' => 'pinjamanBaru.'], function () {
        Route::get('', [LoanController::class, 'create'])->name('index')->middleware('permission:app.jualBeliSawit.pinjamanBaru.index');
        Route::post('', [LoanController::class, 'store'])->name('simpanPinjamanBaru')->middleware('permission:app.jualBeliSawit.pinjamanBaru.simpanPinjamanBaru');
    });

    Route::group(['prefix' => 'pembelianSawit', 'as' => 'pembelianSawit.'], function () {
        Route::get('', [TradingController::class, 'index'])->name('index')->middleware('permission:app.jualBeliSawit.pembelianSawit.index');
        Route::post('/', [TradingController::class, 'store'])->name('createTransaction')->middleware('permission:app.jualBeliSawit.pembelianSawit.createTransaction');
        Route::patch('/{trade}', [TradingController::class, 'update'])->name('updateTransaction')->middleware('permission:app.jualBeliSawit.pembelianSawit.updateTransaction');
        Route::delete('/{trade}', [TradingController::class, 'destroy'])->name('deleteTransaction')->middleware('permission:app.jualBeliSawit.pembelianSawit.deleteTransaction');
        Route::patch('/{trade}/details', [TradingController::class, 'updateFactory'])->name('createFactoryTransaction')->middleware('permission:app.jualBeliSawit.pembelianSawit.createFactoryTransaction');
        Route::get('/{trade}/details', [TradingController::class, 'show'])->name('viewDetailsTransaction')->middleware('permission:app.jualBeliSawit.pembelianSawit.viewDetailsTransaction');

        Route::post('/{trade}/details', [TradingDetailsController::class, 'store'])->name('createDetailsTransaction')->middleware('permission:app.jualBeliSawit.pembelianSawit.createDetailsTransaction');
        Route::delete('/{trade}/details/{details}', [TradingDetailsController::class, 'destroy'])->scopeBindings()->name('deleteDetailsTransaction')->middleware('permission:app.jualBeliSawit.pembelianSawit.deleteDetailsTransaction');
        Route::patch('/{trade}/details/{details}', [TradingDetailsController::class, 'update'])->scopeBindings()->name('updateDetailsTransaction')->middleware('permission:app.jualBeliSawit.pembelianSawit.updateDetailsTransaction');
    });

    Route::group(['prefix' => 'biayaJualBeliSawit', 'as' => 'biayaJualBeliSawit.'], function () {
        Route::get('/', [TradingCostController::class, 'index'])->name('index')->middleware('permission:app.jualBeliSawit.biayaJualBeliSawit.index');
        Route::post('/', [TradingCostController::class, 'store'])->name('simpanBiayaJualBeliSawit')->middleware('permission:app.jualBeliSawit.biayaJualBeliSawit.simpanBiayaJualBeliSawit');
        Route::patch('/{cost}', [TradingCostController::class, 'update'])->name('ubahJuBiayaalBeliSawit')->middleware('permission:app.jualBeliSawit.biayaJualBeliSawit.ubahJuBiayaalBeliSawit');
        Route::delete('/{cost}', [TradingCostController::class, 'destroy'])->name('hapusJBiayaualBeliSawit')->middleware('permission:app.jualBeliSawit.biayaJualBeliSawit.hapusJBiayaualBeliSawit');
    });

    Route::group(['prefix' => 'buatInvoicePetani', 'as' => 'buatInvoicePetani.'], function () {
        Route::get('/', [InvoiceFarmersController::class, 'index'])->name('index')->middleware('permission:app.jualBeliSawit.buatInvoicePetani.index');
        Route::get('/{customer}/details', [InvoiceFarmersController::class, 'show'])->name('details')->middleware('permission:app.jualBeliSawit.buatInvoicePetani.details');
        Route::post('/{customer}/details', [InvoiceFarmersController::class, 'store'])->name('simpanInvoicePetani')->middleware('permission:app.jualBeliSawit.buatInvoicePetani.simpanInvoicePetani');
    });

    Route::group(['prefix' => 'dataInvoicePetani', 'as' => 'dataInvoicePetani.'], function () {
        Route::get('/', [DataInvoiceFarmerController::class, 'index'])->name('index')->middleware('permission:app.jualBeliSawit.dataInvoicePetani.index');
        Route::get('/{invoice:invoice_number}/print', [DataInvoiceFarmerController::class, 'show'])->name('printInvoice')->middleware('permission:app.jualBeliSawit.dataInvoicePetani.printInvoice');
    });

    Route::group(['prefix' => 'laporan', 'as' => 'laporan.'], function () {
        Route::get('/', [ReportController::class, 'index'])->name('index')->middleware('permission:permission:app.jualBeliSawit.laporan.index');

        Route::match(['get', 'post'], '/jualBeliSawit', TradingReportController::class)->name('jualBeliSawit')->middleware('permission:app.jualBeliSawit.laporan.jualBeliSawit');
        Route::match(['get', 'post'], '/printJualBeliSawit', TradingReportController::class)->name('printJualBeliSawit')->middleware('permission:app.jualBeliSawit.laporan.printJualBeliSawit');

        Route::match(['get', 'post'], '/rekapituliasiJualBeliSawit', TradingRecapReportController::class)->name('rekapituliasiJualBeliSawit')->middleware('permission:app.jualBeliSawit.laporan.rekapituliasiJualBeliSawit');
        Route::match(['get', 'post'], '/printRekapituliasiJualBeliSawit', TradingRecapReportController::class)->name('printRekapituliasiJualBeliSawit')->middleware('permission:app.jualBeliSawit.laporan.printRekapituliasiJualBeliSawit');
    });
});

Route::group(['prefix' => 'deliveryOrder', 'as' => 'deliveryOrder.'], function (){
    Route::group(['prefix' => 'dataPengepul', 'as' => 'dataPengepul.'], function () {
        Route::get('', [CustomerController::class, 'index'])->name('index')->middleware('permission:app.deliveryOrder.dataPengepul.index');
        Route::post('/', [CustomerController::class, 'store'])->name('simpanDataPengepul')->middleware('permission:app.deliveryOrder.dataPengepul.simpanDataPengepul');
        Route::patch('/{customer}', [CustomerController::class, 'update'])->name('updateDataPengepul')->middleware('permission:app.deliveryOrder.dataPengepul.updateDataPengepul');
        Route::delete('/{customer}', [CustomerController::class, 'destroy'])->name('hapusDataPengepul')->middleware('permission:app.deliveryOrder.dataPengepul.hapusDataPengepul');
    });

    Route::group(['prefix' => 'dataPinjamanPengepul', 'as' => 'dataPinjamanPengepul.'], function () {
        Route::get('', [LoanController::class, 'index'])->name('index')->middleware('permission:app.deliveryOrder.dataPinjamanPengepul.index');
        Route::post('/{loan}/add', [LoanController::class, 'addLoan'])->name('tambahPinjaman')->middleware('permission:app.deliveryOrder.dataPinjamanPengepul.tambahPinjaman');
        Route::post('/{loan}/installment', [LoanController::class, 'installmentLoan'])->name('angsurPinjaman')->middleware('permission:app.deliveryOrder.dataPinjamanPengepul.angsurPinjaman');
    });

    Route::group(['prefix' => 'pinjamanBaru', 'as' => 'pinjamanBaru.'], function () {
        Route::get('', [LoanController::class, 'create'])->name('index')->middleware('permission:app.deliveryOrder.pinjamanBaru.index');
        Route::post('', [LoanController::class, 'store'])->name('simpanPinjamanBaru')->middleware('permission:app.deliveryOrder.pinjamanBaru.simpanPinjamanBaru');
    });

    Route::group(['prefix' => 'transaksiDO', 'as' => 'transaksiDO.'], function () {
        Route::get('', [DeliveryOrderController::class, 'index'])->name('index')->middleware('permission:app.deliveryOrder.transaksiDO.index');
        Route::post('/', [DeliveryOrderController::class, 'store'])->name('createDeliveryOrder')->middleware('permission:app.deliveryOrder.transaksiDO.createDeliveryOrder');
        Route::patch('/{delivery}', [DeliveryOrderController::class, 'update'])->name('updateDeliveryOrder')->middleware('permission:app.deliveryOrder.transaksiDO.updateDeliveryOrder');
        Route::delete('/{delivery}', [DeliveryOrderController::class, 'destroy'])->name('deleteDeliveryOrder')->middleware('permission:app.deliveryOrder.transaksiDO.deleteDeliveryOrder');
    });

    Route::group(['prefix' => 'buatInvoiceDO', 'as' => 'buatInvoiceDO.'], function () {
        Route::get('/', [InvoiceDeliveryOrderController::class, 'index'])->name('index')->middleware('permission:app.deliveryOrder.buatInvoiceDO.index');
        Route::post('/', [InvoiceDeliveryOrderController::class, 'store'])->name('simpanInvoiceDO')->middleware('permission:app.deliveryOrder.buatInvoiceDO.simpanInvoiceDO');
    });

    Route::group(['prefix' => 'dataInvoicePengepul', 'as' => 'dataInvoicePengepul.'], function () {
        Route::get('/', [DataInvoiceCollectorController::class, 'index'])->name('index')->middleware('permission:app.deliveryOrder.dataInvoicePengepul.index');
        Route::get('/{invoice:invoice_number}/print', [DataInvoiceCollectorController::class, 'show'])->name('printInvoice')->middleware('permission:app.deliveryOrder.dataInvoicePengepul.printInvoice');
    });

    Route::group(['prefix' => 'laporan', 'as' => 'laporan.'], function () {
        Route::get('/', [ReportController::class, 'index'])->name('index')->middleware('permission:permission:app.deliveryOrder.laporan.index');

        Route::match(['get', 'post'], '/deliveryOrder', DOReportController::class)->name('deliveryOrder')->middleware('permission:app.deliveryOrder.laporan.deliveryOrder');
        Route::match(['get', 'post'], '/printDeliveryOrder', DOReportController::class)->name('printdeliveryOrder')->middleware('permission:app.deliveryOrder.laporan.printdeliveryOrder');

        Route::match(['get', 'post'], '/rekapitulasiDO', DORecapReportController::class)->name('rekapitulasiDO')->middleware('permission:app.deliveryOrder.laporan.rekapitulasiDO');
        Route::match(['get', 'post'], '/printRekapitulasiDO', DORecapReportController::class)->name('printRekapitulasiDO')->middleware('permission:app.deliveryOrder.laporan.printRekapitulasiDO');
    });
});










//Route::group(['prefix' => 'masterData', 'as' => 'masterData.'], function () {
//    Route::group(['prefix' => 'mobil', 'as' => 'mobil.'], function () {
//        Route::get('', [CarController::class, 'index'])->name('index')->middleware('permission:app.masterData.mobil.index');
//        Route::post('/', [CarController::class, 'store'])->name('createCar')->middleware('permission:app.masterData.mobil.createCar');
//        Route::patch('/{car}', [CarController::class, 'update'])->name('updateCar')->middleware('permission:app.masterData.mobil.updateCar');
//        Route::delete('/{car}', [CarController::class, 'destroy'])->name('deleteCar')->middleware('permission:app.masterData.mobil.deleteCar');
//    });
//    Route::group(['prefix' => 'supir', 'as' => 'supir.'], function () {
//        Route::get('', [DriverController::class, 'index'])->name('index')->middleware('permission:app.masterData.supir.index');
//        Route::post('/', [DriverController::class, 'store'])->name('createDriver')->middleware('permission:app.masterData.supir.createDriver');
//        Route::patch('/{driver}', [DriverController::class, 'update'])->name('updateDriver')->middleware('permission:app.masterData.supir.updateDriver');
//        Route::delete('/{driver}', [DriverController::class, 'destroy'])->name('deleteDriver')->middleware('permission:app.masterData.supir.deleteDriver');
//    });
//    Route::group(['prefix' => 'pelanggan', 'as' => 'pelanggan.'], function () {
//        Route::get('', [CustomerController::class, 'index'])->name('index')->middleware('permission:app.masterData.pelanggan.index');
//        Route::post('/', [CustomerController::class, 'store'])->name('createCustomer')->middleware('permission:app.masterData.pelanggan.createCustomer');
//        Route::patch('/{customer}', [CustomerController::class, 'update'])->name('updateCustomer')->middleware('permission:app.masterData.pelanggan.updateCustomer');
//        Route::delete('/{customer}', [CustomerController::class, 'destroy'])->name('deleteCustomer')->middleware('permission:app.masterData.pelanggan.deleteCustomer');
//    });
//    Route::group(['prefix' => 'wilayah', 'as' => 'wilayah.'], function () {
//        Route::get('', [AreaController::class, 'index'])->name('index')->middleware('permission:app.masterData.wilayah.index');
//        Route::post('/', [AreaController::class, 'store'])->name('createArea')->middleware('permission:app.masterData.wilayah.createArea');
//        Route::patch('/{area}', [AreaController::class, 'update'])->name('updateArea')->middleware('permission:app.masterData.wilayah.updateArea');
//        Route::delete('/{area}', [AreaController::class, 'destroy'])->name('deleteArea')->middleware('permission:app.masterData.wilayah.deleteArea');
//    });
//    Route::group(['prefix' => 'lahan', 'as' => 'lahan.'], function () {
//        Route::get('', [LandController::class, 'index'])->name('index')->middleware('permission:app.masterData.lahan.index');
//        Route::post('/', [LandController::class, 'store'])->name('createLand')->middleware('permission:app.masterData.lahan.createLand');
//        Route::patch('/{land}', [LandController::class, 'update'])->name('updateLand')->middleware('permission:app.masterData.lahan.updateLand');
//        Route::delete('/{land}', [LandController::class, 'destroy'])->name('deleteLand')->middleware('permission:app.masterData.lahan.deleteLand');
//    });
//});
//Route::group(['prefix' => 'pinjaman', 'as' => 'pinjaman.'], function () {
//    Route::group(['prefix' => 'dataPinjaman', 'as' => 'dataPinjaman.'], function () {
//        Route::get('', [LoanController::class, 'index'])->name('index')->middleware('permission:app.pinjaman.dataPinjaman.index');
//        Route::post('/{loan}/add', [LoanController::class, 'addLoan'])->name('tambahPinjaman')->middleware('permission:app.pinjaman.dataPinjaman.tambahPinjaman');
//        Route::post('/{loan}/installment', [LoanController::class, 'installmentLoan'])->name('angsurPinjaman')->middleware('permission:app.pinjaman.dataPinjaman.angsurPinjaman');
//
//    });
//    Route::group(['prefix' => 'pinjamanBaru', 'as' => 'pinjamanBaru.'], function () {
//        Route::get('', [LoanController::class, 'create'])->name('index')->middleware('permission:app.pinjaman.pinjamanBaru.index');
//        Route::post('', [LoanController::class, 'store'])->name('simpanPinjamanBaru')->middleware('permission:app.pinjaman.pinjamanBaru.simpanPinjamanBaru');
//    });
//});
//Route::group(['prefix' => 'transaction', 'as' => 'transaction.'], function () {
//    Route::group(['prefix' => 'pembelianSawit', 'as' => 'pembelianSawit.'], function () {
//        Route::get('', [TradeBuyController::class, 'index'])->name('index')->middleware('permission:app.transaction.pembelianSawit.index');
//        Route::post('/', [TradeBuyController::class, 'store'])->name('createTransaction')->middleware('permission:app.transaction.pembelianSawit.createTransaction');
//        Route::patch('/{trade}', [TradeBuyController::class, 'update'])->name('updateTransaction')->middleware('permission:app.transaction.pembelianSawit.updateTransaction');
//        Route::delete('/{trade}', [TradeBuyController::class, 'destroy'])->name('deleteTransaction')->middleware('permission:app.transaction.pembelianSawit.deleteTransaction');
//        Route::patch('/{trade}/details', [TradeBuyController::class, 'updateFactory'])->name('createFactoryTransaction')->middleware('permission:app.transaction.pembelianSawit.createFactoryTransaction');
//        Route::get('/{trade}/details', [TradeBuyController::class, 'show'])->name('viewDetailsTransaction')->middleware('permission:app.transaction.pembelianSawit.viewDetailsTransaction');
//
//        Route::post('/{trade}/details', [TradeBuyDetailsController::class, 'store'])->name('createDetailsTransaction')->middleware('permission:app.transaction.pembelianSawit.createDetailsTransaction');
//        Route::delete('/{trade}/details/{details}', [TradeBuyDetailsController::class, 'destroy'])->scopeBindings()->name('deleteDetailsTransaction')->middleware('permission:app.transaction.pembelianSawit.deleteDetailsTransaction');
//        Route::patch('/{trade}/details/{details}', [TradeBuyDetailsController::class, 'update'])->scopeBindings()->name('updateDetailsTransaction')->middleware('permission:app.transaction.pembelianSawit.updateDetailsTransaction');
//    });
//
//    Route::group(['prefix' => 'hasilKebun', 'as' => 'hasilKebun.'], function () {
//        Route::get('', [PlantationController::class, 'index'])->name('index')->middleware('permission:app.transaction.hasilKebun.index');
//        Route::post('/', [PlantationController::class, 'store'])->name('createHasilKebun')->middleware('permission:app.transaction.hasilKebun.createHasilKebun');
//        Route::patch('/{plantation}', [PlantationController::class, 'update'])->name('updateHasilKebun')->middleware('permission:app.transaction.hasilKebun.updateHasilKebun');
//        Route::delete('/{plantation}', [PlantationController::class, 'destroy'])->name('deleteHasilKebun')->middleware('permission:app.transaction.hasilKebun.deleteHasilKebun');
//    });
//
//    Route::group(['prefix' => 'deliveryOrders', 'as' => 'deliveryOrders.'], function () {
//        Route::get('', [DeliveryOrderController::class, 'index'])->name('index')->middleware('permission:app.transaction.deliveryOrders.index');
//        Route::post('/', [DeliveryOrderController::class, 'store'])->name('createDeliveryOrder')->middleware('permission:app.transaction.deliveryOrders.createDeliveryOrder');
//        Route::patch('/{delivery}', [DeliveryOrderController::class, 'update'])->name('updateDeliveryOrder')->middleware('permission:app.transaction.deliveryOrders.updateDeliveryOrder');
//        Route::delete('/{delivery}', [DeliveryOrderController::class, 'destroy'])->name('deleteDeliveryOrder')->middleware('permission:app.transaction.deliveryOrders.deleteDeliveryOrder');
//    });
//});
//Route::group(['prefix' => 'biaya', 'as' => 'biaya.'], function () {
//    Route::group(['prefix' => 'biayaMobil', 'as' => 'biayaMobil.'], function () {
//        Route::get('/', [CarCostController::class, 'index'])->name('index')->middleware('permission:app.biaya.biayaMobil.index');
//        Route::post('/', [CarCostController::class, 'store'])->name('simpanBiayaMobil')->middleware('permission:app.biaya.biayaMobil.simpanBiayaMobil');
//        Route::patch('/{cost}', [CarCostController::class, 'update'])->name('ubahBiayaMobil')->middleware('permission:app.biaya.biayaMobil.ubahBiayaMobil');
//        Route::delete('/{cost}', [CarCostController::class, 'destroy'])->name('hapusBiayaMobil')->middleware('permission:app.biaya.biayaMobil.hapusBiayaMobil');
//
//    });
//    Route::group(['prefix' => 'biayaPembelianSawit', 'as' => 'biayaPembelianSawit.'], function () {
//        Route::get('/', [TradingCostController::class, 'index'])->name('index')->middleware('permission:app.biaya.biayaPembelianSawit.index');
//        Route::post('/', [TradingCostController::class, 'store'])->name('simpanPembelianSawit')->middleware('permission:app.biaya.biayaPembelianSawit.simpanPembelianSawit');
//        Route::patch('/{cost}', [TradingCostController::class, 'update'])->name('ubahPembelianSawit')->middleware('permission:app.biaya.biayaPembelianSawit.ubahPembelianSawit');
//        Route::delete('/{cost}', [TradingCostController::class, 'destroy'])->name('hapusPembelianSawit')->middleware('permission:app.biaya.biayaPembelianSawit.hapusPembelianSawit');
//    });
//    Route::group(['prefix' => 'biayaKebun', 'as' => 'biayaKebun.'], function () {
//        Route::get('/', [PlantationCostController::class, 'index'])->name('index')->middleware('permission:app.biaya.biayaKebun.index');
//        Route::post('/', [PlantationCostController::class, 'store'])->name('simpanBiayaKebun')->middleware('permission:app.biaya.biayaKebun.simpanBiayaKebun');
//        Route::patch('/{cost}', [PlantationCostController::class, 'update'])->name('ubahBiayaKebun')->middleware('permission:app.biaya.biayaKebun.ubahBiayaKebun');
//        Route::delete('/{cost}', [PlantationCostController::class, 'destroy'])->name('hapusBiayaKebun')->middleware('permission:app.biaya.biayaKebun.hapusBiayaKebun');
//    });
//});
//
Route::group(['prefix' => 'invoice', 'as' => 'invoice.'], function () {
    Route::group(['prefix' => 'invoiceData', 'as' => 'invoiceData.'], function () {
        Route::get('/', [InvoiceDataController::class, 'index'])->name('index')->middleware('permission:app.invoice.invoiceData.index');
        Route::get('/{invoice:invoice_number}/print', [InvoiceDataController::class, 'show'])->name('printInvoice')->middleware('permission:app.invoice.invoiceData.printInvoice');
    });

//    Route::group(['prefix' => 'buatInvoiceDO', 'as' => 'buatInvoiceDO.'], function () {
//        Route::get('/', [InvoiceDeliveryOrderController::class, 'index'])->name('index')->middleware('permission:app.invoice.buatInvoiceDO.index');
//        Route::post('/', [InvoiceDeliveryOrderController::class, 'store'])->name('simpanInvoiceDO')->middleware('permission:app.invoice.buatInvoiceDO.simpanInvoiceDO');
//    });
//    Route::group(['prefix' => 'buatInvoicePetani', 'as' => 'buatInvoicePetani.'], function () {
//        Route::get('/', [InvoiceFarmersController::class, 'index'])->name('index')->middleware('permission:app.invoice.buatInvoicePetani.index');
//        Route::post('/', [InvoiceFarmersController::class, 'store'])->name('simpanInvoicePetani')->middleware('permission:app.invoice.buatInvoicePetani.simpanInvoicePetani');
//
//    });
});

Route::group(['prefix' => 'laporan', 'as' => 'laporan.'], function () {
    Route::group(['prefix' => 'dataLaporan', 'as' => 'dataLaporan.'], function () {
        Route::get('/', [ReportController::class, 'index'])->name('index')->middleware('permission:app.laporan.dataLaporan.index');

        Route::get('/hasilKebun', PlantationReportController::class)->name('hasilKebun')->middleware('permission:app.laporan.dataLaporan.hasilKebun');
        Route::get('/printHasilKebun', PlantationReportController::class)->name('printHasilKebun')->middleware('permission:app.laporan.dataLaporan.printHasilKebun');

        Route::match(['get', 'post'], '/hasilLahan', LandReportController::class)->name('hasilLahan')->middleware('permission:app.laporan.dataLaporan.hasilLahan');
        Route::match(['get', 'post'], '/printHasilLahan', LandReportController::class)->name('printHasilLahan')->middleware('permission:app.laporan.dataLaporan.printHasilLahan');

//        Route::get('/hasilLahanPerArea', [AreaReportController::class, 'index'])->name('hasilLahanPerArea'); //->middleware('permission:app.laporan.dataLaporan.hasilLahanPerArea');
//        Route::get('/printHasilLahanPerArea', [AreaReportController::class, 'index'])->name('printHasilLahanPerArea'); //->middleware('permission:app.laporan.dataLaporan.printHasilLahanPerArea');

        Route::match(['get', 'post'], '/penghasilanMobil', CarReportController::class)->name('penghasilanMobil')->middleware('permission:app.laporan.dataLaporan.penghasilanMobil');
        Route::match(['get', 'post'], '/printPenghasilanMobil', CarReportController::class)->name('printPenghasilanMobil')->middleware('permission:app.laporan.dataLaporan.printPenghasilanMobil');

        Route::match(['get', 'post'], '/rekapPenghasilanMobil', CarRecapReportController::class)->name('rekapPenghasilanMobil'); //->middleware('permission:app.laporan.dataLaporan.rekapPenghasilanMobil');
        Route::match(['get', 'post'], '/printRekapPenghasilanMobil', CarRecapReportController::class)->name('printRekapPenghasilanMobil'); //->middleware('permission:app.laporan.dataLaporan.printRekapPenghasilanMobil');

    });

});


