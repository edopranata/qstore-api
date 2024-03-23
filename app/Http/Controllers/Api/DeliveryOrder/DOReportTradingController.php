<?php

namespace App\Http\Controllers\Api\DeliveryOrder;

use App\Http\Controllers\Controller;
use App\Models\DeliveryOrder;
use App\Models\Trading;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

class DOReportTradingController extends Controller
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
        return DeliveryOrder::query()
            ->whereHasMorph('customer', Trading::class)
            ->with('customer')
            ->when(Arr::exists($params, 'period_start'), function ($builder) use ($params) {
                $builder->whereDate('delivery_date', '>=', $params['period_start']);
            })
            ->when(Arr::exists($params, 'period_end'), function ($builder) use ($params) {
                $builder->whereDate('delivery_date', '<=', $params['period_end']);
            })
            ->when(Arr::exists($params, 'month'), function ($builder) use ($params) {
                $builder->whereMonth('delivery_date', $params['month']);
            })
            ->when(Arr::exists($params, 'year'), function ($builder) use ($params) {
                $builder->whereYear('delivery_date', $params['year']);
            })
            ->get()->map(function ($delivery) use ($params) {
                $type = $delivery->customer_type ? str($delivery->customer_type)->split('/\\\\/')->last() : '';
                $customer = $delivery->customer?->name;
                return [
                    'id' => $delivery->id,
                    'params' => $params,
                    'type' => $type,
                    'customer' => $customer,
                    'date' => $delivery->delivery_date->format('Y-m-d H:i:s'),
                    'day' => (int)$delivery->delivery_date->format('d'),
                    'month' =>(int)$delivery->delivery_date->format('m'),
                    'year' => (int)$delivery->delivery_date->format('Y'),
                    'net_weight' => $delivery->net_weight,
                    'net_price' => $delivery->net_price,
                    'margin' => $delivery->margin,
                    'gross_total' => $delivery->gross_total,
                    'net_total' => $delivery->net_total,
                ];
            })->collect();
    }
}
