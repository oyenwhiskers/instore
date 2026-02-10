<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoterAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'location_id',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function promoter()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
