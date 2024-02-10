<?php

namespace App\Http\Controllers\Api\Loan;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Loan;
use App\Models\LoanDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoanController extends Controller
{
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
                    ->with(['transaction'])
                    ->where('loan_id', $loan->id)
                    ->orderBy('id');

                $details = $query->paginate($request->get('limit', 10));
            }


            return response()->json([
                'loan' => $loan,
                'details' => $details ?? null
            ], 201);
        } else {
            $car = Driver::query()->withWhereHas('loan')->select('id', 'name', 'phone', DB::raw('"driver" as type'))->get();
            $customer = Customer::query()->withWhereHas('loan')->select('id', 'name', 'phone', 'type')->get();
            return response()->json([
                'customers' => $car->union($customer),
            ], 201);
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
