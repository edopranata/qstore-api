<?php

use App\Http\Resources\Management\RoleCollection;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return response()->json(['status' => 'OK'], 201);
});
Route::get('tos', function (\Illuminate\Http\Request $request) {
//    $role = Role::query()->with('permissions', 'users')->first();
//    return $role->jsonSerialize();
    $role = Role::findById(3)->load('permissions');

    $active = $role->permissions;

    $permissions = \Spatie\Permission\Models\Permission::all();

    $inactive = $permissions->whereNotIn('id', $active->pluck('id'));

    $all = $permissions
//        ->filter(function ($item) {
//            return $item['title'] === 'Index';
//        })
        ->groupBy(['parent', function (object $item) {
            return collect($item['children'])->first();
        }], preserveKeys: true);
    return response()->json([
        'role' => $role->only(['id', 'name', 'created_at']),
        'all' => $all,
        'active' => collect($active)->pluck('id'),
        'inactive' => $inactive

    ], 201);
});
