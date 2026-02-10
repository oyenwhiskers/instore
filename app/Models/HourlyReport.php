<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HourlyReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'promoter_user_id',
        'location_id',
        'report_date',
        'report_hour',
        'total_sales_amount',
        'engagements_count',
        'samplings_count',
    ];

    protected $casts = [
        'report_date' => 'date',
    ];

    public function promoter()
    {
        return $this->belongsTo(User::class, 'promoter_user_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function items()
    {
        return $this->hasMany(HourlyReportItem::class);
    }

    public function premiums()
    {
        return $this->hasMany(PremiumRedemption::class);
    }
}
