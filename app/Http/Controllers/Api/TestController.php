<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\Management\MenuCollection;
use App\Http\Resources\UserResource;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class TestController extends Controller
{

    public function index()
    {
        $permissions = collect(Route::getRoutes())
            ->whereNotNull('action.as')
            ->map(function ($route) {
                $action = collect($route->action)->toArray();

                return !\str($action['as'])->lower()->startsWith('app') ? null : [
                    'name' => $action['as'],
                    'path' => $route->uri
                ];
            })
            ->filter(function ($value) {
                return !is_null($value);
            });

        Menu::query()->truncate();

        foreach ($permissions as $key => $permission) {
            $params = collect(\str($permission['name'])->explode('.'));
            $menuParent = $params[1];
            $menuIndex = $params[2];
            $data[$key]['name'] = $permission['name'];
            $data[$key]['length'] = $params->count();

            $menu = Menu::query()
                ->firstOrCreate([
                    'name'  => $params[0] . '.' . $params[1],
                ], [
                    'title' => \str($menuParent)->headline(),
                    'path'  => $permission['path'],
                    'icon'  => 'mdi-arrow-right'
                ]);
            if($params->last() === 'index'){
                $menu->children()->create([
                    'name'  => $permission['name'],
                    'title' => \str($menuIndex)->headline(),
                    'path'  => $permission['path'],
                    'icon'  => 'mdi-arrow-right'
                ]);
            }
        }

        $data = Menu::query()->with('children')->get();

        return response()->json(['data' => $data], 201);
    }

    public function menu()
    {
        $permissions = collect(Route::getRoutes())
            ->whereNotNull('action.as')
            ->map(function ($route) {
                $action = collect($route->action)->toArray();
                $method = collect($route->methods)->first();

                if (!str($action['as'])->lower()->startsWith('app')) {
                    $name = Str::replace('app.', '', $action['as']);
                    return [
                        'method' => $method,
                        'name' => $action['as'],
                        'parent' => \str(collect(\str($name)->explode('.'))[0])->headline(),
                        'children' => \str(collect(\str($name)->explode('.'))[1])->headline(),
                        'title' => \str(collect(\str($name)->explode('.'))[2])->headline(),
                        'path' => $route->uri
                    ];
                }else {
                    return [];
                }
            })
            ->filter(function ($value) {
                return !is_null($value);
            });
        return new UserResource(User::query()->first());
    }
    public function get(Request $request)
    {
        $permissions = collect(Route::getRoutes())
            ->whereNotNull('action.as')
            ->map(function ($route) {
                $action = collect($route->action)->toArray();
                $method = collect($route->methods)->first();

                return str($action['as'])->lower()->startsWith('app') ? [
                    'method' => $method,
                    'name' => $action['as'],
                    'description' => Str::replace('app ', '', Str::replace('.', ' ', $action['as'])),
                    'path' => $route->uri
                ] : null;
            })
            ->filter(function ($value) {
                return !is_null($value);
            });


        $permissions = $request->user()->getAllPermissions()->sortBy('name');

        $data = [];
        $index=0;
        $index_menu=0;

        foreach ($permissions as $key => $permission) {
            $menu_array = collect(\str($permission['name'])->explode('.'));
            $menu_type = $menu_array->last();

            if ($menu_type === 'aaa' || $menu_type === 'index') {

                if($menu_type==='aaa'){
                    $index++;
                    $index_menu=0;

                    $menu_array->pop();
                    $data[$index]['text']  = \str($menu_array->last())->headline();
                    $data[$index]['icon']   = $permission['menu_icon'] ?? null;
                    $data[$index]['to']   = \str($permission['name'])->replace('aaa', 'index');
                    $data[$index]['path']   = $permission['path'] ?? null;

                }

                if($menu_type==='index'){
                    $menu_array->pop();
                    $data[$index]['children'][$index_menu]['text'] = \str($menu_array->last())->headline();
                    $data[$index]['children'][$index_menu]['to'] = $permission['name'];
                    $data[$index]['children'][$index_menu]['path'] = $permission['path'] ?? null;
                    $index_menu++;
                }

            }
        }
//        return response()->json(['data' => $permissions->values()], 201);
        return response()->json(['data' => collect($data)->values()], 201);
    }
}
