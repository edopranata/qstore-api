<?php

namespace App\Http\Resources\Transaction\Plantation;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlantationResource extends JsonResource
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
            'created_by' => $this->whenLoaded('user', $this->user->name, ''),
            'driver_id' => $this->whenLoaded('driver', $this->driver->id),
            'driver_name' => $this->whenLoaded('driver', $this->driver->name, ''),
            'car_id' => $this->whenLoaded('car', $this->car->id),
            'car_no_pol' => $this->whenLoaded('car', $this->car->no_pol, ''),
            'trade_date' => $this->whenNotNull($this->trade_date->format('Y/m/d H:i:s')),
            'trade_cost' => $this->trade_cost,
            'net_weight' => $this->net_weight,
            'net_price' => $this->net_price,
            'net_total' => $this->net_total,
            'net_income' => $this->net_income,
            'driver_fee' => $this->driver_fee,
            'car_fee' => $this->car_fee,
            'car_transport' => $this->car_transport,
            'loader_fee' => $this->loader_fee,
            'wide_total' => $this->wide_total,
            'trees_total' => $this->trees_total,
            'gross_total' => $this->gross_total,
            'created_at' => $this->whenNotNull($this->created_at->format('Y/m/d H:i:s')),
            'invoice' => $this->invoice_status?->format('Y/m/d H:i:s'),
            'details' => PlantationDetailResource::collection($this->whenLoaded('details')),
        ];
    }
}
