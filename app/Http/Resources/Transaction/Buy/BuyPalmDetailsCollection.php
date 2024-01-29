<?php

namespace App\Http\Resources\Transaction\Buy;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class BuyPalmDetailsCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => BuyPalmDetailsResource::collection($this->collection->all()),
            'meta' => [
                'total' => collect($this->resource)['total']
            ]
        ];
    }
}
