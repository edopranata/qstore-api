<?php

namespace App\Http\Resources;

use App\Models\Menu;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

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
            'setting' => $this->getSettings(),
            'menu'          => UserMenuResource::collection($this->getMenu($permissions))
        ];

    }

    public function with($request): array
    {
        return [
            'status' => [
                'code' => 201,
                'message' => 'OK',
            ]
        ];
    }

    private function getMenu($permissions): Collection|array
    {
        return Menu::query()
            ->whereNull('menu_id')
            ->with('children', function ($menu) use ($permissions) {
                $menu->whereIn('name', $permissions->pluck('name'));
            })
            ->get();
    }

    private function getSettings(): Collection
    {
        $settings = Setting::all();

        return collect($settings)->mapWithKeys(function ($item, int $key) {
            return [$item['name'] => $item['value']];
        });


    }

}
