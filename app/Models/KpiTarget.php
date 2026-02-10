<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KpiTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'promoter_user_id',
        'period_type',
        'period_start',
        'target_sales_amount',
        'target_engagements',
        'target_samplings',
        'target_premium_tier1',
        'target_premium_tier2',
    ];

    protected $casts = [
        'period_start' => 'date',
    ];

    public function promoter()
    {
        return $this->belongsTo(User::class, 'promoter_user_id');
    }
}
