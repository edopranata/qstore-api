<?php

namespace App\Http\Resources\Invoice;

use App\Http\Resources\InvoiceDetailTradeResource;
use App\Http\Resources\Loan\LoanDetailResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
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
            'invoice_number' => $this->invoice_number,
            'trade_date' => $this->trade_date,
            'type' => $this->type,
            'loan_detail' => new LoanDetailResource($this->whenLoaded('loan_details')),
            'detail_do' => InvoiceDOPrintResource::collection( ($this->whenLoaded('detail_do'))),
            'detail_trades' => InvoiceDetailTradeResource::collection( ($this->whenLoaded('detail_trades'))),
            'customer_id' => str($this->customer_type)->endsWith('Customer') ? $this->customer?->id : null,
            'customer_name' => str($this->customer_type)->endsWith('Customer') ? $this->customer?->name : str($this->customer_type)->explode('\\')->last(),
            'customer_phone' => str($this->customer_type)->endsWith('Customer') ? $this->customer?->phone : null,
            'created_by' => $this->whenLoaded('user', $this->user?->name),
            'created_at' => $this->created_at->format('d-m-Y H:i:s'),
        ];
    }
}
