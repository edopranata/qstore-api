<?php

namespace App\Http\Controllers\Api\Invoice;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\InvoiceTrait;
use App\Http\Resources\Data\Customer\CustomerResource;
use App\Http\Resources\Invoice\InvoiceResource;
use App\Http\Resources\Transaction\Delivery\DeliveryResource;
use App\Models\Customer;
use App\Models\DeliveryOrder;
use App\Models\Invoice;
use App\Models\Loan;
use App\Models\Plantation;
use App\Models\Trading;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InvoiceDeliveryOrderController extends Controller
{
    use InvoiceTrait;

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
        $now = now();
        $delivery_orders = $request->trade_id ? DeliveryOrder::query()->with('customer')->whereIn('id', $request->trade_id)->get() : [];

        $class = get_class(new Customer());

        if ($request->type === 'Plantation') {
            $class = get_class(new Plantation());
        }
        if ($request->type === 'Trading') {
            $class = get_class(new Trading());
        }

        $customer_type = $class;

        $customer_id = $request->type === 'Customer' ? $request->customer_id : collect($delivery_orders)->first()?->customer?->id;
        $net_total = $delivery_orders ? $delivery_orders->sum('net_total') : 0;
        $gross_total = $delivery_orders ? $delivery_orders->sum('gross_total') : 0;
        $customer_total = $gross_total - $net_total;
        $installment = (float)$request->installment;
        $trade_date = Carbon::parse($request->trade_date . ' ' . $now->format('H:i:s'));
        $type = 'DO';
        $sequence = $this->getLastSequence($trade_date, $type);
        $invoice_number = 'MM' . $type . $trade_date->format('Y') . sprintf('%08d', $sequence);

        $max_installment = $customer_total >= $installment ? $installment : $customer_total;

        $validator = Validator::make($request->only([
            'trade_date', 'customer_id', 'installment', 'type'
        ]), [
            'trade_date' => 'required|date|before_or_equal:' . Carbon::now()->toDateString(),
            'customer_id' => 'required_if:type,Customer',
            'installment' => 'required|numeric|max:' . $max_installment,
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()->toArray()], 422);
        }
        if (collect($delivery_orders)->count() < 1) {
            abort(402, 'Transaction empty');
        }
        DB::beginTransaction();
        try {
            // Insert Invoice
            $invoice = Invoice::query()
                ->create([
                    'user_id' => auth()->id(),
                    'trade_date' => $trade_date,
                    'customer_id' => $customer_id,
                    'customer_type' => $customer_type,
                    'to' => str($customer_type)->explode('\\')->last(),
                    'invoice_number' => $invoice_number,
                    'type' => $type,
                    'sequence' => $sequence,
                ]);

            $transaction_type = get_class(new DeliveryOrder());

            foreach ($delivery_orders as $order) {
                // Update invoice status
                $order->update([
                    'invoice_status' => $trade_date
                ]);

                $invoice->details()->create([
                    'delivery_order_id' => $order->id,
                ]);

            }

            if ($installment > 0) {
                // Get Customer Loan
                $loan = Loan::query()
                    ->where('person_id', $customer_id)
                    ->where('person_type', $customer_type)->first();

                if ($loan) {
                    // Add to loan details
                    $details = $loan->details()
                        ->create([
                            'user_id' => auth()->id(),
                            'trade_date' => $trade_date,
                            'opening_balance' => $loan->balance,
                            'balance' => $installment * -1
                        ]);

                    // Update balance in loan table
                    $loan->update([
                        'balance' => $loan->balance - $installment
                    ]);

                    // Create Invoice Loan
                    $invoice->loan()->create([
                        'loan_details_id' => $details->id
                    ]);
                }
            }

            DB::commit();
            return new InvoiceResource($invoice);
        } catch (\Exception $exception) {
            abort(403, $exception->getCode() . ' ' . $exception->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
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
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
