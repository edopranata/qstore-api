<?php

namespace App\Http\Controllers\Api\Car;

use App\Http\Controllers\Controller;
use App\Http\Resources\Data\Car\CarCollection;
use App\Http\Resources\Data\Car\CarResource;
use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Car::query()
            ->when($request->get('name'), function ($query, $search) {
                return $query->where('name', 'LIKE', "%$search%");
            })
            ->when($request->get('no_pol'), function ($query, $search) {
                return $query->where('no_pol', 'LIKE', "%$search%");
            })
            ->when($request->get('status'), function ($query, $search) {
                return $query->where('status', $search);
            })
            ->when($request->get('year'), function ($query, $search) {
                return $query->where('year', 'LIKE', "%$search%");
            })
            ->when($request->get('user'), function ($query, $search) {
                return $query->whereRelation("user", "name", "like", "%$search%");
            })
            ->when($request->get('sortBy'), function ($query, $sort) {
                $sortBy = collect(json_decode($sort));
                return $query->orderBy($sortBy['key'], $sortBy['order']);
            });

        $data = $request->get('limit', 0) > 0 ? $query->paginate($request->get('limit', 10)) : $query->get();

        return new CarCollection($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->only([
                'status',
                'name',
                'no_pol',
                'description',
                'year',
            ]), [
                'status' => 'required|string|in:yes,no',
                'name' => 'required|string|min:3|max:30',
                'no_pol' => 'required|string|max:11|unique:cars,no_pol',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'errors' => $validator->errors()->toArray()], 422);
            }

            $car = Car::query()
                ->create([
                    'status' => $request->status,
                    'name' => $request->name,
                    'no_pol' => $request->no_pol,
                    'description' => $request->description,
                    'year' => $request->year,
                    'user_id' => auth()->id()
                ]);

            DB::commit();

            return new CarResource($car->load('user'));

        } catch (\Exception $exception) {
            abort(403, $exception->getCode() . ' ' . $exception->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Car $car)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Car $car)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->only([
                'name',
                'no_pol',
                'description',
                'year',
                'status',
            ]), [
                'status' => 'required|string|in:yes,no',
                'name' => 'required|string|min:3|max:30',
                'no_pol' => 'required|string|max:11|unique:cars,no_pol,' . $request->id,
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'errors' => $validator->errors()->toArray()], 422);
            }

            $car->update([
                'status' => $request->status,
                'name' => $request->name,
                'no_pol' => $request->no_pol,
                'description' => $request->description,
                'year' => $request->year,
                'user_id' => auth()->id()
            ]);

            DB::commit();

            return new CarResource($car->load('user'));

        } catch (\Exception $exception) {
            abort(403, $exception->getCode() . ' ' . $exception->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Car $car, Request $request)
    {
        DB::beginTransaction();
        try {
            if ($car->id === '1') {
                throw new \Exception('Invalid user', '301');
            }
            $cars = $request->car_id;
            if (is_array($cars)) {
                Car::query()
                    ->whereIn('id', $request->car_id)->delete();
            } else {
                $car->delete();
            }

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
