<?php

namespace App\Http\Controllers;

use App\Http\Resources\Cost\CostCollection;
use App\Http\Resources\Cost\CostResource;
use App\Http\Resources\Data\Land\LandResource;
use App\Models\Cost;
use App\Models\Land;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PlantationCostController extends Controller
{
    protected string $type;

    public function __construct()
    {
        $this->type = 'land';
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse|CostCollection
    {
        if($request->has('page') || $request->has('limit')){
            $query = Cost::query()->with('subject')
                ->whereHasMorph('subject',
                    [Land::class], function (Builder $query) use ($request) {
                        $query->when($request->name, function (Builder $land, $search){
                            $land->where('name', 'like', "%$search%");
                        });
                    })
                ->when($request->description, function (Builder $land, $search){
                    $land->where('description', 'like', "%$search%");
                })
                ->when($request->category, function (Builder $land, $search){
                    $land->where('category', 'like', "%$search%");
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
            $lands = Land::query()->with('area')->get();

            return response()->json([
                'lands' => LandResource::collection($lands)
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
            'category','land_id','trade_date','description','amount',
        ]), [
            'category' => 'required|string',
            'land_id' => 'required|exists:lands,id',
            'trade_date' => 'required|date|before_or_equal:' . $now->toDateString(),
            'description' => 'required|string',
            'amount' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()->toArray()], 422);
        }

        $data['type'] = $this->type;
        $data['trade_date'] = Carbon::createFromFormat('Y/m/d H:i:s', $request->trade_date . ' ' . $now->format('H:i:s'));
        $data['subject_id'] = $request->land_id;
        $data['subject_type'] = get_class(new Land());
        $data['category'] = $request->category;
        $data['description'] = $request->description;
        $data['amount'] = $request->amount;

        DB::beginTransaction();
        try {
            $cost = Cost::query()
                ->create([
                    'type' => $data['type'],
                    'trade_date' => $data['trade_date'],
                    'subject_id' => $data['subject_id'],
                    'subject_type' => $data['subject_type'],
                    'category' => $data['category'],
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
            'category','land_id','trade_date','description','amount',
        ]), [
            'category' => 'required|string',
            'land_id' => 'required|exists:lands,id',
            'trade_date' => 'required|date|before_or_equal:' . $now->toDateString(),
            'description' => 'required|string',
            'amount' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()->toArray()], 422);
        }

        $data['type'] = $this->type;
        $data['trade_date'] = Carbon::createFromFormat('Y/m/d H:i:s', str($request->trade_date)->replace('-', '/') . ' ' . $now->format('H:i:s'));
        $data['subject_id'] = $request->land_id;
        $data['subject_type'] = get_class(new Land());
        $data['category'] = $request->category;
        $data['description'] = $request->description;
        $data['amount'] = $request->amount;

        DB::beginTransaction();
        try {
            $cost->update([
                'type' => $data['type'],
                'trade_date' => $data['trade_date'],
                'subject_id' => $data['subject_id'],
                'subject_type' => $data['subject_type'],
                'category' => $data['category'],
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
