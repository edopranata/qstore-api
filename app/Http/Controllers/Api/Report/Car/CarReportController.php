<?php

namespace App\Http\Controllers\Api\Report\Car;

use App\Http\Controllers\Controller;
use App\Http\Resources\Data\Car\CarResource;
use App\Models\Car;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CarReportController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        switch ($request->get('type')) {
            case 'Period':
                return $this->period($request);

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

    public function cars(array $data): Collection
    {
        $car_tradings = DB::table('cars')
            ->select(['cars.id', 'cars.name', 'cars.no_pol', 'tradings.car_fee', 'tradings.net_weight', DB::raw('"Trading" as `type`, tradings.trade_date, month(tradings.trade_date) as month, year(tradings.trade_date) as year, (tradings.car_fee*tradings.net_weight) as car_price')])
            ->join('tradings', 'cars.id', '=', 'tradings.car_id')
            ->whereIn('cars.id', $data['car_id'])
            ->whereNotNull('tradings.trade_status')
            ->when(Arr::exists($data, 'period_start'), function (Builder $builder) use ($data) {
                $builder->whereDate('tradings.trade_date', '>=', $data['period_start']);
            })
            ->when(Arr::exists($data, 'period_end'), function (Builder $builder) use ($data) {
                $builder->whereDate('tradings.trade_date', '<=', $data['period_end']);
            })
            ->when(Arr::exists($data, 'year'), function (Builder $builder) use ($data) {
                $builder->whereYear('tradings.trade_date', $data['year']);
            })
            ->when(Arr::exists($data, 'month'), function (Builder $builder) use ($data) {
                $builder->whereMonth('tradings.trade_date', $data['month']);
            })
            ->get();

        $car_plantations = DB::table('cars')
            ->select(['cars.id', 'cars.name', 'cars.no_pol', 'plantations.car_fee', 'plantations.net_weight', DB::raw('"Plantation" as `type`, plantations.trade_date, month(plantations.trade_date) as month, year(plantations.trade_date) as year, (plantations.car_fee*plantations.net_weight) as car_price')])
            ->join('plantations', 'cars.id', '=', 'plantations.car_id')
            ->whereIn('cars.id', $data['car_id'])
            ->when(Arr::exists($data, 'period_start'), function (Builder $builder) use ($data) {
                $builder->whereDate('plantations.trade_date', '>=', $data['period_start']);
            })
            ->when(Arr::exists($data, 'period_end'), function (Builder $builder) use ($data) {
                $builder->whereDate('plantations.trade_date', '<=', $data['period_end']);
            })
            ->when(Arr::exists($data, 'year'), function (Builder $builder) use ($data) {
                $builder->whereYear('plantations.trade_date', $data['year']);
            })
            ->when(Arr::exists($data, 'month'), function (Builder $builder) use ($data) {
                $builder->whereMonth('plantations.trade_date', $data['month']);
            })
            ->get();

        return $car_tradings->merge($car_plantations);
    }

    private function period(Request $request): JsonResponse
    {
        $validator = Validator::make($request->only([
            'period_start', 'period_end', 'car_id'
        ]), [
            'period_start' => ['required', 'date', 'min:1'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'car_id' => 'required|array',
            'car_id.*' => 'exists:cars,id',

        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()->toArray()], 422);
        }

        $data = ['period_start' => $request->get('period_start'), 'period_end' => $request->get('period_end'), 'car_id' => $request->get('car_id')];

        return response()->json(['cars' => $this->cars($data), 'request' => collect($data['car_id'])], 201);
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

        return response()->json(['cars' => $this->cars($data), 'request' => collect($data['car_id'])], 201);
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

        return response()->json(['cars' => $this->cars($data), 'request' => collect($data['car_id'])], 201);
    }
}
