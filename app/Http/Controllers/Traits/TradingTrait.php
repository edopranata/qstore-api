<?php

namespace App\Http\Controllers\Traits;

use App\Models\DeliveryOrder;
use App\Models\Setting;
use App\Models\Trading;
use Illuminate\Support\Arr;

trait TradingTrait
{
    private function update_trading(Trading $trade, array $data)
    {
        $detail = $trade->details()->get();

        $trade_date = Arr::exists($data, 'trade_date') ? $data['trade_date'] : $trade->trade_date;
        $customer_average_price = $detail->count() > 0 ? $detail->avg('price') : 0;
        $customer_total_price = $detail->count() > 0 ? $detail->sum('total') : 0;
        $customer_total_weight = $detail->count() > 0 ? $detail->sum('weight') : 0;
        $net_weight = Arr::exists($data, 'net_weight') ? $data['net_weight'] : $trade->net_weight;
        $net_price = Arr::exists($data, 'net_price') ? $data['net_price'] : $trade->net_price;
        $car_id = Arr::exists($data, 'car_id') ? $data['car_id'] : $trade->car_id;
        $driver_id = Arr::exists($data, 'driver_id') ? $data['driver_id'] : $trade->driver_id;
        $trade_cost = Arr::exists($data, 'trade_cost') ? $data['trade_cost'] : $trade->trade_cost;
        $car_fee = Arr::exists($data, 'car_fee') ? $data['car_fee'] : $trade->car_fee;
        $driver_fee = Arr::exists($data, 'driver_fee') ? $data['driver_fee'] : $trade->driver_fee;
        $car_transport = Arr::exists($data, 'car_transport') ? $data['car_transport'] : $trade->car_transport;
        $loader_fee = Arr::exists($data, 'loader_fee') ? $data['loader_fee'] : $trade->loader_fee;
        $driver_cost = $net_weight * $driver_fee;
        $car_cost = $net_weight * $car_fee;
        $loader_cost = $net_weight * $loader_fee;

        $cost_total = $driver_cost + $loader_cost + $car_cost + $trade_cost + $car_transport;

        $gross_total = $net_price * $net_weight;
        $net_income = $gross_total - ($cost_total + $customer_total_price);
        $margin = $net_price > 0 ? $net_price - $customer_average_price : 0;

        $data_update = [
            'car_id' => $car_id,
            'driver_id' => $driver_id,
            'trade_date' => $trade_date,
            'customer_average_price' => $customer_average_price,
            'customer_total_price' => $customer_total_price,
            'customer_total_weight' => $customer_total_weight,
            'margin' => $margin,
            'net_weight' => $net_weight,
            'net_price' => $net_price,
            'gross_total' => $gross_total,
            'trade_cost' => $trade_cost,
            'car_transport' => $car_transport,
            'driver_fee' => $driver_fee,
            'loader_fee' => $loader_fee,
            'car_fee' => $car_fee,
            'cost_total' => $cost_total,
            'net_income' => $net_income,
        ];

        $trade->update($data_update);

        if ($net_weight > 0 && $net_price > 0) {
            $setting = Setting::query()
                ->where('name', 'do_margin')->first();

            $do_margin = $setting['value'] ?? 0;

            DeliveryOrder::query()
                ->updateOrCreate(
                    [
                        'customer_id' => $trade->id,
                        'customer_type' => get_class(new Trading())
                    ],
                    [
                        'delivery_date' => $trade_date,
                        'user_id' => auth()->id(),
                        'net_weight' => $net_weight,
                        'net_price' => $net_price,
                        'margin' => $do_margin,
                        'gross_total' => $net_price * $net_weight,
                        'net_total' => $do_margin * $net_weight,
                    ]);
        }
    }


}
