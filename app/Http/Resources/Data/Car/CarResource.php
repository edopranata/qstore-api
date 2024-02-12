<?php

namespace App\Http\Resources\Data\Car;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'status'    => $this->status,
            'name'      => $this->name,
            'no_pol'    => $this->no_pol,
            'year'      => $this->year,
            'description'   => $this->description,
            'created_by'    => $this->whenLoaded('user', $this->user->name),
            'created_at'    => $this->created_at->format('d-m-Y H:i:s'),
        ];
    }
}
