<?php

namespace App\Http\Controllers\Api\Report\Plantation;

use App\Http\Controllers\Controller;
use App\Http\Resources\Data\Area\AreaResource;
use App\Http\Resources\Transaction\Plantation\PlantationDetailResource;
use App\Models\Area;
use App\Models\PlantationDetails;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

class LandReportController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {

        switch ($request->type) {
            case 'Period':
                return $this->period($request);

            case 'Monthly':
                return $this->monthly($request);

            case 'Annual':
                return $this->annual($request);
            default:
                $areas = Area::query()->with('lands')->get();
                return response()->json([
                    'areas' => AreaResource::collection($areas),
                ], 201);
        }
    }

    private function lands(array $data): Collection|array
    {
        $land_id = collect($data['land_id'])->toArray();
        return PlantationDetails::query()
            ->with('land.area')
            ->withWhereHas('plantation', function ($builder) use ($data) {
                return $builder->when(Arr::exists($data, 'period_start'), function (Builder $builder) use ($data) {
                    $builder->where('trade_date', '>=', $data['period_start']);
                })
                    ->when(Arr::exists($data, 'period_end'), function (Builder $builder) use ($data) {
                        $builder->where('trade_date', '<=', $data['period_end']);
                    })
                    ->when(Arr::exists($data, 'year'), function (Builder $builder) use ($data) {
                        $builder->whereYear('trade_date', $data['year']);
                    })
                    ->when(Arr::exists($data, 'month'), function (Builder $builder) use ($data) {
                        $builder->whereMonth('trade_date', $data['month']);
                    });
            })
            ->whereIn('land_id', $land_id)->get();

    }

    private function period(Request $request): JsonResponse
    {
        $validator = Validator::make($request->only([
            'period_start', 'period_end', 'land_id'
        ]), [
            'period_start' => ['required', 'date', 'min:1'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'land_id' => 'required|array',
            'land_id.*' => 'exists:lands,id'

        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()->toArray()], 422);
        }

        $data = ['period_start' => $request->period_start, 'period_end' => $request->period_end,  'land_id' => $request->land_id];

        return response()->json(['lands' => PlantationDetailResource::collection($this->lands($data))], 201);

    }

    private function monthly(Request $request): JsonResponse
    {
        $validator = Validator::make($request->only([
            'monthly', 'land_id'
        ]), [
            'monthly' => ['required', 'date_format:Y/m'],
            'land_id' => 'required|array',
            'land_id.*' => 'exists:lands,id'

        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()->toArray()], 422);
        }

        $date = str($request->monthly)->split('#/#');

        $data = ['month' => $date[1], 'year' => $date[0], 'land_id' =>$request->land_id];

        return response()->json(['lands' => PlantationDetailResource::collection($this->lands($data))], 201);
    }

    private function annual(Request $request): JsonResponse
    {
        $validator = Validator::make($request->only([
            'annual', 'land_id'
        ]), [
            'annual' => ['required', 'date_format:Y'],
            'land_id' => 'required|array',
            'land_id.*' => 'exists:lands,id'

        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()->toArray()], 422);
        }

        $data = ['year' => $request->annual, 'land_id' => $request->land_id];

        return response()->json(['lands' => PlantationDetailResource::collection($this->lands($data))], 201);
    }
}
