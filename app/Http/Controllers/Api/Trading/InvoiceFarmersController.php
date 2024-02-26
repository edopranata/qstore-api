<?php

namespace App\Http\Controllers\Api\Trading;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\InvoiceTrait;
use App\Http\Resources\Data\Customer\CustomerResource;
use App\Http\Resources\Invoice\InvoiceFarmersCollection;
use App\Http\Resources\Invoice\InvoiceResource;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\TradingDetails;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InvoiceFarmersController extends Controller
{
    use InvoiceTrait;
    public function index(Request $request): InvoiceFarmersCollection
    {
        $farmers = Customer::query()
            ->withWhereHas('trades', function ($builder) use ($request){
                return $builder->whereNull('farmer_status');
            })
            ->where('type', 'farmer')
            ->get();

        return new InvoiceFarmersCollection($farmers);
    }

    public function show(Customer $customer): JsonResponse
    {
        $customer = $customer->load('loan');

        $tradings = $customer->trades()
            ->with(['car', 'driver'])
            ->whereNull('farmer_status')->get();


        return response()->json([
            'customer' => new CustomerResource($customer),
            'details' => $tradings
        ], 201);
    }

    public function store(Request $request, Customer $customer)
    {
        $now = now();
        $trade_details = $request->trade_details_id ? TradingDetails::query()->with('customer')->whereIn('id', $request->trade_details_id)->get() : [];

        $class = get_class(new Customer());

        $customer_type = $class;

        $customer_id = $customer->id;
        $customer_total = $trade_details ? $trade_details->sum('total') : 0;
        $installment = $request->installment ? (float)$request->installment : 0;
        $trade_date = Carbon::parse($request->trade_date . ' ' . $now->format('H:i:s'));
        $type = 'TR';
        $sequence = $this->getLastSequence($trade_date, $type);
        $invoice_number = 'MM' . $type . $trade_date->format('Y') . sprintf('%08d', $sequence);

        $max_installment = $customer_total >= $installment ? $installment : $customer_total;

        $validator = Validator::make($request->only([
            'trade_date', 'installment', 'type'
        ]), [
            'trade_date' => 'required|date|before_or_equal:' . Carbon::now()->toDateString(),
            'installment' => 'required|numeric|max:' . $max_installment,
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()->toArray()], 422);
        }
        if (collect($trade_details)->count() < 1) {
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
                    'invoice_number' => $invoice_number,
                    'type' => $type,
                    'sequence' => $sequence,
                ]);

            foreach ($trade_details as $trade_detail) {
                // Update invoice status
                $trade_detail->update([
                    'farmer_status' => $trade_date
                ]);

                $invoice->details()->create([
                    'trading_details_id' => $trade_detail->id,
                ]);

            }

            if ($installment > 0) {
                // Get Customer Loan
                $loan = $customer->loan()->first();

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
            DB::rollBack();
            abort(403, $exception->getCode() . ' ' . $exception->getMessage());
        }
    }
}
