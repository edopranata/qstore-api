<?php

namespace App\Http\Controllers\Api\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Resources\Data\Area\AreaCollection;
use App\Http\Resources\Data\Car\CarResource;
use App\Http\Resources\Data\Driver\DriverResource;
use App\Http\Resources\Transaction\Plantation\PlantationCollection;
use App\Http\Resources\Transaction\Plantation\PlantationResource;
use App\Models\Area;
use App\Models\Car;
use App\Models\Driver;
use App\Models\Land;
use App\Models\Plantation;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PlantationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Plantation::query()
            ->with(['details'])
            ->when($request->get('sortBy'), function ($query, $sort) {
                $sortBy = collect(json_decode($sort));
                return $query->orderBy($sortBy['key'], $sortBy['order']);
            })->when(!$request->get('sortBy'), function ($query) {
                return $query->orderByDesc('id');
            });

        $plantations = $query->paginate($request->get('limit', 10));

        $cars = Car::query()->get();
        $drivers = Driver::query()->get();
        $areas = Area::query()->with('lands')->get();
        return response()->json([
            'areas' => new AreaCollection($areas),
            'plantations' => new PlantationCollection($plantations),
            'cars' => CarResource::collection($cars),
            'drivers' => DriverResource::collection($drivers),
        ], 201);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $now = \Carbon\Carbon::now();
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->only([
                'trade_date', 'trade_cost', 'car_id', 'driver_id', 'land_id', 'net_weight', 'net_price',
            ]), [
                'trade_date' => 'required|date|before_or_equal:' . $now->toDateString(),
                'trade_cost' => 'required|numeric|min:1',
                'net_weight' => 'required|numeric|min:1',
                'net_price' => 'required|numeric|min:1',
                'car_id' => 'required|exists:cars,id',
                'driver_id' => 'required|exists:drivers,id',
                'land_id' => 'required|array',
                'land_id.*' => 'exists:lands,id'
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'errors' => $validator->errors()->toArray()], 422);
            }
            // get Land list
            $lands = Land::query()->whereIn('id', $request->land_id)->get();

            // insert into plantation
            $plantation = Plantation::query()
                ->create([
                    'driver_id' => $request->driver_id,
                    'car_id' => $request->car_id,
                    'user_id' => auth()->id(),
                    'trade_date' => Carbon::createFromFormat('Y/m/d H:i:s', $request->trade_date . ' ' . $now->format('H:i:s')),
                    'trade_cost' => $request->trade_cost,
                    'net_weight' => $request->net_weight,
                    'net_price' => $request->net_price,
                ]);

            // set plantation details (list land)
            foreach ($lands as $land) {
                $plantation->details()
                    ->create([
                        'user_id' => $plantation->user_id,
                        'land_id' => $land->id,
                        'wide' => $land->wide,
                        'trees' => $land->trees
                    ]);
            }
            $details = $plantation->details();

            // update plantation wide_total and trees total
            $plantation->update([
                'wide_total' => $details->sum('wide'),
                'trees_total' => $details->sum('trees'),
                'net_total' => $plantation->net_price * $plantation->net_weight,
            ]);

            // insert into delivery order (net price + margin [40])
            $margin = 40;
            $net_price = $plantation->net_price + $margin;

            $plantation->order()
                ->create([
                    'user_id' => $plantation->user_id,
                    'delivery_date' => $plantation->trade_date,
                    'net_weight' => $plantation->net_weight,
                    'net_price' => $net_price,
                    'margin' => $margin,
                    'gross_total' => $net_price * $plantation->net_weight,
                    'net_total' => $margin * $plantation->net_weight,
                    'invoice_status' => now(),
                ]);


            DB::commit();
            return new PlantationResource($plantation);

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
     * Update the specified resource in storage.
     */
    public function update(Request $request, Plantation $plantation)
    {
        $now = \Carbon\Carbon::now();
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->only([
                'trade_date', 'trade_cost', 'car_id', 'driver_id', 'land_id', 'net_weight', 'net_price',
            ]), [
                'trade_date' => 'required|date|before_or_equal:' . $now->toDateString(),
                'trade_cost' => 'required|numeric|min:1',
                'net_weight' => 'required|numeric|min:1',
                'net_price' => 'required|numeric|min:1',
                'car_id' => 'required|exists:cars,id',
                'driver_id' => 'required|exists:drivers,id',
                'land_id' => 'required|array',
                'land_id.*' => 'exists:lands,id'
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'errors' => $validator->errors()->toArray()], 422);
            }
            // get Land list
            $lands = Land::query()->whereIn('id', $request->land_id)->get();

            // insert into plantation
            $plantation->update([
                'driver_id' => $request->driver_id,
                'car_id' => $request->car_id,
                'user_id' => auth()->id(),
                'trade_date' => Carbon::createFromFormat('Y/m/d H:i:s', $request->trade_date . ' ' . $now->format('H:i:s')),
                'trade_cost' => $request->trade_cost,
                'net_weight' => $request->net_weight,
                'net_price' => $request->net_price,
            ]);
            // delete old details (skip soft delete)
            $plantation->details()->forceDelete();

            // set plantation details (list land)
            foreach ($lands as $land) {
                $plantation->details()
                    ->create([
                        'user_id' => $plantation->user_id,
                        'land_id' => $land->id,
                        'wide' => $land->wide,
                        'trees' => $land->trees
                    ]);
            }
            $details = $plantation->details();

            // update plantation wide_total and trees total
            $plantation->update([
                'wide_total' => $details->sum('wide'),
                'trees_total' => $details->sum('trees'),
                'net_total' => $plantation->net_price * $plantation->net_weight,
            ]);

            // insert into delivery order (net price + margin [40])
            $margin = 40;
            $net_price = $plantation->net_price + $margin;

            $plantation->order()
                ->update([
                    'user_id' => $plantation->user_id,
                    'delivery_date' => $plantation->trade_date,
                    'net_weight' => $plantation->net_weight,
                    'net_price' => $net_price,
                    'margin' => $margin,
                    'gross_total' => $net_price * $plantation->net_weight,
                    'net_total' => $margin * $plantation->net_weight,
                ]);

            DB::commit();
            return new PlantationResource($plantation);

        } catch (\Exception $exception) {
            abort(403, $exception->getCode() . ' ' . $exception->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Plantation $plantation)
    {
        DB::beginTransaction();
        try {

            $plantation->delete();
            $plantation->details()->delete();
            $plantation->order()->delete();

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
