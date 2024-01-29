<?php

namespace App\Http\Controllers\Api\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Resources\Transaction\Buy\BuyPalmDetailsResource;
use App\Models\Trading;
use App\Models\TradingDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TradeBuyDetailsController extends Controller
{

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Trading $trade)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->only([
                'weight', 'price', 'total', 'customer_id'
            ]), [
                'weight' => 'required|numeric|min:1',
                'price' => 'required|numeric|min:1',
                'total' => 'required|numeric|min:1',
                'customer_id' => 'required|exists:customers,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'errors' => $validator->errors()->toArray()], 422);
            }

            $trading = $trade->details()
                ->create([
                    'trade_date' => $trade->trade_date,
                    'weight' => $request->weight,
                    'price' => $request->price,
                    'total' => $request->total,
                    'customer_id' => $request->customer_id,
                    'user_id' => auth()->id()
                ]);

            $detail = $trade->details()->get();

            $trade->update([
                'customer_average_price' => $detail->avg('price'),
                'customer_total_price' => $detail->sum('total'),
                'customer_total_weight' => $detail->sum('weight')
            ]);


            DB::commit();
            return new BuyPalmDetailsResource($trading);

        } catch (\Exception $exception) {
            abort(403, $exception->getCode() . ' ' . $exception->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Trading $trade, TradingDetails $details)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->only([
                'weight', 'price', 'total', 'customer_id'
            ]), [
                'weight' => 'required|numeric|min:1',
                'price' => 'required|numeric|min:1',
                'total' => 'required|numeric|min:1',
                'customer_id' => 'required|exists:customers,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'errors' => $validator->errors()->toArray()], 422);
            }

            $details->update([
                'trade_date' => $trade->trade_date,
                'weight' => $request->weight,
                'price' => $request->price,
                'total' => $request->total,
                'customer_id' => $request->customer_id,
            ]);

            $detail = $trade->details()->get();

            $trade->update([
                'customer_average_price' => $detail->avg('price'),
                'customer_total_price' => $detail->sum('total'),
                'customer_total_weight' => $detail->sum('weight')
            ]);

            DB::commit();

            return response()->json(['status' => true], 201);

        } catch (\Exception $exception) {
            abort(403, $exception->getCode() . ' ' . $exception->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Trading $trade, TradingDetails $details)
    {

        DB::beginTransaction();
        try {

            $details->delete();
            $detail = $trade->details()->get();
            $trade->update([
                'customer_average_price' => $detail->avg('price'),
                'customer_total_price' => $detail->sum('total'),
                'customer_total_weight' => $detail->sum('weight')
            ]);

            DB::commit();
            return response()->json(['status' => true], 201);

        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'error' => [
                    'code' => $exception->getCode(),
                    'massage' => $exception->getMessage()
                ]
            ], 301);
        }
    }
}
