<?php

namespace App\Http\Controllers\Api\Report\Plantation;

use App\Http\Controllers\Controller;
use App\Http\Resources\Transaction\Plantation\PlantationResource;
use App\Models\Plantation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class PlantationReportController extends Controller
{
    public function index(Request $request)
    {
        switch ($request->type) {
            case 'Period':
                return $this->period($request);

            case 'Monthly':
                return $this->monthly($request);

            case 'Annual':
                return $this->annual($request);
            default:
                abort(403, 'Invalid parameters');
        }
    }

    private function period(Request $request): JsonResponse
    {
        $validator = Validator::make($request->only([
            'period_start', 'period_end'
        ]), [
            'period_start' => ['required', 'date', 'min:1'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start']

        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()->toArray()], 422);
        }

        $data = $request->only(['period_start', 'period_end']);

        return response()->json(['plantations' => PlantationResource::collection($this->plantation($data))], 201);

    }

    private function plantation(array $data)
    {
        return Plantation::query()
            ->when(Arr::exists($data, 'period_start'), function (Builder $builder) use ($data) {
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
            })->get();
    }

    private function monthly(Request $request)
    {
        $validator = Validator::make($request->only([
            'monthly'
        ]), [
            'monthly' => ['required', 'date_format:Y/m'],

        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()->toArray()], 422);
        }

        $date = str($request->monthly)->split('#/#');

        $data = ['month' => $date[1], 'year' => $date[0]];

        return response()->json(['plantations' => PlantationResource::collection($this->plantation($data))], 201);
    }

    private function annual(Request $request)
    {
        $validator = Validator::make($request->only([
            'annual'
        ]), [
            'annual' => ['required', 'date_format:Y'],

        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()->toArray()], 422);
        }

        $data = ['year' => $request->annual];

        return response()->json(['plantations' => PlantationResource::collection($this->plantation($data))], 201);
    }
}
