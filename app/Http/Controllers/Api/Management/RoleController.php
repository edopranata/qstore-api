<?php

namespace App\Http\Controllers\Api\Management;

use App\Http\Controllers\Controller;
use App\Http\Resources\Management\PermissionCollection;
use App\Http\Resources\Management\RoleCollection;
use App\Http\Resources\Management\RoleResource;
use App\Http\Resources\Management\UserCollection;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(Request $request): RoleCollection
    {
        $role = Role::query()
            ->whereNotIn('id', [1])
            ->when($request->name, function ($query, $name){
                $query->where('name', 'like',  "%$name%");
            })
            ->when($request->get('sortBy'), function ($query, $sort) {
                $sortBy = collect(json_decode($sort));
                return $query->orderBy($sortBy['key'], $sortBy['order']);
            })->paginate($request->get('limit', 10));

        return new RoleCollection($role);

    }

    public function addPermissionsToRole(Request $request, Role $role)
    {

        $validator = Validator::make($request->only(['permissions']), [
            'permissions' => 'required:array|min:1',
            'permissions.*' => 'required|string|distinct|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()->toArray()], 422);
        }
        DB::beginTransaction();
        try {
            $role->syncPermissions($request->permissions);

            DB::commit();
            return $this->getRolePermissions($role);

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

    private function getRolePermissions(Role $role): \Illuminate\Http\JsonResponse
    {
        $role->load('permissions');
        $active = $role->permissions;
        $permissions = \Spatie\Permission\Models\Permission::all();
        $all = $permissions->groupBy(['parent', function (object $item) {
            return collect($item['children'])->first();
        }], preserveKeys: true);
        return response()->json([
            'data' => [
                'role' => new RoleResource($role),
                'all' => $all,
                'active' => collect($active)->pluck('name'),
            ]

        ], 201);
    }

    public function update(Request $request, Role $role)
    {
        DB::beginTransaction();
        try {
            $role->update($request->only(['name']));
            DB::commit();

            return new RoleResource($role);

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

    public function destroy(Role $role, Request $request)
    {
        DB::beginTransaction();
        try {
            $roles = $request->roles_id;
            if (is_array($roles)) {
                Role::query()
                    ->whereIn('id', $request->roles_id)
                    ->delete();
            } else {
                $role->deleteQuietly();
            }

            DB::commit();

            return response()->json(['status' => true, 'message' => "Role deleted"], 201);

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

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->only(['name']), [
                'name' => 'required|min:3|max:20|unique:roles,name'
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => false, 'errors' => $validator->errors()->toArray()], 422);
            }
            $role = Role::create([
                'name' => $request->name,
                'guard_name' => 'web'
            ]);
            if(collect($request->active)->count() > 0){
                $role->syncPermissions($request->active);

            }
            DB::commit();

            return new RoleResource($role);

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

    public function show(Role $role)
    {
        return $this->getRolePermissions($role);
    }

    public function showDetails(Request $request, Role $role)
    {
        $type = str($request->get('load'))->lower();
        return $type == 'permissions' ?
            new PermissionCollection($role->permissions()
                ->when($request->get('sortBy'), function ($query, $sort) {
                    $sortBy = collect(json_decode($sort));
                    return $query->orderBy($sortBy['key'], $sortBy['order']);
                })
                ->paginate($request->get('limit', 10)))
            :
            new UserCollection(User::role($role->name)->whereNotIn('id', [1, auth()->id()])->when($request->get('sortBy'), function ($query, $sort) {
                $sortBy = collect(json_decode($sort));
                return $query->orderBy($sortBy['key'], $sortBy['order']);
            })->paginate($request->get('limit', 10)));
    }
}
