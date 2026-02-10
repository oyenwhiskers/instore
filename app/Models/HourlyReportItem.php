<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HourlyReportItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'hourly_report_id',
        'product_id',
        'quantity_sold',
    ];

    public function report()
    {
        return $this->belongsTo(HourlyReport::class, 'hourly_report_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
