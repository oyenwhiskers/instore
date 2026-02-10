<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrandClient extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'created_by',
        'name',
        'status',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'brand_client_id');
    }

    public function locations()
    {
        return $this->belongsToMany(Location::class, 'brand_client_location');
    }
}
