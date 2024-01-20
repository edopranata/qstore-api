<?php

namespace App\Http\Controllers\Api\Data;

use App\Http\Controllers\Controller;
use App\Http\Resources\Data\Driver\DriverCollection;
use App\Http\Resources\Data\Driver\DriverResource;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DriverController extends Controller
{
    public function index(Request $request)
    {
        $query = Driver::query()
            ->when($request->get('name'), function ($query, $search) {
                return $query->where('name', 'LIKE', "%$search%");
            })
            ->when($request->get('phone'), function ($query, $search) {
                return $query->where('phone', 'LIKE', "%$search%");
            })
            ->when($request->get('address'), function ($query, $search) {
                return $query->where('address', 'LIKE', "%$search%");
            })
            ->when($request->get('user'), function ($query, $search) {
                return $query->whereRelation("user", "name", "like", "%$search%");
            })
            ->when($request->get('sortBy'), function ($query, $sort) {
                $sortBy = collect(json_decode($sort));
                return $query->orderBy($sortBy['key'], $sortBy['order']);
            });

        $data = $request->get('limit', 0) > 0 ? $query->paginate($request->get('limit', 10)) : $query->get();

        return new DriverCollection($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->only([
                'name',
                'phone',
                'address',
            ]), [
                'name' => 'required|string|min:3|max:30',
                'phone' => 'required|string|max:20|unique:drivers,phone',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'errors' => $validator->errors()->toArray()], 422);
            }

            $driver = Driver::query()
                ->create([
                    'name' => $request->name,
                    'phone' => $request->phone,
                    'address' => $request->address,
                    'user_id' => auth()->id()
                ]);

            DB::commit();

            return new DriverResource($driver->load('user'));

        } catch (\Exception $exception) {
            abort(403, $exception->getCode() . ' ' . $exception->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Driver $driver)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Driver $driver)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->only([
                'name',
                'phone',
                'address',
            ]), [
                'name' => 'required|string|min:3|max:30',
                'phone' => 'required|string|max:20|unique:drivers,phone,' . $request->id,
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'errors' => $validator->errors()->toArray()], 422);
            }

            $driver->update([
                'name' => $request->name,
                'phone' => $request->phone,
                'address' => $request->address,
                'user_id' => auth()->id()
            ]);

            DB::commit();

            return new DriverResource($driver->load('user'));

        } catch (\Exception $exception) {
            abort(403, $exception->getCode() . ' ' . $exception->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Driver $driver, Request $request)
    {
        DB::beginTransaction();
        try {
            $drivers = $request->driver_id;
            if (is_array($drivers)) {
                Driver::query()
                    ->whereIn('id', $request->driver_id)->delete();
            } else {
                $driver->delete();
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
