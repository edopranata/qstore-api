<?php

namespace App\Http\Resources\Cost;

use App\Http\Resources\Data\Car\CarResource;
use App\Http\Resources\Data\Land\LandResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CostResource extends JsonResource
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
            'trade_date' => $this->trade_date ? $this->trade_date->format('Y/m/d H:i:s') : '',
            'type' => $this->type,
            'updated_at' => $this->updated_at ? $this->updated_at->format('Y/m/d H:i:s') : '',
            'created_at' => $this->created_at ? $this->created_at->format('Y/m/d H:i:s') : '',
            'description' => $this->description,
            'category' => $this->category,
            'amount' => $this->amount,
            'subject' => str($this->subject_type)->endsWith('Car') ? new CarResource($this->whenLoaded('subject')) : new LandResource($this->whenLoaded('subject')),
            'subject_id' => $this->subject_id
        ];
    }
}
