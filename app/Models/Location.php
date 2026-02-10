<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'country',
        'state',
        'district',
        'address',
        'geo_lat',
        'geo_lng',
        'geofence_radius',
        'status',
        'company_id',
        'brand_client_id',
    ];

    public function hourlyReports()
    {
        return $this->hasMany(HourlyReport::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'location_product');
    }

    public function assignments()
    {
        return $this->hasMany(PromoterAssignment::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function brandClient()
    {
        return $this->belongsTo(BrandClient::class, 'brand_client_id');
    }

    public function brandClients()
    {
        return $this->belongsToMany(BrandClient::class, 'brand_client_location');
    }
}
