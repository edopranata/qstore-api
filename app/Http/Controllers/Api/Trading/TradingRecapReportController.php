<?php

namespace App\Http\Controllers\Api\Trading;

use App\Http\Controllers\Controller;
use App\Models\Trading;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

class TradingRecapReportController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        switch ($request->get('type')) {
            case 'Monthly':
                return $this->monthly($request);

            case 'Annual':
                return $this->annual($request);
            default:
                abort(301, 'Invalid parameter');
        }
    }

    private function monthly(Request $request): JsonResponse|array|Collection
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
        $params['type'] = $request->type;
        $collection = $this->detail_report($params);
        return $this->report($params, $collection);
    }

    private function annual(Request $request): JsonResponse|array|Collection
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
        $params['type'] = $request->type;

        $collection = $this->detail_report($params);

        return $this->report($params, $collection);

    }

    private function report(array $params, Collection $collection): array|Collection
    {

        $report = array();
        if($params['type'] === 'Monthly'){
            $now = Carbon::create($params['year'], $params['month'], 01);
            $periods = CarbonPeriod::create($now->startOfMonth()->toDateString(), $now->endOfMonth()->toDateString());

            $temp = array();
            foreach ($periods as $key => $period) {
                $month = $period->format('m');
                $year = $period->format('Y');
                $day = $period->format('d');

                $where = $collection->where('year', (int)$year)->where('month', (int)$month)->where('day', (int)$day);
                $count = $where->count();

                $net_weight = $where->sum('net_weight');
                $net_price = $where->avg('net_price');
                $margin = $where->avg('margin');
                $gross_total = $where->sum('gross_total');
                $cost_total = $where->sum('cost_total');
                $customer_average = $where->avg('customer_average');
                $customer_total = $where->sum('customer_total');
                $customer_weight = $where->sum('customer_weight');
                $net_total = $where->sum('net_total');


                if($count > 0){
                    $temp[$key]['year'] = (int)$year;
                    $temp[$key]['month'] = (int)$month;
                    $temp[$key]['day'] = (int)$day;
                    $temp[$key]['period'] = $period->format('d F Y');
                    $temp[$key]['net_weight'] = $net_weight;
                    $temp[$key]['net_price'] = $net_price;
                    $temp[$key]['margin'] = $margin;
                    $temp[$key]['gross_total'] = $gross_total;
                    $temp[$key]['cost_total'] = $cost_total;
                    $temp[$key]['customer_average'] = $customer_average;
                    $temp[$key]['customer_total'] = $customer_total;
                    $temp[$key]['customer_weight'] = $customer_weight;
                    $temp[$key]['net_total'] = $net_total;

                    $temp[$key]['count'] = $count;
                }

            }
            $report = collect($temp)->values();
        }

        if($params['type'] === 'Annual'){
            $current = Carbon::create($params['year'], 1, 1);

            $temp = array();
            for ($key = 1; $key <= 12; $key++) {
                $month = $current->format('m');
                $year = $current->format('Y');
                $day = $current->format('d');

                $where = $collection->where('year', (int)$year)->where('month', (int)$month);
                $count = $where->count();

                $net_weight = $where->sum('net_weight');
                $net_price = $where->avg('net_price');
                $margin = $where->avg('margin');
                $gross_total = $where->sum('gross_total');
                $cost_total = $where->sum('cost_total');
                $customer_average = $where->avg('customer_average');
                $customer_total = $where->sum('customer_total');
                $customer_weight = $where->sum('customer_weight');
                $net_total = $where->sum('net_total');

                if($count > 0){
                    $temp[$key]['day'] = $day;
                    $temp[$key]['month'] = $month;
                    $temp[$key]['year'] = $year;
                    $temp[$key]['period'] = $current->format('F Y');
                    $temp[$key]['net_weight'] = $net_weight;
                    $temp[$key]['net_price'] = $net_price;
                    $temp[$key]['margin'] = $margin;
                    $temp[$key]['gross_total'] = $gross_total;
                    $temp[$key]['cost_total'] = $cost_total;
                    $temp[$key]['customer_average'] = $customer_average;
                    $temp[$key]['customer_total'] = $customer_total;
                    $temp[$key]['customer_weight'] = $customer_weight;
                    $temp[$key]['net_total'] = $net_total;
                    $temp[$key]['count'] = $count;
                }

                $current->addMonth();
            }
            $report = collect($temp)->values();
        }

        return $report;
    }

    private function detail_report(array $params): Collection
    {
        return Trading::query()
            ->with(['car', 'driver'])
            ->whereNotNull('trade_status')
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
                    'margin' => $trade->margin,
                    'gross_total' => $trade->gross_total,
                    'cost_total' => $trade->cost_total,
                    'customer_average' => $trade->customer_average_price,
                    'customer_total' => $trade->customer_total_price,
                    'customer_weight' => $trade->customer_total_weight,
                    'net_total' => $trade->net_income,
                ];
            })->collect();
    }
}
