<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventPromoterPremiumTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'promoter_user_id',
        'premium_id',
        'target_qty',
        'created_by',
        'updated_by',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function promoter()
    {
        return $this->belongsTo(User::class, 'promoter_user_id');
    }

    public function premium()
    {
        return $this->belongsTo(Premium::class);
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
