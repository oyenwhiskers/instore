<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventPromoterKpi extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'promoter_user_id',
        'target_sales_amount',
        'target_engagements',
        'target_samplings',
        'target_premium_tier1',
        'target_premium_tier2',
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

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
