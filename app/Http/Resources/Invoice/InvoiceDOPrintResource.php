<?php

namespace App\Http\Resources\Invoice;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceDOPrintResource extends JsonResource
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
            'delivery_date' => $this->delivery_date,
            'margin' => $this->margin ?? 0,
            'customer_price' => $this->net_price - $this->margin ?? 0,
            'customer_weight' => $this->net_weight,
            'customer_total' => ($this->net_price - $this->margin ?? 0) * $this->net_weight,
        ];
    }
}
