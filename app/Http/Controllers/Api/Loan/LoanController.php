<?php

namespace App\Http\Controllers\Api\Loan;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\InvoiceTrait;
use App\Http\Resources\Invoice\InvoiceResource;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Invoice;
use App\Models\Loan;
use App\Models\LoanDetails;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LoanController extends Controller
{
    use InvoiceTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->has('details') && $request->has('customer_id') && $request->has('type')) {
            $person = $request->type === 'driver' ? get_class(new Driver()) : get_class(new Customer());
            $loan = Loan::query()
                ->with(['person'])
                ->where('person_type', $person)
                ->where('person_id', $request->customer_id)
                ->first();

            if ($loan) {
                $query = LoanDetails::query()
                    ->with('invoice')
                    ->where('loan_id', $loan->id)
                    ->orderByDesc('id');

                $details = $query->paginate($request->get('limit', 10));
            }


            return response()->json([
                'loan' => $loan,
                'details' => $details ?? null
            ], 201);
        } else {
            $drivers = Driver::query()->withWhereHas('loan')->select('id', 'name', 'phone', DB::raw('"driver" as type'))->get();
            $customers = Customer::query()->withWhereHas('loan')->select('id', 'name', 'phone', 'type')->get();
            return response()->json([
                'customers' => $drivers->merge($customers),
            ], 201);
        }

    }

    public function printInvoice(Invoice $invoice)
    {
        return $invoice;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $type = $request->type === 'driver' ? 'drivers' : 'customers';

        $validator = Validator::make($request->only([
            'type',
            'customer_id',
            'balance',
            'trade_date'
        ]), [
            'type' => 'required|in:farmers,collector,driver',
            'customer_id' => 'required|exists:' . $type . ',id',
            'balance' => 'required|numeric|min:1',
            'trade_date' => 'required|date|before_or_equal:' . Carbon::now()->toDateString(),
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()->toArray()], 422);
        }
        // Set trade date to current time
        $now = now();
        $trade_date = Carbon::parse($request->trade_date . ' ' . $now->format('H:i:s'));

        DB::beginTransaction();
        try {
            $customer_type = $request->type === 'driver' ? new Driver() : new Customer();
            // Create new Loan
            $loan = Loan::query()
                ->create([
                    'person_id' => $request->customer_id,
                    'person_type' => get_class($customer_type),
                    'balance' => $request->balance,
                    'user_id' => auth()->id()
                ]);

            // Create Loan Details
            $details = $loan->details()
                ->create([
                    'user_id' => auth()->id(),
                    'trade_date' => $trade_date,
                    'opening_balance' => 0,
                    'balance' => $request->balance
                ]);

            $type = 'LN';
            $sequence = $this->getLastSequence($details->trade_date, $type);
            $invoice_number = 'MM'.$type. $trade_date->format('Y') . sprintf('%08d', $sequence);

            // Create Invoice
            $invoice = Invoice::query()
                ->create([
                    'user_id' => auth()->id(),
                    'trade_date' => $trade_date,
                    'customer_id' => $request->customer_id,
                    'customer_type' => get_class($customer_type),
                    'invoice_number' => $invoice_number,
                    'type' => $type,
                    'sequence' => $sequence,
                ]);

            // Create Invoice Loan
            $invoice->loan()->create([
                'loan_details_id'    => $details->id
            ]);

            DB::commit();

            return new InvoiceResource($invoice);

        } catch (\Exception $exception) {
            abort(403, $exception->getCode() . ' ' . $exception->getMessage());
        }

    }

    public function create(Request $request): JsonResponse
    {
//        $drivers = DB::table('drivers')->select('id', 'name', 'phone', DB::raw('"driver" as type'))->
        $drivers = Driver::query()->doesntHave('loan')->select('id', 'name', 'phone', DB::raw('"driver" as type'))->get();
        $customers = Customer::query()->doesntHave('loan')->select('id', 'name', 'phone', 'type')->get();
        return response()->json([
            'customers' => $drivers->merge($customers),
        ], 201);
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
