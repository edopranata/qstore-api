<?php

namespace App\Http\Controllers\Api\Trading;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\LoanDetails;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

class LoanRecapReportController extends Controller
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
                $debit = $where->sum('debit');
                $credit = $where->avg('credit');


                if($count > 0){
                    $temp[$key]['year'] = (int)$year;
                    $temp[$key]['month'] = (int)$month;
                    $temp[$key]['day'] = (int)$day;
                    $temp[$key]['period'] = $period->format('d F Y');
                    $temp[$key]['debit'] = $debit;
                    $temp[$key]['credit'] = $credit;
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
                $debit = $where->sum('debit');
                $credit = $where->avg('credit');

                if($count > 0){
                    $temp[$key]['day'] = $day;
                    $temp[$key]['month'] = $month;
                    $temp[$key]['year'] = $year;
                    $temp[$key]['period'] = $current->format('F Y');
                    $temp[$key]['debit'] = $debit;
                    $temp[$key]['credit'] = $credit;
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
        return LoanDetails::query()
            ->withWhereHas('loan', function ($builder) {
                return $builder->whereHasMorph('person', [Customer::class], function ($query) {
                    $query->where('type',  'farmer');
                });
            })
            ->when(Arr::exists($params, 'period_start'), function ($builder) use ($params) {
                $builder->whereDate('trade_date', '>=', $params['period_start']);
            })
            ->when(Arr::exists($params, 'period_end'), function ($builder) use ($params) {
                $builder->whereDate('trade_date', '<=', $params['period_end']);
            })
            ->when(Arr::exists($params, 'year'), function ($builder) use ($params) {
                $builder->whereYear('trade_date', $params['year']);
            })
            ->when(Arr::exists($params, 'month'), function ($builder) use ($params) {
                $builder->whereMonth('trade_date', $params['month']);
            })
            ->get()
            ->map(function ($loan) {
                $trade_date = Carbon::parse($loan->trade_date);
                return [
                    'trade_date' => $trade_date->format('d F Y'),
                    'day' => (int)$trade_date->format('d'),
                    'month' => (int)$trade_date->format('m'),
                    'year' => (int)$trade_date->format('Y'),
                    'debit' => $loan->balance > 0 ? $loan->balance : 0,
                    'credit' => $loan->balance < 0 ? $loan->balance * -1 : 0,
                ];
            })->collect();
    }
}
