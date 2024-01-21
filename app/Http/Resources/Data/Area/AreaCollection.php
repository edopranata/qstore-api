<?php

namespace App\Http\Resources\Data\Area;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class AreaCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => AreaResource::collection($this->collection->all())
        ];
    }
}
