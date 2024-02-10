<?php

namespace App\Http\Controllers\Api\Invoice;

use App\Http\Controllers\Controller;
use App\Http\Resources\Data\Customer\CustomerResource;
use App\Http\Resources\Transaction\Delivery\DeliveryResource;
use App\Models\Customer;
use App\Models\DeliveryOrder;
use Illuminate\Http\Request;

class InvoiceDeliveryOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $type = $request->type;

        if ($type) {
            $customers = Customer::query()
                ->withWhereHas('orders', function ($builder) {
                    return $builder->whereNull('invoice_status');
                })
                ->with('loan')
                ->where('type', 'collector')
                ->get();
            $orders = DeliveryOrder::query()
                ->whereNull('invoice_status')
                ->get();
            return response()->json([
                'customers' => CustomerResource::collection($customers),
                'orders' => DeliveryResource::collection($orders),
            ], 201);
        } else {
            abort(403, 'Invalid parameters');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
