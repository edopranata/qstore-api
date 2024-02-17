<?php

namespace App\Http\Resources\Transaction\Plantation;

use App\Http\Resources\Data\Land\LandResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlantationDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'plantation_id' => $this->plantation_id,
            'land_id' => $this->land_id,
            'user_id' => $this->user_id,
            'wide' => $this->wide,
            'trees' => $this->trees,
            'land' => new LandResource($this->whenLoaded('land')),
            'plantation' => new PlantationResource($this->whenLoaded('plantation'))
        ];
    }
}
