<?php

namespace App\Http\Resources\Management;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\Permission\Models\Permission;

class MenuResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'title'     => $this->title,
            'path'      => $this->path,
            'icon'      => $this->icon,
            'children'  => MenuResource::collection($this->whenLoaded('children')),
            'permissions'    => $this->menu_id ? $this->permissions($this->name) : []
        ];
    }

    private function permissions($dotString)
    {
        $array = collect(str($dotString)->explode('.'));
        $array->pop();
        $param = $array->implode('.');
        return Permission::query()
            ->where('name', 'like', $param.'%')->get()->map(function ($permission) {
                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'title' => $permission->title,
                    'path' => $permission->path,
                ];
            });
    }
}
