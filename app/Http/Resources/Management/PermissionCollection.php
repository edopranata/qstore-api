<?php

namespace App\Http\Resources\Management;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PermissionCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => PermissionResource::collection($this->collection->all()),
        ];

    }
    public function with($request)
    {
        return [
            'abilities' => [
                'canSync' => $request->user()->can('app.management.permissions.syncPermissions'),
                'canSetToUser' => $request->user()->can('app.management.permissions.setPermissionToUser'),
                'canSetToRole' => $request->user()->can('app.management.permissions.setPermissionToRole'),
                'canViewPermission' => $request->user()->can('app.management.permissions.viewPermission'),
            ]
        ];
    }
}
