<?php

namespace App\Http\Controllers\Api\DeliveryOrder;

use App\Http\Controllers\Controller;
use App\Http\Resources\Data\Customer\CustomerResource;
use App\Http\Resources\Transaction\Delivery\DeliveryCollection;
use App\Http\Resources\Transaction\Delivery\DeliveryResource;
use App\Models\Customer;
use App\Models\DeliveryOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DeliveryOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = DeliveryOrder::query()
            ->when($request->get('sortBy'), function ($query, $sort) {
                $sortBy = collect(json_decode($sort));
                return $query->orderBy($sortBy['key'], $sortBy['order']);
            })->when(!$request->get('sortBy'), function ($query) {
                return $query->orderByDesc('id');
            });

        $deliveries = $query->paginate($request->get('limit', 10));

        $customers = Customer::query()->with('loan')->where('type', 'collector')->get();
        return response()->json([
            'deliveries' => new DeliveryCollection($deliveries),
            'customers' => CustomerResource::collection($customers)
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
                'delivery_date', 'customer_id', 'net_weight', 'net_price', 'margin',
            ]), [
                'delivery_date' => 'required|date|before_or_equal:' . Carbon::now()->toDateString(),
                'customer_id' => 'required|exists:customers,id',
                'net_weight' => 'required|numeric|min:1',
                'net_price' => 'required|numeric|min:1',
                'margin' => 'required|numeric|min:1|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'errors' => $validator->errors()->toArray()], 422);
            }

            $delivery = DeliveryOrder::query()
                ->create([
                    'delivery_date' => Carbon::createFromFormat('Y/m/d H:i:s', $request->delivery_date . ' ' . $now->format('H:i:s')),
                    'customer_id' => $request->customer_id,
                    'customer_type' => get_class(new Customer()),
                    'net_weight' => $request->net_weight,
                    'net_price' => $request->net_price,
                    'gross_total' => $request->gross_total,
                    'net_total' => $request->net_total,
                    'margin' => $request->margin,
                    'user_id' => auth()->id()
                ]);

            DB::commit();
//            return  $delivery->load(['user', 'customer']);
            return new DeliveryResource($delivery->load(['user', 'customer']));

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
    public function update(Request $request, DeliveryOrder $delivery)
    {
        $now = Carbon::now();
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->only([
                'delivery_date', 'customer_id', 'net_weight', 'net_price', 'margin',
            ]), [
                'delivery_date' => 'required|date|before_or_equal:' . Carbon::now()->toDateString(),
                'customer_id' => 'required|exists:customers,id',
                'net_weight' => 'required|numeric|min:1',
                'net_price' => 'required|numeric|min:1',
                'margin' => 'required|numeric|min:1|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'errors' => $validator->errors()->toArray()], 422);
            }

            $delivery->update([
                'delivery_date' => Carbon::createFromFormat('Y/m/d H:i:s', $request->delivery_date . ' ' . $now->format('H:i:s')),
                'customer_id' => $request->customer_id,
                'customer_type' => get_class(new Customer()),
                'net_weight' => $request->net_weight,
                'net_price' => $request->net_price,
                'gross_total' => $request->gross_total,
                'net_total' => $request->net_total,
                'margin' => $request->margin,
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
    public function destroy(DeliveryOrder $delivery): \Illuminate\Http\JsonResponse
    {
        DB::beginTransaction();
        try {

            $delivery->delete();

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
