<?php

use App\Models\Car;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return response()->json(['status' => 'OK'], 201);
});
Route::get('tos', function (\Illuminate\Http\Request $request) {
//    $role = Role::query()->with('permissions', 'users')->first();
//    return $role->jsonSerialize();
    $role = Role::findById(3)->load('permissions');

    $active = $role->permissions;

    $permissions = \Spatie\Permission\Models\Permission::all();

    $inactive = $permissions->whereNotIn('id', $active->pluck('id'));

    $all = $permissions
//        ->filter(function ($item) {
//            return $item['title'] === 'Index';
//        })
        ->groupBy(['parent', function (object $item) {
            return collect($item['children'])->first();
        }], preserveKeys: true);
    return response()->json([
        'role' => $role->only(['id', 'name', 'created_at']),
        'all' => $all,
        'active' => collect($active)->pluck('id'),
        'inactive' => $inactive

    ], 201);
});

Route::group(['prefix' => 'car'], function (){
    Route::get('recap', function () {
        $car_id = [1,2,3,4,5,6,7];
        $cost = \App\Models\Cost::query()
            ->selectRaw('subject_id as car_id, amount as cost, year(trade_date) as year, month(trade_date) as month, day(trade_date) as day')
            ->whereHasMorph('subject', [Car::class], function ($query) use ($car_id) {
                $query->when($car_id, function ($car, $search) {
                    $car->whereIn('id', $search);
                });
            })
            ->get();

        $trading = \App\Models\Trading::query()
        ->whereHas('car', function ($query) use ($car_id) {
                $query->when($car_id, function ($car, $search) {
                    $car->whereIn('id', $search);
                });
            })
            ->whereNotNull('trade_status')
            ->selectRaw('car_id, year(trade_date) as year, month(trade_date) as month, day(trade_date) as day, (car_fee*net_weight) as income')
            ->whereHas('car')
            ->get();

        $plantation = \App\Models\Plantation::query()
            ->whereHas('car', function ($query) use ($car_id) {
                $query->when($car_id, function ($car, $search) {
                    $car->whereIn('id', $search);
                });
            })
            ->selectRaw('car_id, year(trade_date) as year, month(trade_date) as month, day(trade_date) as day, (car_fee*net_weight) as income')
            ->whereHas('car')
            ->get();

        $now = Carbon::create(2024, 02, 05);
        $periods = CarbonPeriod::create($now->startOfMonth()->toDateString(), $now->endOfMonth()->toDateString());
        $data = array();
        foreach ($periods as $key => $period) {
            $month = $period->format('m');
            $year = $period->format('Y');
            $day = $period->format('d');

            $data[$key]['year'] = $year;
            $data[$key]['month'] = $month;
            $data[$key]['day'] = $day;
            $data[$key]['period'] = $period->addDay()->format('d F Y');
            $data[$key]['trading'] = collect($trading)->where('year', (int)$year)->where('month', (int)$month)->where('day', (int)$day)->sum('income');
            $data[$key]['plantation'] = collect($plantation)->where('year', (int)$year)->where('month', (int)$month)->where('day', (int)$day)->sum('income');
            $data[$key]['cost'] = collect($cost)->where('year', (int)$year)->where('month', (int)$month)->where('day', (int)$day)->sum('cost');
        }
        return response()->json([
            'array' => $data
        ]);
    });
});

