<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;

class Menu extends Model
{
    use HasFactory;

    protected $guarded = [
        'id'
    ];
    public function children()
    {
        return $this->hasMany(Menu::class);
    }

    public function permission()
    {
        return $this->hasOne(Permission::class, 'name', 'name');
    }
}
