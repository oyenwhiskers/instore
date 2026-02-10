<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'promoter_id',
        'ic_number',
        'company_id',
        'role',
        'phone',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function promoterProfile()
    {
        return $this->hasOne(PromoterProfile::class);
    }

    public function hourlyReports()
    {
        return $this->hasMany(HourlyReport::class, 'promoter_user_id');
    }

    public function kpiTargets()
    {
        return $this->hasMany(KpiTarget::class, 'promoter_user_id');
    }

    public function assignment()
    {
        return $this->hasOne(PromoterAssignment::class, 'user_id');
    }

    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_promoter')
            ->withPivot(['start_date', 'end_date', 'start_time', 'end_time', 'notes'])
            ->withTimestamps();
    }

    public function checkins()
    {
        return $this->hasMany(PromoterCheckin::class, 'user_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
