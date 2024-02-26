<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class TradingDetails extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'farmer_status' => 'datetime:Y-m-d H:i:s',
        'trade_date' => 'datetime:Y-m-d H:i:s',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'deleted_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function trade()
    {
        return $this->belongsTo(Trading::class)->withTrashed();
    }

    public function car(): HasOneThrough
    {
        return $this->hasOneThrough(Car::class, Trading::class, 'id', 'id', 'trading_id', 'car_id');

    }

    public function driver(): HasOneThrough
    {
        return $this->hasOneThrough(Driver::class, Trading::class, 'id', 'id', 'trading_id', 'driver_id');

    }
}
