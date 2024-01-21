<?php

namespace App\Http\Resources\Data\Land;

use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class LandCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => LandResource::collection($this->collection->all())
        ];
    }

    public function with(Request $request): array
    {
        return [
            'areas' => Area::query()->get()->map(function ($area) {
                return [
                    'id' => $area->id,
                    'name' => $area->name
                ];
            })
        ];
    }
}
