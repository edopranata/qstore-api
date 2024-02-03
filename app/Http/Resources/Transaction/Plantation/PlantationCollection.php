<?php

namespace App\Http\Resources\Transaction\Plantation;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PlantationCollection extends ResourceCollection
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => PlantationResource::collection($this->collection->all()),
            'meta' => [
                'total' => collect($this->resource)['total']
            ]
        ];
    }
}
