<?php

namespace App\Http\Resources\Management;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Spatie\Permission\Models\Role;

class UserCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data'  => UserResource::collection($this->collection->all())
        ];
    }

    public function with($request)
    {
        if(!$request->routeIs('app.management.users.index')) return [];
        $roles = collect([Role::query()->whereNot('id', 1)->get()->pluck('name')])->collapse();
        return [
            'roles' => $roles->all(),
            'abilities' => [
                'canCreate' => $request->user()->can('app.management.users.createUser'),
                'canEdit' => $request->user()->can('app.management.users.updateUser'),
                'canDelete' => $request->user()->can('app.management.users.deleteUser'),
                'canReset' => $request->user()->can('app.management.users.resetPassword'),
            ]
        ];
    }
}
