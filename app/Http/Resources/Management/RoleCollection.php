<?php

namespace App\Http\Resources\Management;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Spatie\Permission\Models\Permission;

class RoleCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => RoleResource::collection($this->collection->all())
        ];
    }

    public function with($request)
    {
        return [
            'permissions' => $this->when(
                $request->routeIs('app.management.roles.index'),
                Permission::all()->groupBy(['parent', function (object $item) {
                    return collect($item['children'])->first();
                }], preserveKeys: true)
            )
        ];
    }
}
