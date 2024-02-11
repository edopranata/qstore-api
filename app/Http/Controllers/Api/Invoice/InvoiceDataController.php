<?php

namespace App\Http\Controllers\Api\Invoice;

use App\Http\Controllers\Controller;
use App\Http\Resources\Invoice\InvoiceCollection;
use App\Http\Resources\Invoice\InvoicePrintResource;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceDataController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): InvoiceCollection
    {
        $query = Invoice::query()->with(['loan_details', 'customer', 'detail_do']);

        $data = $query->paginate($request->get('limit', 10));

        return new InvoiceCollection($data);
    }



    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        $invoice->load(['customer', 'detail_do', 'loan_details']);
        return new InvoicePrintResource($invoice);
    }

}
