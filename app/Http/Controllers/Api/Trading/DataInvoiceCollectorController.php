<?php

namespace App\Http\Controllers\Api\Trading;

use App\Http\Controllers\Controller;
use App\Http\Resources\Invoice\InvoiceCollection;
use App\Http\Resources\Invoice\InvoicePrintResource;
use App\Models\Invoice;
use Illuminate\Http\Request;

class DataInvoiceCollectorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): InvoiceCollection
    {
        $query = Invoice::query()
            ->with('customer')
            ->withWhereHas('detail_do')
            ->withWhereHas('loan_details');
//            ->with(['loan_details', 'customer', 'detail_do', 'detail_trades']);

        $data = $query->paginate($request->get('limit', 10));

        return new InvoiceCollection($data);
    }


    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        $invoice->load(['customer', 'detail_do', 'loan_details', 'detail_trades']);
        return new InvoicePrintResource($invoice);
    }
}
