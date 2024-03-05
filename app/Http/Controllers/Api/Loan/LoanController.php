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
use Closure;
use Illuminate\Database\Eloquent\Model;
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
    public function index(Request $request): JsonResponse
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

    public function installmentLoan(Loan $loan, Request $request): InvoiceResource|JsonResponse|null
    {
        $now = now();

        $type = $request->customer_type === 'Driver' ? 'drivers' : 'customers';
        $customer_type = $request->type === 'driver' ? new Driver() : new Customer();
        $trade_date = $request->trade_date ? Carbon::parse($request->trade_date . ' ' . $now->format('H:i:s')) : $now;

        $validator = Validator::make($request->only([
            'customer_id',
            'installment',
        ]), [
            'customer_id' => 'required|exists:' . $type . ',id',
            'installment' => ['required', 'numeric', function (string $attribute, mixed $value, Closure $fail) use ($loan) {
                $ending_balance = $loan->balance + $value;
                $balance = number_format($loan->balance);
                if ($ending_balance < 0) {
                    $fail("The {$attribute} is invalid. loan balance is {$balance}");
                }
            }],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()->toArray()], 422);
        }

        $data['user_id'] = auth()->id();
        $data['opening_balance'] = $loan->balance;
        $data['balance'] = $request->installment;
        $data['trade_date'] = $trade_date ?? now();

        return $this->extracted($loan, $data, $trade_date, $request, $customer_type);
    }

    /**
     * @param Loan $loan
     * @param array $data
     * @param \Illuminate\Support\Carbon|Carbon $trade_date
     * @param Request $request
     * @param Customer|Driver $customer_type
     * @return InvoiceResource|void
     */
    public function extracted(Loan $loan, array $data, \Illuminate\Support\Carbon|Carbon $trade_date, Request $request, Customer|Driver $customer_type)
    {
        DB::beginTransaction();
        try {
            $details = $this->saveLoanDetails($loan, $data);

            $type = 'LN';
            $sequence = $this->getLastSequence($trade_date, $type);
            $invoice_number = 'MM' . $type . $trade_date->format('Y') . sprintf('%08d', $sequence);

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
                'loan_details_id' => $details->id
            ]);

            $loan->update([
                'balance' => $loan->balance + $data['balance'],
            ]);
            DB::commit();

            return new InvoiceResource($invoice);

        } catch (\Exception $exception) {
            DB::rollBack();
            abort(403, $exception->getCode() . ' ' . $exception->getMessage());
        }
    }

    private function saveLoanDetails(Loan $loan, array $data): Model
    {
        return $loan->details()
            ->create($data);
    }

    public function create(Request $request): JsonResponse
    {
        $customers = null;
        if($request->routeIs('app.mobil.pinjamanBaru.index')) {
            $customers = Driver::query()->doesntHave('loan')->select('id', 'name', 'phone', DB::raw('"driver" as type'))->get();
        } else{
            $customers = Customer::query()->doesntHave('loan')->select('id', 'name', 'phone', 'type')->get();

        }
//        $drivers = Driver::query()->doesntHave('loan')->select('id', 'name', 'phone', DB::raw('"driver" as type'))->get();
//        $customers = Customer::query()->doesntHave('loan')->select('id', 'name', 'phone', 'type')->get();
        return response()->json([
            'customers' => $customers,
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    public function addLoan(Loan $loan, Request $request): InvoiceResource|JsonResponse|null
    {
        $now = now();

        $type = $request->customer_type === 'Driver' ? 'drivers' : 'customers';
        $customer_type = $request->type === 'driver' ? new Driver() : new Customer();
        $trade_date = $request->trade_date ? Carbon::parse($request->trade_date . ' ' . $now->format('H:i:s')) : $now;

        $validator = Validator::make($request->only([
            'customer_id',
            'amount',
        ]), [
            'customer_id' => 'required|exists:' . $type . ',id',
            'amount' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()->toArray()], 422);
        }

        $data['user_id'] = auth()->id();
        $data['opening_balance'] = $loan->balance;
        $data['balance'] = $request->amount;
        $data['trade_date'] = $trade_date;

        return $this->extracted($loan, $data, $trade_date, $request, $customer_type);

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
            'type' => 'required|in:farmer,collector,driver',
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
            $data['user_id'] = auth()->id();
            $data['opening_balance'] = 0;
            $data['balance'] = $request->balance;
            $data['trade_date'] = $trade_date;

            $details = $this->saveLoanDetails($loan, $data);

            $type = 'LN';
            $sequence = $this->getLastSequence($details->trade_date, $type);
            $invoice_number = 'MM' . $type . $trade_date->format('Y') . sprintf('%08d', $sequence);

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
                'loan_details_id' => $details->id
            ]);

            DB::commit();

            return new InvoiceResource($invoice);

        } catch (\Exception $exception) {
            abort(403, $exception->getCode() . ' ' . $exception->getMessage());
        }

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
