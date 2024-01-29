<?php

namespace App\Http\Controllers\Api\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Resources\Data\Car\CarResource;
use App\Http\Resources\Data\Driver\DriverResource;
use App\Http\Resources\Transaction\Buy\BuyPalmCollection;
use App\Http\Resources\Transaction\Buy\BuyPalmDetailsCollection;
use App\Http\Resources\Transaction\Buy\BuyPalmResource;
use App\Models\Car;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Trading;
use App\Models\TradingDetails;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TradeBuyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = Trading::query()
            ->when($request->get('sortBy'), function ($query, $sort) {
                $sortBy = collect(json_decode($sort));
                return $query->orderBy($sortBy['key'], $sortBy['order']);
            })->when(!$request->get('sortBy'), function ($query) {
                return $query->orderByDesc('id');
            });

        $tradings = $query->paginate($request->get('limit', 10));

        $cars = Car::query()->get();
        $drivers = Driver::query()->get();
        return response()->json([
            'tradings' => new BuyPalmCollection($tradings),
            'cars' => CarResource::collection($cars),
            'drivers' => DriverResource::collection($drivers),
        ], 201);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $now = Carbon::now();
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->only([
                'trade_date', 'trade_cost', 'car_id', 'driver_id',
            ]), [
                'trade_date' => 'required|date|before_or_equal:' . $now->toDateString(),
                'trade_cost' => 'required|numeric|min:1',
                'car_id' => 'required|exists:cars,id',
                'driver_id' => 'required|exists:cars,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'errors' => $validator->errors()->toArray()], 422);
            }

            $trading = Trading::query()
                ->create([
                    'trade_date' => Carbon::createFromFormat('Y/m/d H:i:s', $request->trade_date . ' ' . $now->format('H:i:s')),
                    'car_id' => $request->car_id,
                    'driver_id' => $request->driver_id,
                    'trade_cost' => $request->trade_cost,
                    'user_id' => auth()->id()
                ]);

            DB::commit();
            return new BuyPalmResource($trading);

        } catch (\Exception $exception) {
            abort(403, $exception->getCode() . ' ' . $exception->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Trading $trade)
    {
//        if ($request->has('page')) {
        $query = TradingDetails::query()
            ->where('trading_id', $trade->id)
            ->when($request->get('sortBy'), function ($query, $sort) {
                $sortBy = collect(json_decode($sort));
                return $query->orderBy($sortBy['key'], $sortBy['order']);
            })->when(!$request->get('sortBy'), function ($query) {
                return $query->orderByDesc('id');
            });

        $details = $query->paginate($request->get('limit', 10));
//            return new BuyPalmDetailsCollection($details);
//        } else {
        $customers = Customer::query()
            ->where('type', 'farmers')->get();

        return response()->json([
            'trading' => new BuyPalmResource($trade),
            'customers' => DriverResource::collection($customers),
            'details' => new BuyPalmDetailsCollection($details),
        ], 201);
//        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Trading $trade)
    {
        $now = Carbon::now();
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->only([
                'trade_date', 'trade_cost', 'car_id', 'driver_id',
            ]), [
                'trade_date' => 'required|date|before_or_equal:' . $now->toDateString(),
                'trade_cost' => 'required|numeric|min:1',
                'car_id' => 'required|exists:cars,id',
                'driver_id' => 'required|exists:cars,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'errors' => $validator->errors()->toArray()], 422);
            }

            $trade->update([
                'trade_date' => Carbon::createFromFormat('Y/m/d H:i:s', $request->trade_date . ' ' . $now->format('H:i:s')),
                'car_id' => $request->car_id,
                'driver_id' => $request->driver_id,
                'trade_cost' => $request->trade_cost,
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
    public function destroy(Trading $trade)
    {
        DB::beginTransaction();
        try {

            $trade->delete();
            $trade->details()->delete();

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
