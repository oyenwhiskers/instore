<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'is_active',
        'company_id',
        'brand_client_id',
        'unit_id',
    ];

    public function reportItems()
    {
        return $this->hasMany(HourlyReportItem::class);
    }

    public function locations()
    {
        return $this->belongsToMany(Location::class, 'location_product');
    }

    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_product')->withTimestamps();
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function brandClient()
    {
        return $this->belongsTo(BrandClient::class, 'brand_client_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
