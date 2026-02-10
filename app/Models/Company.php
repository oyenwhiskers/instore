<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
        'billing_cycle',
        'subscription_ends_at',
        'plan_id',
    ];

    protected $casts = [
        'subscription_ends_at' => 'date',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function brandClients()
    {
        return $this->hasMany(BrandClient::class);
    }

    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}
