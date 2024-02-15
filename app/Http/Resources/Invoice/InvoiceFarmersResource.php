<?php

namespace App\Http\Resources\Invoice;

use App\Http\Resources\Transaction\Buy\BuyPalmDetailsResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceFarmersResource extends JsonResource
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
            'type'      => $this->type,
            'name'      => $this->name,
            'phone'     => $this->phone,
            'address'   => $this->address,
            'distance'  => $this->distance,
            'created_by'    => $this->whenLoaded('user', $this->user->name),
            'created_at'    => $this->created_at->format('d-m-Y H:i:s'),
            'trades'    => BuyPalmDetailsResource::collection($this->whenLoaded('trades')),
            'loan' => $this->whenLoaded('loan', $this->loan?->balance),
            'trade_total' => $this->trades ? $this->trades->sum('total') : 0,
            'trade_price' => $this->trades ? $this->trades->avg('price') : 0,
            'trade_weight' => $this->trades ? $this->trades->sum('weight') : 0,
        ];
    }
}
