<?php

namespace App\Http\Controllers\Api\Data;

use App\Http\Controllers\Controller;
use App\Http\Resources\Data\Land\LandCollection;
use App\Http\Resources\Data\Land\LandResource;
use App\Models\Land;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LandController extends Controller
{
    public function index(Request $request)
    {
        $query = Land::query()
            ->with('area')
            ->when($request->get('name'), function ($query, $search) {
                return $query->where('name', 'LIKE', "%$search%");
            })
            ->when($request->get('area_id'), function ($query, $search) {
                return $query->whereRelation("area", "id", $search);
            })
            ->when($request->get('user'), function ($query, $search) {
                return $query->whereRelation("user", "name", "like", "%$search%");
            })
            ->when($request->get('sortBy'), function ($query, $sort) {
                $sortBy = collect(json_decode($sort));
                return $query->orderBy($sortBy['key'], $sortBy['order']);
            });

        $data = $request->get('limit', 0) > 0 ? $query->paginate($request->get('limit', 10)) : $query->get();

        return new LandCollection($data);
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
                'area_id',
                'wide',
                'trees',
            ]), [
                'name' => 'required|string|min:3|max:30|unique:lands,name',
                'area_id' => 'required|exists:areas,id',
                'wide' => 'required|numeric',
                'trees' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'errors' => $validator->errors()->toArray()], 422);
            }

            $land = Land::query()
                ->create([
                    'name' => $request->name,
                    'area_id' => $request->area_id,
                    'wide' => $request->wide,
                    'trees' => $request->trees,
                    'user_id' => auth()->id()
                ]);

            DB::commit();

            return new LandResource($land->load(['user', 'area']));

        } catch (\Exception $exception) {
            abort(403, $exception->getCode() . ' ' . $exception->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Land $land)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Land $land)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->only([
                'name',
                'area_id',
                'wide',
                'trees',
            ]), [
                'name' => 'required|string|min:3|max:30|unique:lands,name,' . $land->id,
                'area_id' => 'required|exists:areas,id',
                'wide' => 'required|numeric',
                'trees' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'errors' => $validator->errors()->toArray()], 422);
            }

            $land->update([
                'name' => $request->name,
                'area_id' => $request->area_id,
                'wide' => $request->wide,
                'trees' => $request->trees,
                'user_id' => auth()->id()
            ]);

            DB::commit();

            return new LandResource($land->load(['user', 'area']));

        } catch (\Exception $exception) {
            abort(403, $exception->getCode() . ' ' . $exception->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Land $land, Request $request)
    {
        DB::beginTransaction();
        try {
            $lands = $request->land_id;
            if (is_array($lands)) {
                Land::query()
                    ->whereIn('id', $request->land_id)->delete();
            } else {
                $land->delete();
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
