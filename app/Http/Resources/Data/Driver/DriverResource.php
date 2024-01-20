<?php

namespace App\Http\Resources\Data\Driver;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverResource extends JsonResource
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
            'name'      => $this->name,
            'phone'     => $this->phone,
            'address'   => $this->address,
            'created_by'    => $this->whenLoaded('user', $this->user->name),
            'created_at'    => $this->created_at->format('d-m-Y H:i:s'),
        ];
    }
}
