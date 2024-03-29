<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cost extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'trade_date'    => 'datetime:Y-m-d H:i:s',
        'created_at'    => 'datetime:Y-m-d H:i:s',
        'deleted_at'    => 'datetime:Y-m-d H:i:s',
        'updated_at'    => 'datetime:Y-m-d H:i:s',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function subject(): MorphTo
    {
        return $this->morphTo('subject')->withTrashed();
    }

    public function cost_type(): BelongsTo
    {
        return $this->belongsTo(CostType::class)->withTrashed();
    }

}
