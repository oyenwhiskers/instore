<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Premium extends Model
{
    use HasFactory;

    protected $table = 'premiums';

    protected $fillable = [
        'company_id',
        'created_by',
        'gift_name',
        'mechanic_description',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_premium')->withTimestamps();
    }
}
