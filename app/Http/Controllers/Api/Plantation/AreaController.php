<?php

namespace App\Http\Controllers\Api\Plantation;

use App\Http\Controllers\Controller;
use App\Http\Resources\Data\Area\AreaCollection;
use App\Http\Resources\Data\Area\AreaResource;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AreaController extends Controller
{
    public function index(Request $request)
    {
        $query = Area::query()
            ->when($request->get('name'), function ($query, $search) {
                return $query->where('name', 'LIKE', "%$search%");
            })
            ->when($request->get('user'), function ($query, $search) {
                return $query->whereRelation("user", "name", "like", "%$search%");
            })
            ->when($request->get('sortBy'), function ($query, $sort) {
                $sortBy = collect(json_decode($sort));
                return $query->orderBy($sortBy['key'], $sortBy['order']);
            });

        $data = $request->get('limit', 0) > 0 ? $query->paginate($request->get('limit', 10)) : $query->get();

        return new AreaCollection($data);
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
            ]), [
                'name' => 'required|string|min:3|max:30|unique:areas,name',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'errors' => $validator->errors()->toArray()], 422);
            }

            $area = Area::query()
                ->create([
                    'name' => $request->name,
                    'user_id' => auth()->id()
                ]);

            DB::commit();

            return new AreaResource($area->load('user'));

        } catch (\Exception $exception) {
            abort(403, $exception->getCode() . ' ' . $exception->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Area $area)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Area $area)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->only([
                'name',
            ]), [
                'name' => 'required|string|min:3|max:30|unique:areas,name,' . $area->id,
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'errors' => $validator->errors()->toArray()], 422);
            }

            $area->update([
                'name' => $request->name,
                'user_id' => auth()->id()
            ]);

            DB::commit();

            return new AreaResource($area->load('user'));

        } catch (\Exception $exception) {
            abort(403, $exception->getCode() . ' ' . $exception->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Area $area, Request $request)
    {
        DB::beginTransaction();
        try {
            $areas = $request->area_id;
            if (is_array($areas)) {
                Area::query()
                    ->whereIn('id', $request->area_id)->delete();
            } else {
                $area->delete();
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
