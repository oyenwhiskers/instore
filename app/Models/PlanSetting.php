<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'active_plan_id',
    ];

    public function activePlan()
    {
        return $this->belongsTo(Plan::class, 'active_plan_id');
    }

    public static function current(): ?Plan
    {
        $setting = self::query()->with('activePlan')->first();

        return $setting?->activePlan;
    }
}
