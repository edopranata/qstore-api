<?php

namespace App\Http\Resources\Cost;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CostCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => CostResource::collection($this->collection->all())
        ];
    }
}
