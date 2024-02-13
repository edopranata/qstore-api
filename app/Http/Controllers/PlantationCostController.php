<?php

namespace App\Http\Controllers;

use App\Http\Resources\Cost\CostCollection;
use App\Http\Resources\Data\Land\LandResource;
use App\Models\Cost;
use App\Models\Land;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
            $query = Cost::query()->with('subject.area')
                ->whereHasMorph('subject',
                    [Land::class]);

            $data = $query->paginate($request->get('limit', 10));

            return new CostCollection($data);
        }else{
            $cars = Land::query()
                ->get();

            return response()->json([
                'lands' => LandResource::collection($cars)
            ],201);
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
