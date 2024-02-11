<?php

namespace App\Http\Resources\Invoice;

use App\Http\Resources\Data\Customer\CustomerResource;
use App\Http\Resources\Loan\LoanDetailResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoicePrintResource extends JsonResource
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
            'type' => $this->type,
            'invoice_number' => $this->invoice_number,
            'trade_date' => $this->trade_date,
            'loan' => new LoanDetailResource($this->whenLoaded('loan_details')),
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'detail_do' => InvoiceDOPrintResource::collection($this->whenLoaded('detail_do')),
        ];
    }
}
