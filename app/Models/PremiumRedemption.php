<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PremiumRedemption extends Model
{
    use HasFactory;

    protected $fillable = [
        'hourly_report_id',
        'premium_id',
        'tier',
        'quantity',
    ];

    public function report()
    {
        return $this->belongsTo(HourlyReport::class, 'hourly_report_id');
    }

    public function premium()
    {
        return $this->belongsTo(Premium::class);
    }
}
