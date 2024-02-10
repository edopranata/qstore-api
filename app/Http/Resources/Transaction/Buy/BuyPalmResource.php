<?php

namespace App\Http\Resources\Transaction\Buy;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BuyPalmResource extends JsonResource
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
            'driver_id' => $this->whenLoaded('driver', $this->driver->id),
            'driver_name' => $this->whenLoaded('driver', $this->driver->name, ''),
            'car_id' => $this->whenLoaded('car', $this->car->id),
            'car_no_pol' => $this->whenLoaded('car', $this->car->no_pol, ''),
            'details' => $this->whenLoaded('details', $this->driver->name, ''),
            'created_by' => $this->whenLoaded('user', $this->user->name),
            'trade_date' => $this->whenNotNull($this->trade_date->format('Y/m/d H:i:s')),
            'trade_cost' => $this->trade_cost,
            'margin' => $this->margin,
            'net_weight' => $this->net_weight,
            'net_price' => $this->net_price,
            'driver_fee' => $this->driver_fee,
            'car_fee' => $this->car_fee,
            'customer_average_price' => $this->customer_average_price,
            'customer_total_price' => $this->customer_total_price,
            'customer_total_weight' => $this->customer_total_weight,
            'trade_status' => $this->trade_status,
            'created_at' => $this->whenNotNull($this->created_at->format('Y/m/d H:i:s')),
        ];
    }
}
