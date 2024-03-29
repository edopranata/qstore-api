<?php

namespace App\Http\Controllers\Api\Plantation;

use App\Http\Controllers\Controller;
use App\Models\Plantation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

class PlantationReportController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        switch ($request->type) {
            case 'Period':
                return $this->period($request);

            case 'Monthly':
                return $this->monthly($request);

            case 'Annual':
                return $this->annual($request);
            default:
                abort(301, 'Invalid parameter');
        }
    }

    private function period(Request $request): JsonResponse|Collection
    {
        $validator = Validator::make($request->only([
            'period_start', 'period_end'
        ]), [
            'period_start' => ['required', 'date', 'min:1'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()->toArray()], 422);
        }

        $params = collect($request->only(['period_start','period_end']))->toArray();

        return $this->detail_report($params);

    }

    private function monthly(Request $request): JsonResponse|Collection
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

        $params = array();
        $params['year'] = $date[0];
        $params['month'] = $date[1];

        return $this->detail_report($params);
    }

    private function annual(Request $request): JsonResponse|Collection
    {
        $validator = Validator::make($request->only([
            'annual'
        ]), [
            'annual' => ['required', 'date_format:Y'],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()->toArray()], 422);
        }

        $params = array();
        $params['year'] = $request->annual;

        return $this->detail_report($params);
    }

    private function detail_report(array $params): Collection
    {
        return Plantation::query()
            ->with(['car', 'driver', 'details.land'])
            ->when(Arr::exists($params, 'period_start'), function ($builder) use ($params) {
                $builder->whereDate('trade_date', '>=', $params['period_start']);
            })
            ->when(Arr::exists($params, 'period_end'), function ($builder) use ($params) {
                $builder->whereDate('trade_date', '<=', $params['period_end']);
            })
            ->when(Arr::has($params, 'month'), function ($builder) use ($params) {
                $builder->whereMonth('trade_date', $params['month']);
            })
            ->when(Arr::has($params, 'year'), function ($builder) use ($params) {
                $builder->whereYear('trade_date', $params['year']);
            })
            ->get()->map(function ($trade) use ($params) {

                return [
                    'id' => $trade->id,
                    'date' => $trade->trade_date->format('Y-m-d H:i:s'),
                    'day' => (int)$trade->trade_date->format('d'),
                    'month' =>(int)$trade->trade_date->format('m'),
                    'year' => (int)$trade->trade_date->format('Y'),
                    'car' => $trade->car->only(['name', 'no_pol']),
                    'driver' => $trade->driver->only(['name', 'phone']),
                    'net_weight' => $trade->net_weight,
                    'net_price' => $trade->net_price,
                    'gross_total' => $trade->net_total,
                    'cost_total' => $trade->gross_total,
                    'net_total' => $trade->net_income,
                    'wide_weight' => $trade->net_weight / $trade->wide_total,
                    'wide_cost' => $trade->gross_total / $trade->wide_total,
                    'wide_total' => $trade->wide_total,
                    'trees_total' => $trade->trees_income,
                    'details' => $trade->details
                ];
            })->collect();
    }
}
