<?php

use App\Http\Resources\Invoice\InvoiceCollection;
use App\Models\Car;
use App\Models\Driver;
use App\Models\Invoice;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
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

Route::group(['prefix' => 'car'], function () {
    Route::get('recap', function () {
        $car_id = [1, 2, 3, 4, 5, 6, 7];
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

Route::get('recap', function () {
    $params = array();
    $params['type'] = 'Annual';
    $params['land_id'] = [5];
    $params['year'] = 2024;
    $params['month'] = 02;

    $lands = DB::table('plantation_details', 'pd')
        ->join(DB::raw('plantations p'), 'pd.plantation_id', '=', 'p.id')
        ->whereIn('pd.land_id', $params['land_id'])
        ->when(Arr::exists($params, 'period_start'), function (\Illuminate\Database\Query\Builder $builder) use ($params) {
            $builder->where('p.trade_date', '>=', $params['period_start']);
        })
        ->when(Arr::exists($params, 'period_end'), function (\Illuminate\Database\Query\Builder $builder) use ($params) {
            $builder->where('p.trade_date', '<=', $params['period_end']);
        })
        ->when(Arr::exists($params, 'year'), function (\Illuminate\Database\Query\Builder $builder) use ($params) {
            $builder->whereYear('p.trade_date', $params['year']);
        })
        ->when(Arr::exists($params, 'month'), function (\Illuminate\Database\Query\Builder $builder) use ($params) {
            $builder->whereMonth('p.trade_date', $params['month']);
        })
        ->get()
        ->map(function ($land) {
            $trade_date = Carbon::parse($land->trade_date);
            $weight_per_wide = $land->net_weight / $land->wide_total;
            $weight_per_tree = $land->net_weight / $land->trees_total;
            return [
                'trade_date' => $trade_date->format('d F Y'),
                'day' => (int)$trade_date->format('d'),
                'month' => (int)$trade_date->format('m'),
                'year' => (int)$trade_date->format('Y'),
                'wide' => $land->wide,
                'trees' => $land->trees,
                'weight_per_wide' => $weight_per_wide,
                'weight_per_tree' => $weight_per_tree,
                'net_price' => $land->net_price,
                'price_per_wide' => $land->net_price * $weight_per_wide,
                'price_per_tree' => $land->net_price * $weight_per_tree,

            ];
        });

    $report = array();

    if (Arr::exists($params, 'month')) {
        $now = Carbon::create($params['year'], $params['month'], 01);
        $periods = CarbonPeriod::create($now->startOfMonth()->toDateString(), $now->endOfMonth()->toDateString());

        foreach ($periods as $key => $period) {
            $month = $period->format('m');
            $year = $period->format('Y');
            $day = $period->format('d');
            $count = collect($lands)->where('year', (int)$year)->where('month', (int)$month)->where('day', (int)$day)->count();
            if ($count > 0) {
                $report[$key]['year'] = (int)$year;
                $report[$key]['month'] = (int)$month;
                $report[$key]['day'] = (int)$day;
                $report[$key]['period'] = $period->format('d F Y');
                $report[$key]['count'] = collect($lands)->where('year', (int)$year)->where('month', (int)$month)->where('day', (int)$day)->count();
                $report[$key]['wide'] = collect($lands)->where('year', (int)$year)->where('month', (int)$month)->where('day', (int)$day)->avg('wide');
                $report[$key]['trees'] = collect($lands)->where('year', (int)$year)->where('month', (int)$month)->where('day', (int)$day)->avg('trees');
                $report[$key]['weight_per_wide'] = collect($lands)->where('year', (int)$year)->where('month', (int)$month)->where('day', (int)$day)->sum('weight_per_wide');
                $report[$key]['weight_per_tree'] = collect($lands)->where('year', (int)$year)->where('month', (int)$month)->where('day', (int)$day)->sum('weight_per_tree');
                $report[$key]['net_price'] = collect($lands)->where('year', (int)$year)->where('month', (int)$month)->where('day', (int)$day)->avg('net_price');
                $report[$key]['price_per_wide'] = collect($lands)->where('year', (int)$year)->where('month', (int)$month)->where('day', (int)$day)->avg('price_per_wide');
                $report[$key]['price_per_tree'] = collect($lands)->where('year', (int)$year)->where('month', (int)$month)->where('day', (int)$day)->avg('price_per_tree');
            }
        }
    } else {
        $cur_month = Carbon::now()->format('m');
        $current = Carbon::create($params['year'], 1, 1);

        for ($key = 1; $key <= $cur_month; $key++) {
            $month = $current->format('m');
            $year = $current->format('Y');
            $day = $current->format('d');

            $count = collect($lands)->where('year', (int)$year)->where('month', (int)$month)->count();
            if ($count > 0) {
                $report[$key]['day'] = (int)$day;
                $report[$key]['month'] = (int)$month;
                $report[$key]['year'] = (int)$year;
                $report[$key]['period'] = $current->format('F Y');
                $report[$key]['count'] = collect($lands)->where('year', (int)$year)->where('month', (int)$month)->count();

            }
            $current->addMonth();
        }
    }

    return response()->json([
        'data' => collect($report)->values()->toArray(),
        'lands' => $lands
    ]);

});


Route::get('tes', function (\Illuminate\Http\Request $request) {


    $query = Invoice::query()->with(['loan_details', 'customer', 'detail_do', 'detail_trades']);

    $data = $query->paginate($request->get('limit', 10));

    return new InvoiceCollection($data);


    $params = array();
    $params['type'] = 'Annual';
    $params['year'] = 2024;
//    $params['month'] = 02;
//    $params['daily'] = '2024/02/01';

    $loans = \App\Models\LoanDetails::query()
        ->withWhereHas('loan', function ($builder) {
            return $builder->whereHasMorph('person', [Driver::class]);
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
                'debit' => max($loan->balance, 0),
                'credit' => min($loan->balance, 0),
            ];
        });

    return $loans;

    $report = array();

    if (Arr::exists($params, 'month')) {
        $now = Carbon::create($params['year'], $params['month'], 01);
        $periods = CarbonPeriod::create($now->startOfMonth()->toDateString(), $now->endOfMonth()->toDateString());

        foreach ($periods as $key => $period) {
            $month = $period->format('m');
            $year = $period->format('Y');
            $day = $period->format('d');
            $count = collect($loans)->where('year', (int)$year)->where('month', (int)$month)->where('day', (int)$day)->count();
            if ($count > 0) {
                $report[$key]['year'] = (int)$year;
                $report[$key]['month'] = (int)$month;
                $report[$key]['day'] = (int)$day;
                $report[$key]['period'] = $period->format('d F Y');
                $report[$key]['count'] = collect($loans)->where('year', (int)$year)->where('month', (int)$month)->where('day', (int)$day)->count();
                $report[$key]['wide'] = collect($loans)->where('year', (int)$year)->where('month', (int)$month)->where('day', (int)$day)->avg('wide');
                $report[$key]['trees'] = collect($loans)->where('year', (int)$year)->where('month', (int)$month)->where('day', (int)$day)->avg('trees');
                $report[$key]['weight_per_wide'] = collect($loans)->where('year', (int)$year)->where('month', (int)$month)->where('day', (int)$day)->sum('weight_per_wide');
                $report[$key]['weight_per_tree'] = collect($loans)->where('year', (int)$year)->where('month', (int)$month)->where('day', (int)$day)->sum('weight_per_tree');
                $report[$key]['net_price'] = collect($loans)->where('year', (int)$year)->where('month', (int)$month)->where('day', (int)$day)->avg('net_price');
                $report[$key]['price_per_wide'] = collect($loans)->where('year', (int)$year)->where('month', (int)$month)->where('day', (int)$day)->avg('price_per_wide');
                $report[$key]['price_per_tree'] = collect($loans)->where('year', (int)$year)->where('month', (int)$month)->where('day', (int)$day)->avg('price_per_tree');
            }
        }
    } else {
        $cur_month = Carbon::now()->format('m');
        $current = Carbon::create($params['year'], 1, 1);

        for ($key = 1; $key <= $cur_month; $key++) {
            $month = $current->format('m');
            $year = $current->format('Y');
            $day = $current->format('d');

            $count = collect($loans)->where('year', (int)$year)->where('month', (int)$month)->count();
            if ($count > 0) {
                $report[$key]['day'] = (int)$day;
                $report[$key]['month'] = (int)$month;
                $report[$key]['year'] = (int)$year;
                $report[$key]['period'] = $current->format('F Y');
                $report[$key]['count'] = collect($loans)->where('year', (int)$year)->where('month', (int)$month)->count();

            }
            $current->addMonth();
        }
    }

    return response()->json([
        'data' => collect($report)->values()->toArray(),
        'loans' => $loans
    ]);

//    $deliveries = \App\Models\DeliveryOrder::query()
//        ->with('customer')
//        ->when(Arr::exists($params, 'period_start'), function ($builder) use ($params) {
//            $builder->whereDate('delivery_date', '>=', $params['period_start']);
//        })
//        ->when(Arr::exists($params, 'period_end'), function ($builder) use ($params) {
//            $builder->whereDate('delivery_date', '<=', $params['period_end']);
//        })
//        ->when(Arr::exists($params, 'month'), function ($builder) use ($params) {
//            $builder->whereMonth('delivery_date', $params['month']);
//        })
//        ->when(Arr::exists($params, 'year'), function ($builder) use ($params) {
//            $builder->whereYear('delivery_date', $params['year']);
//        })
//        ->get()->map(function ($delivery) {
//            $type = $delivery->customer_type ? str($delivery->customer_type)->split('/\\\\/')->last() : '';
//            $customer = $delivery->customer?->name;
//            return [
//                'id' => $delivery->id,
//                'type' => $type,
//                'customer' => $customer,
//                'date' => $delivery->delivery_date->format('Y-m-d H:i:s'),
//                'day' => (int)$delivery->delivery_date->format('d'),
//                'month' =>(int)$delivery->delivery_date->format('m'),
//                'year' => (int)$delivery->delivery_date->format('Y'),
//                'net_weight' => $delivery->net_weight,
//                'net_price' => $delivery->net_price,
//                'margin' => $delivery->margin,
//                'gross_total' => $delivery->gross_total,
//                'net_total' => $delivery->net_total,
//            ];
//        })->collect();
//
//    $report = array();
//    if($params['type'] === 'Period'){
//        $report = $deliveries;
//    }
//    if($params['type'] === 'Monthly'){
//        $now = Carbon::create($params['year'], $params['month'], 01);
//        $periods = CarbonPeriod::create($now->startOfMonth()->toDateString(), $now->endOfMonth()->toDateString());
//
//        $temp = array();
//        foreach ($periods as $key => $period) {
//            $month = $period->format('m');
//            $year = $period->format('Y');
//            $day = $period->format('d');
//
//            $where = $deliveries->where('year', (int)$year)->where('month', (int)$month)->where('day', (int)$day);
//            $count = $where->count();
//            $net_weight = $where->sum('net_weight');
//            $net_price = $where->avg('net_price');
//            $margin = $where->avg('margin');
//            $gross_total = $where->sum('gross_total');
//            $net_total = $where->sum('net_total');
//
//
//            if($count > 0){
//                $temp[$key]['year'] = (int)$year;
//                $temp[$key]['month'] = (int)$month;
//                $temp[$key]['day'] = (int)$day;
//                $temp[$key]['period'] = $period->format('d F Y');
//                $temp[$key]['net_weight'] = $net_weight;
//                $temp[$key]['net_price'] = $net_price;
//                $temp[$key]['margin'] = $margin;
//                $temp[$key]['gross_total'] = $gross_total;
//                $temp[$key]['net_total'] = $net_total;
//                $temp[$key]['count'] = $count;
//            }
//
//        }
//        $report = collect($temp)->values();
//
//    }
//
//    if($params['type'] === 'Annual'){
//        $cur_month = Carbon::now()->format('m');
//        $current = Carbon::create($params['year'], 1, 1);
//
//        $temp = array();
//        for ($key = 1; $key <= $cur_month; $key++) {
//            $month = $current->format('m');
//            $year = $current->format('Y');
//            $day = $current->format('d');
//
//            $where = $deliveries->where('year', (int)$year)->where('month', (int)$month);
//            $count = $where->count();
//            $net_weight = $where->sum('net_weight');
//            $net_price = $where->avg('net_price');
//            $margin = $where->avg('margin');
//            $gross_total = $where->sum('gross_total');
//            $net_total = $where->sum('net_total');
//
//            $temp[$key]['day'] = $day;
//            $temp[$key]['month'] = $month;
//            $temp[$key]['year'] = $year;
//            $temp[$key]['period'] = $current->format('F Y');
//            $temp[$key]['net_weight'] = $net_weight;
//            $temp[$key]['net_price'] = $net_price;
//            $temp[$key]['margin'] = $margin;
//            $temp[$key]['gross_total'] = $gross_total;
//            $temp[$key]['net_total'] = $net_total;
//            $temp[$key]['count'] = $count;
//
//
//            $current->addMonth();
//        }
//        $report = collect($temp)->values();
//
//    }
//    return $report;

//    $params = array();
//    $params['type'] = 'Annual';
//    $params['land_id'] = [2];
//    $params['period_start'] = Carbon::create(2023, 01, 01);
//    $params['period_end'] = Carbon::create(2024, 02, 21);
//    $params['year'] = 2024;
//    $params['month'] = 02;

//    $lands = DB::table('plantation_details', 'pd')
//        ->join(DB::raw('plantations p'), 'pd.plantation_id', '=', 'p.id')
//        ->whereIn('pd.land_id', $params['land_id'])
//        ->when(Arr::exists($params, 'period_start'), function (\Illuminate\Database\Query\Builder $builder) use ($params) {
//            $builder->where('p.trade_date', '>=', $params['period_start']);
//        })
//        ->when(Arr::exists($params, 'period_end'), function (\Illuminate\Database\Query\Builder $builder) use ($params) {
//            $builder->where('p.trade_date', '<=', $params['period_end']);
//        })
//        ->when(Arr::exists($params, 'year'), function (\Illuminate\Database\Query\Builder $builder) use ($params) {
//            $builder->whereYear('p.trade_date', $params['year']);
//        })
//        ->when(Arr::exists($params, 'month'), function (\Illuminate\Database\Query\Builder $builder) use ($params) {
//            $builder->whereMonth('p.trade_date', $params['month']);
//        })
//        ->get()
//        ->map(function ($land) {
//            $trade_date = Carbon::parse($land->trade_date);
//            $weight_per_wide = $land->net_weight / $land->wide_total;
//            $weight_per_tree = $land->net_weight / $land->trees_total;
//            return [
//                'trade_date' => $trade_date->format('d F Y'),
//                'day' => (int)$trade_date->format('d'),
//                'month' => (int)$trade_date->format('m'),
//                'year' => (int)$trade_date->format('Y'),
//                'wide' => $land->wide,
//                'trees' => $land->trees,
//                'weight_per_wide' => $weight_per_wide,
//                'weight_per_tree' => $weight_per_tree,
//                'net_price' => $land->net_price,
//                'price_per_wide' => $land->net_price * $weight_per_wide,
//                'price_per_tree' => $land->net_price * $weight_per_tree,
//
//            ];
//        });
//
//    return response()->json([
//        'data' => $lands,
//    ]);
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
