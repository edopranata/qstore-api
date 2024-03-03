<?php

namespace App\Http\Controllers\Api\Report\Car;

use App\Http\Controllers\Controller;
use App\Http\Resources\Data\Car\CarResource;
use App\Models\Car;
use App\Models\Cost;
use App\Models\Plantation;
use App\Models\Trading;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class CarRecapReportController extends Controller
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
                $cars = Car::query()->where('status', 'yes')->get();
                return response()->json([
                    'cars' => CarResource::collection($cars),
                ], 201);
        }
    }

    private function monthly(Request $request): JsonResponse
    {
        $validator = Validator::make($request->only([
            'monthly', 'car_id'
        ]), [
            'monthly' => ['required', 'date_format:Y/m'],
            'car_id' => 'required|array',
            'car_id.*' => 'exists:cars,id',

        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()->toArray()], 422);
        }

        $date = str($request->get('monthly'))->split('#/#');

        $data = ['month' => $date[1], 'year' => $date[0], 'car_id' => $request->get('car_id')];

        return response()->json(['cars' => $this->cars($data)], 201);
    }

    public function cars(array $data): array
    {
        $cost = Cost::query()
            ->selectRaw('subject_id as car_id, amount as cost, year(trade_date) as year, month(trade_date) as month, day(trade_date) as day')
            ->whereHasMorph('subject', [Car::class], function ($query) use ($data) {
                $query->when(Arr::exists($data, 'car_id'), function ($car) use ($data) {
                    $car->whereIn('id', $data['car_id']);
                });
            })
            ->when(Arr::exists($data, 'year'), function (Builder $builder) use ($data) {
                $builder->whereYear('trade_date', $data['year']);
            })
            ->when(Arr::exists($data, 'month'), function (Builder $builder) use ($data) {
                $builder->whereMonth('trade_date', $data['month']);
            })
            ->get();

        $trading = Trading::query()
            ->whereIn('car_id', $data['car_id'])
            ->when(Arr::exists($data, 'year'), function (Builder $builder) use ($data) {
                $builder->whereYear('trade_date', $data['year']);
            })
            ->when(Arr::exists($data, 'month'), function (Builder $builder) use ($data) {
                $builder->whereMonth('trade_date', $data['month']);
            })
            ->whereNotNull('trade_status')
            ->selectRaw('car_id, year(trade_date) as year, month(trade_date) as month, day(trade_date) as day, (car_fee*net_weight) as income')
            ->whereHas('car')
            ->get();

        $plantation = Plantation::query()
            ->whereIn('car_id', $data['car_id'])
            ->when(Arr::exists($data, 'year'), function (Builder $builder) use ($data) {
                $builder->whereYear('trade_date', $data['year']);
            })
            ->when(Arr::exists($data, 'month'), function (Builder $builder) use ($data) {
                $builder->whereMonth('trade_date', $data['month']);
            })
            ->selectRaw('car_id, year(trade_date) as year, month(trade_date) as month, day(trade_date) as day, (car_fee*net_weight) as income')
            ->whereHas('car')
            ->get();


        $report = array();

        if (Arr::exists($data, 'month')) {
            $now = Carbon::create($data['year'], $data['month'], 01);
            $periods = CarbonPeriod::create($now->startOfMonth()->toDateString(), $now->endOfMonth()->toDateString());

//            dd($data);
            foreach ($periods as $key => $period) {
                $month = $period->format('m');
                $year = $period->format('Y');
                $day = $period->format('d');

                $report[$key]['year'] = $year;
                $report[$key]['month'] = $month;
                $report[$key]['day'] = $day;
                $report[$key]['period'] = $period->format('d F Y');
                $report[$key]['trading'] = collect($trading)->where('year', (int)$year)->where('month', (int)$month)->where('day', (int)$day)->sum('income');
                $report[$key]['plantation'] = collect($plantation)->where('year', (int)$year)->where('month', (int)$month)->where('day', (int)$day)->sum('income');
                $report[$key]['cost'] = collect($cost)->where('year', (int)$year)->where('month', (int)$month)->where('day', (int)$day)->sum('cost');
            }
        } else {
            $cur_month = Carbon::now()->format('m');
            $current = Carbon::create($data['year'], 1, 1);

            for ($key = 1; $key <= $cur_month; $key++) {
                $month = $current->format('m');
                $year = $current->format('Y');
                $day = $current->format('d');

                $report[$key]['day'] = $day;
                $report[$key]['month'] = $month;
                $report[$key]['year'] = $year;
                $report[$key]['period'] = $current->format('F Y');
                $report[$key]['trading'] = collect($trading)->where('year', (int)$year)->where('month', (int)$month)->sum('income');
                $report[$key]['plantation'] = collect($plantation)->where('year', (int)$year)->where('month', (int)$month)->sum('income');
                $report[$key]['cost'] = collect($cost)->where('year', (int)$year)->where('month', (int)$month)->sum('cost');

                $current->addMonth();
            }
        }


        return collect($report)->values()->toArray();
    }

    private function annual(Request $request): JsonResponse
    {
        $validator = Validator::make($request->only([
            'annual', 'car_id'
        ]), [
            'annual' => ['required', 'date_format:Y'],
            'car_id' => 'required|array',
            'car_id.*' => 'exists:cars,id',

        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()->toArray()], 422);
        }

        $data = ['year' => $request->get('annual'), 'car_id' => $request->get('car_id')];

        return response()->json(['cars' => $this->cars($data)], 201);
    }
}
