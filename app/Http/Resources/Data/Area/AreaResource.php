<?php

namespace App\Http\Resources\Data\Area;

use App\Http\Resources\Data\Land\LandResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AreaResource extends JsonResource
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
            'lands' => LandResource::collection($this->whenLoaded('lands')),
            'created_by' => $this->whenLoaded('user', $this->user->name),
            'created_at' => $this->created_at->format('d-m-Y H:i:s'),
        ];
    }
}
