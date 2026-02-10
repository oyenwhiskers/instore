<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'location_id',
        'created_by',
        'updated_by',
        'name',
        'status',
        'start_date',
        'end_date',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function promoters()
    {
        return $this->belongsToMany(User::class, 'event_promoter')
            ->withPivot(['start_date', 'end_date', 'start_time', 'end_time', 'notes'])
            ->withTimestamps();
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'event_product')
            ->withPivot(['unit_price'])
            ->withTimestamps();
    }

    public function premiums()
    {
        return $this->belongsToMany(Premium::class, 'event_premium')->withTimestamps();
    }

    public function stockMovements()
    {
        return $this->hasMany(EventStockMovement::class);
    }

    public function promoterKpis()
    {
        return $this->hasMany(EventPromoterKpi::class);
    }
}
