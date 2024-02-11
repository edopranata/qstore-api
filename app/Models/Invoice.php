<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'deleted_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function customer(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(InvoiceDetail::class);
    }

    public function detail_do(): HasManyThrough
    {
        return $this->hasManyThrough(DeliveryOrder::class, InvoiceDetail::class, 'invoice_id', 'id', 'id', 'delivery_order_id');
    }

    public function detail_plantation(): HasManyThrough
    {
        return $this->hasManyThrough(Plantation::class, InvoiceDetail::class, 'invoice_id', 'id', 'id', 'plantation_id');

    }
    public function loan(): HasOne
    {
        return $this->hasOne(InvoiceLoan::class);
    }

    public function loan_details(): HasOneThrough
    {
        return $this->hasOneThrough(LoanDetails::class, InvoiceLoan::class, 'invoice_id', 'id', 'id', 'loan_details_id');
    }


}
