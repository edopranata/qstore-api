<?php

namespace App\Http\Controllers\Api\Management;

use App\Http\Controllers\Controller;
use App\Http\Resources\Management\MenuCollection;
use App\Http\Resources\Management\PermissionCollection;
use App\Http\Resources\Management\PermissionResource;
use App\Http\Resources\Management\RoleCollection;
use App\Http\Resources\Management\UserCollection;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{

    private function list(Request $request): \Illuminate\Database\Eloquent\Collection|\Illuminate\Contracts\Pagination\LengthAwarePaginator|array
    {
        $query = Permission::query()
            ->when($request->get('sortBy'), function ($query, $sort) {
                $sortBy = collect(json_decode($sort));
                return $query->orderBy($sortBy['key'], $sortBy['order']);
            });

        return $query->paginate($request->get('limit', 10));
    }
    public function index(Request $request): PermissionCollection
    {
        $menu = $this->list($request);

        return new PermissionCollection($menu);
    }

    public function view(string $id): PermissionResource
    {
        $permission = Permission::query()
            ->with(['roles'])
            ->where('id', $id)->first();
        if (!$permission) abort(404, 'Not found');
        return new PermissionResource($permission);
    }

    public function sync(Request $request)
    {
        $permissions = collect(Route::getRoutes())
            ->whereNotNull('action.as')
            ->map(function ($route) {
                $action = collect($route->action)->toArray();
                $method = collect($route->methods)->first();
                $as = str($action['as'])->lower();
                $name = str($action['as'])->replace('app.', '');
                return ($as->startsWith('app') && !$as->endsWith('.')) ? [
                    'method' => $method,
                    'name' => $action['as'],
                    'parent' => \str(collect(\str($name)->explode('.'))[0])->headline(),
                    'children' => \str(collect(\str($name)->explode('.'))[1])->headline(),
                    'title' => \str(collect(\str($name)->explode('.'))[2])->headline(),
                    'path' => $route->uri
                ] : null;
            })
            ->filter(function ($value) {
                return !is_null($value);
            })->values();
        DB::beginTransaction();
        try {
            foreach ($permissions as $permission) {
                Permission::query()->firstOrCreate([
                    'name' => $permission['name']
                ], [
                    'parent' => $permission['parent'],
                    'children' => $permission['children'],
                    'title' => \str($permission['title'])->replace('app', '')->trim()->ucfirst(),
                    'path' => $permission['path'],
                    'method' => $permission['method'],
                ]);

                $path = collect(\str($permission['name'])->explode('.'));
                $menuParent = $path[1];
                $menuIndex = $path[2];

                $menu = Menu::query()
                    ->firstOrCreate([
                        'name' => $path[0] . '.' . $path[1],
                    ], [
                        'title' => \str($menuParent)->headline(),
                        'path' => \str($permission['path'])->replace('api', ''),
                        'icon' => 'mdi-arrow-right'
                    ]);
                if ($path->last() === 'index') {
                    $menu->children()->firstOrCreate([
                        'name' => $permission['name']
                    ], [
                        'title' => \str($menuIndex)->headline(),
                        'path' => \str($permission['path'])->replace('api', ''),
                    ]);
                }
            }
            DB::commit();
            $menu = $this->list($request);

            return new PermissionCollection($menu);

        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'error' => [
                    'code' => $exception->getCode(),
                    'massage' => $exception->getMessage()
                ]
            ], 301);
        }
    }

    public function viewRolesUsers(string $id, Request $request): UserCollection|RoleCollection
    {
        $type = $request->get('load');
        $permission = Permission::query()
            ->with(['roles'])
            ->where('id', $id)
            ->first();

        if (str($type)->lower() == 'users') {

            $query = User::permission($permission->name)->whereNotIn('id', [1, auth()->id()]);
            $data = $request->get('limit', 0) > 0 ? $query->paginate($request->get('limit', 10)) : $query->get();

            return new UserCollection($data);
        } else {
            $query = $permission->roles();
            $data = $request->get('limit', 0) > 0 ? $query->paginate($request->get('limit', 10)) : $query->get();

            return new RoleCollection($data);
        }
    }
}
