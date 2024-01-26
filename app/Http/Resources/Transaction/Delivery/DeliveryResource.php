<?php

namespace App\Http\Resources\Transaction\Delivery;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryResource extends JsonResource
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
            'customer_name' => $this->whenLoaded('customer', $this->customer->name),
            'created_by' => $this->whenLoaded('user', $this->user->name),
            'delivery_date' => $this->whenNotNull($this->delivery_date->format('Y/m/d H:i:s')),
            'net_weight' => $this->net_weight,
            'net_price' => $this->net_price,
            'margin' => $this->margin,
            'gross_total' => $this->gross_total,
            'net_total' => $this->net_total,
            'invoice_status' => $this->whenNotNull($this->invoice_status ? $this->invoice_status->format('Y/m/d H:i:s') : null),
            'income_status' => $this->whenNotNull($this->income_status ? $this->income_status->format('Y/m/d H:i:s') : null),
            'created_at' => $this->whenNotNull($this->created_at->format('Y/m/d H:i:s')),
        ];
    }

    public function with(Request $request)
    {
        return [
            'meta' => $request->all()
        ];
    }
}
