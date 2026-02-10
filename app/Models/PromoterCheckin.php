<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoterCheckin extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'location_id',
        'check_in_at',
        'latitude',
        'longitude',
        'image_path',
        'status',
    ];

    protected $casts = [
        'check_in_at' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
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
