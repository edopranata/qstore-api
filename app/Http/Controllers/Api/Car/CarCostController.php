<?php

namespace App\Http\Controllers\Api\Car;

use App\Http\Controllers\Controller;
use App\Http\Resources\Cost\CostCollection;
use App\Http\Resources\Cost\CostResource;
use App\Http\Resources\Data\Car\CarResource;
use App\Models\Car;
use App\Models\Cost;
use App\Models\CostType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CarCostController extends Controller
{

    private string $type = 'car';
    private string $status = 'yes';

    public function index(Request $request)
    {
        if($request->has('page') || $request->has('limit')){
            $query = Cost::query()->with(['subject', 'cost_type'])
                ->whereHasMorph('subject',
                    [Car::class], function (Builder $query) use ($request) {
                        $query->when($request->no_pol, function (Builder $car, $search){
                            $car->where('no_pol', 'like', "%$search%");
                        });
                    })
                ->when($request->description, function (Builder $car, $search){
                    $car->where('description', 'like', "%$search%");
                })
                ->when($request->cost_type_id, function (Builder $car, $search){
                    $car->where('cost_type_id', $search);
                })
                ->when($request->get('sortBy'), function ($query, $sort) {
                    $sortBy = collect(json_decode($sort));
                    return $query->orderBy($sortBy['key'], $sortBy['order']);
                })->when(!$request->get('sortBy'), function ($query) {
                    return $query->orderByDesc('id');
                });;

            $data = $query->paginate($request->get('limit', 10));

            return new CostCollection($data);
        }else{
            $cars = Car::query()
                ->where('status', $this->status)->get();
            $costs = CostType::query()
                ->where('type', $this->type)
                ->get();

            return response()->json([
                'cars' => CarResource::collection($cars),
                'cost_type' => $costs
            ],201);
        }

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $now = now();
        $validator = Validator::make($request->only([
            'cost_type_id','car_id','trade_date','description','amount',
        ]), [
            'cost_type_id' => 'required|exists:cost_types,id',
            'car_id' => 'required|exists:cars,id',
            'trade_date' => 'required|date|before_or_equal:' . $now->toDateString(),
            'description' => 'required|string',
            'amount' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()->toArray()], 422);
        }

        $data['type'] = $this->type;
        $data['trade_date'] = Carbon::createFromFormat('Y/m/d H:i:s', $request->trade_date . ' ' . $now->format('H:i:s'));
        $data['subject_id'] = $request->car_id;
        $data['subject_type'] = get_class(new Car());
        $data['cost_type_id'] = $request->cost_type_id;
        $data['description'] = $request->description;
        $data['amount'] = $request->amount;

        DB::beginTransaction();
        try {
            $cost = Cost::query()
                ->create([
                    'trade_date' => $data['trade_date'],
                    'subject_id' => $data['subject_id'],
                    'subject_type' => $data['subject_type'],
                    'cost_type_id' => $data['cost_type_id'],
                    'description' => $data['description'],
                    'amount' => $data['amount'],
                    'user_id' => auth()->id()
                ]);

            DB::commit();
            return new CostResource($cost);

        } catch (\Exception $exception) {
            DB::rollBack();
            abort(403, $exception->getCode() . ' ' . $exception->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cost $cost)
    {
        $now = now();
        $validator = Validator::make($request->only([
            'cost_type_id','car_id','trade_date','description','amount',
        ]), [
            'cost_type_id' => 'required|exists:cost_types,id',
            'car_id' => 'required|exists:cars,id',
            'trade_date' => 'required|date|before_or_equal:' . $now->toDateString(),
            'description' => 'required|string',
            'amount' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()->toArray()], 422);
        }

        $data['trade_date'] = Carbon::createFromFormat('Y/m/d H:i:s', str($request->trade_date)->replace('-', '/') . ' ' . $now->format('H:i:s'));
        $data['subject_id'] = $request->car_id;
        $data['subject_type'] = get_class(new Car());
        $data['cost_type_id'] = $request->cost_type_id;
        $data['description'] = $request->description;
        $data['amount'] = $request->amount;

        DB::beginTransaction();
        try {
            $cost->update([
                'trade_date' => $data['trade_date'],
                'subject_id' => $data['subject_id'],
                'subject_type' => $data['subject_type'],
                'cost_type_id' => $data['cost_type_id'],
                'description' => $data['description'],
                'amount' => $data['amount'],
                'user_id' => auth()->id()
            ]);

            DB::commit();
            return new CostResource($cost);

        } catch (\Exception $exception) {
            DB::rollBack();
            abort(403, $exception->getCode() . ' ' . $exception->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cost $cost)
    {
        DB::beginTransaction();
        try {

            $cost->delete();
            DB::commit();
            return response()->json(['status' => true], 201);
        } catch (\Exception $exception) {
            DB::rollBack();
            abort(403, $exception->getCode() . ' ' . $exception->getMessage());
        }
    }
}
