<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Permission\Models\Permission;

class Menu extends Model
{
    use HasFactory;

    protected $guarded = [
        'id'
    ];

    public function children(): HasMany
    {
        return $this->hasMany(Menu::class);
    }

    public function permission(): HasOne
    {
        return $this->hasOne(Permission::class, 'name', 'name');
    }
}
