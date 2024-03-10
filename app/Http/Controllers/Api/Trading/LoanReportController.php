<?php

namespace App\Http\Controllers\Api\Trading;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Loan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoanReportController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $loans = Loan::query()
            ->with('person')
            ->whereHasMorph('person', [Customer::class], function ($query) {
                $query->where('type',  'farmer');
            })
            ->get()
            ->map(function ($loan) {
                return [
                    'id' => $loan->id,
                    'person_type' => str($loan->person_type)->lower()->split('/\\\\/')->last(),
                    'person_id' => $loan->person_id,
                    'name' => $loan->person?->name,
                    'phone' => $loan->person?->phone,
                    'balance' => $loan->balance,
                ];
            });

        return response()->json([
            'loans' => $loans
        ], 201);
    }
}
