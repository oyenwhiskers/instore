<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price_monthly',
        'max_products',
        'max_locations',
        'max_dashboards',
        'max_customers',
        'inventory_level',
        'features',
    ];

    protected $casts = [
        'features' => 'array',
    ];

    public function isUnlimited(?int $value): bool
    {
        return is_null($value);
    }
}
