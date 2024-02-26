<?php

namespace App\Http\Controllers\Api\Trading;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\InvoiceTrait;
use App\Http\Resources\Data\Customer\CustomerCollection;
use App\Http\Resources\Data\Customer\CustomerResource;
use App\Http\Resources\Invoice\InvoiceResource;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Loan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FarmerLoanController extends Controller
{
    use InvoiceTrait;

    public function index(Request $request): AnonymousResourceCollection|CustomerCollection
    {
        if($request->has('page') && $request->has('limit')){
            $query = Customer::query()
                ->where('type', 'farmer')
                ->when($request->get('name'), function ($query, $search) {
                    return $query->where('name', 'LIKE', "%$search%");
                })
                ->when($request->get('type'), function ($query, $search) {
                    return $query->where('type', 'LIKE', "%$search%");
                })
                ->when($request->get('phone'), function ($query, $search) {
                    return $query->where('phone', 'LIKE', "%$search%");
                })
                ->when($request->get('address'), function ($query, $search) {
                    return $query->where('address', 'LIKE', "%$search%");
                })
                ->when($request->get('user'), function ($query, $search) {
                    return $query->whereRelation("user", "name", "like", "%$search%");
                })
                ->when($request->get('sortBy'), function ($query, $sort) {
                    $sortBy = collect(json_decode($sort));
                    return $query->orderBy($sortBy['key'], $sortBy['order']);
                });
            $data = $query->paginate($request->get('limit', 10));

            return new CustomerCollection($data);
        }else{

            $customers = Customer::query()
                ->doesntHave('loan')
                ->where('type', 'farmer')->get();
            return CustomerResource::collection($customers);
        }

    }

    public function store(Request $request)
    {
        $now = now();

        $validator = Validator::make($request->only([
            'customer_id',
            'balance',
            'trade_date'
        ]), [
            'customer_id' => 'required|exists:customers,id',
            'balance' => 'required|numeric|min:1',
            'trade_date' => 'required|date|before_or_equal:' . $now->toDateString(),
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()->toArray()], 422);
        }

        $trade_date = Carbon::parse($request->trade_date . ' ' . $now->format('H:i:s'));

        DB::beginTransaction();
        try {
            $customer_type = new Customer();
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
            DB::rollBack();
            abort(403, $exception->getCode() . ' ' . $exception->getMessage());
        }
    }




    private function saveLoanDetails(Loan $loan, array $data): Model
    {
        return $loan->details()
            ->create($data);
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
}
