<?php

namespace App\Http\Resources\Data\Customer;

use App\Http\Resources\Transaction\Delivery\DeliveryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
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
            'loan' => $this->whenLoaded('loan', $this->loan?->balance),
            'created_by'    => $this->whenLoaded('user', $this->user->name),
            'created_at'    => $this->created_at->format('d-m-Y H:i:s'),
            'orders' => DeliveryResource::collection($this->whenLoaded('orders'))
        ];
    }
}