Route::get('tes', function () {
//    $now = Carbon::now();
    $cur_month = Carbon::now()->format('m');
    $current = Carbon::create(2024, 1, 1);
    $data = array();
    for($key = 1; $key <= $cur_month; $key++){
        $month = $current->format('m');
        $year = $current->format('Y');
        $day = $current->format('d');

        $data[$key]['day'] = $day;
        $data[$key]['month'] = $month;
        $data[$key]['year'] = $year;
        $data[$key]['current'] = $current->format('d F Y');

        $current->addMonth();
    }

    return $data;
//    $year = now()->format('Y');
//    $car_id = [1,2,3,4,5,6];
//    $plantations = \App\Models\Plantation::query()
//        ->select(['id', 'car_id', 'car_fee', 'net_weight', DB::raw('"Plantation" as `type`, trade_date, month(trade_date) as month, year(trade_date) as year, (car_fee*net_weight) as car_price')])
//        ->whereYear('trade_date', $year)
//        ->whereIn('car_id', $car_id)
//        ->get()->collect();
//
//    $tradings = \App\Models\Trading::query()
//        ->select(['id', 'car_id', 'car_fee', 'net_weight', DB::raw('"Trading" as `type`, trade_date, month(trade_date) as month, year(trade_date) as year, (car_fee*net_weight) as car_price')])
//        ->whereYear('trade_date', $year)
//        ->whereIn('car_id', $car_id)
//        ->whereNotNull('trade_status')
//        ->get()->collect();
//
//    $data = ['car_id' => $car_id, 'year' => $year];
//
//    $car_tradings = DB::table('cars')
//        ->select(['cars.id', 'cars.name', 'cars.no_pol', 'tradings.car_fee', 'tradings.net_weight', DB::raw('"Trading" as `type`, tradings.trade_date, month(tradings.trade_date) as month, year(tradings.trade_date) as year, (tradings.car_fee*tradings.net_weight) as car_price')])
//        ->join('tradings', 'cars.id', '=', 'tradings.car_id')
//        ->whereIn('cars.id', $data['car_id'])
//        ->whereNotNull('tradings.trade_status')
//        ->when(Arr::exists($data, 'period_end'), function (Builder $builder) use ($data) {
//            $builder->where('tradings.trade_date', '<=', $data['period_end']);
//        })
//        ->when(Arr::exists($data, 'year'), function (Builder $builder) use ($data) {
//            $builder->whereYear('tradings.trade_date', $data['year']);
//        })
//        ->when(Arr::exists($data, 'month'), function (Builder $builder) use ($data) {
//            $builder->whereMonth('tradings.trade_date', $data['month']);
//        })
//        ->get();
//    $car_plantations = DB::table('cars')
//        ->select(['cars.id', 'cars.name', 'cars.no_pol', 'plantations.car_fee', 'plantations.net_weight', DB::raw('"Plantation" as `type`, plantations.trade_date, month(plantations.trade_date) as month, year(plantations.trade_date) as year, (plantations.car_fee*plantations.net_weight) as car_price')])
//        ->join('plantations', 'cars.id', '=', 'plantations.car_id')
//        ->whereIn('cars.id', $data['car_id'])
//        ->when(Arr::exists($data, 'period_end'), function (Builder $builder) use ($data) {
//            $builder->where('plantations.trade_date', '<=', $data['period_end']);
//        })
//        ->when(Arr::exists($data, 'year'), function (Builder $builder) use ($data) {
//            $builder->whereYear('plantations.trade_date', $data['year']);
//        })
//        ->when(Arr::exists($data, 'month'), function (Builder $builder) use ($data) {
//            $builder->whereMonth('plantations.trade_date', $data['month']);
//        })
//        ->get();
//
//    $cars = \App\Models\Car::query()
//        ->select(['id', 'no_pol'])
//        ->whereIn('id', $car_id)
//        ->get()->map(function ($car) use($plantations, $tradings){
//
//            return [
//                'id' => $car->id,
//                'no_pol' => $car->no_pol,
//                'trade' => $tradings->where('car_id', $car->id)->merge($plantations->where('car_id', $car->id)),
//                'tradings' => $tradings->where('car_id', $car->id),
//                'plantations' => $plantations->where('car_id', $car->id)
//            ];
//        });
//
//    return response()->json([
//        'cars' => $cars,
//        'tradings' => $car_tradings,
//        'plantations' => $car_plantations,
//        'join_trading' => collect($car_tradings->merge($car_plantations)),
//
//    ]);
});
