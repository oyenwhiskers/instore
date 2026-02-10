<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventStockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'product_id',
        'movement_type',
        'quantity',
        'notes',
        'created_by',
        'updated_by',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
