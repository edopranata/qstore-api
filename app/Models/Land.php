<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Land extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'created_at'    => 'datetime:Y-m-d H:i:s',
        'deleted_at'    => 'datetime:Y-m-d H:i:s',
        'updated_at'    => 'datetime:Y-m-d H:i:s',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class)->withTrashed();
    }

    public function trades(): HasMany
    {
        return $this->hasMany(PlantationDetails::class);
    }

    public function plantation(): HasOneThrough
    {
        return $this->hasOneThrough(Plantation::class, PlantationDetails::class, 'land_id', 'id', 'id', 'plantation_id');
    }

    public function costs(): MorphMany
    {
        return $this->morphMany(Cost::class, 'subject');
    }
}
