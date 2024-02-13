<?php

namespace App\Http\Controllers;

use App\Http\Resources\Cost\CostCollection;
use App\Http\Resources\Data\Car\CarResource;
use App\Models\Car;
use App\Models\Cost;
use Illuminate\Http\Request;

class CarCostController extends Controller
{

    protected string $type;


    public function __construct()
    {
        $this->type = 'car';
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        if($request->has('page') || $request->has('limit')){
            $query = Cost::query()->with('subject')
                ->whereHasMorph('subject',
                    [Car::class]);

            $data = $query->paginate($request->get('limit', 10));

            return new CostCollection($data);
        }else{
            $cars = Car::query()
                ->where('status', 'yes')->get();

            return response()->json([
                'cars' => CarResource::collection($cars)
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
