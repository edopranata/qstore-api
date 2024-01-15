<?php

namespace App\Http\Resources;

use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $permissions = $this->getAllPermissions();

        return [
            'user'      => [
                'id'            => $this->id,
                'name'          => $this->name,
                'username'      => $this->username,
                'email'         => $this->email,
                'photo_url'     => $this->photo_url ?? asset('/avatar.svg'),
            ],
            'routes'        => $permissions->pluck('name'),
            'menu'          => UserMenuResource::collection($this->getMenu($permissions))
        ];

    }

    public function with($request)
    {
        return [
            'status' => [
                'code' => 201,
                'message' => 'OK',
            ]
        ];
    }

    private function getMenu($permissions)
    {
        return Menu::query()
            ->whereNull('menu_id')
            ->with('children', function ($menu) use ($permissions) {
                $menu->whereIn('name', $permissions->pluck('name'));
            })
            ->get();
    }

}
