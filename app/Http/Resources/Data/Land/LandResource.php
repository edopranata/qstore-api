<?php

namespace App\Http\Resources\Data\Land;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LandResource extends JsonResource
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
            'name' => $this->name,
            'wide' => $this->wide,
            'trees' => $this->trees,
            'area_id' => $this->whenLoaded('area', $this->area?->id),
            'area' => $this->whenLoaded('area', $this->area?->name),
            'created_by' => $this->whenLoaded('user', $this->user?->name),
            'created_at' => $this->created_at->format('d-m-Y H:i:s'),
        ];
    }
}
