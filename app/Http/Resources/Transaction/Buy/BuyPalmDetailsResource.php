<?php

namespace App\Http\Resources\Transaction\Buy;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BuyPalmDetailsResource extends JsonResource
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
            'customer_id' => $this->whenLoaded('customer', $this->customer->id),
            'customer_name' => $this->whenLoaded('customer', $this->customer->name, ''),
            'created_by' => $this->whenLoaded('user', $this->user->name),
            'trade_date' => $this->whenNotNull($this->trade_date->format('Y/m/d H:i:s')),
            'weight' => $this->weight,
            'price' => $this->price,
            'total' => $this->total,
            'farmer_status' => $this->when($this->farmer_status, $this->farmer_status ? $this->farmer_status->format('Y/m/d H:i:s') : ''),
            'created_at' => $this->whenNotNull($this->created_at->format('Y/m/d H:i:s')),
        ];
    }
}
