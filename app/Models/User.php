<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'name',
        'email',
        'mentor',
        'code_reviewer',
        'password',
        'role_id',
        'department_id',
        'app_password',
        'email_verified_at',
    ];

    /**
     * Hidden attributes
     */
    protected $hidden = [
        'password',
        'remember_token',
        'app_password',
    ];

    /**
     * Attribute casting
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * User belongs to role
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * User belongs to department
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * User has many DSRs
     */
    public function dsrs()
    {
        return $this->hasMany(Dsr::class);
    }

    /**
     * User as mentor in DSRs
     */
    public function mentorDsrs()
    {
        return $this->hasMany(Dsr::class, 'mentor_id');
    }

    /**
     * User as reviewer in DSRs
     */
    public function reviewerDsrs()
    {
        return $this->hasMany(Dsr::class, 'reviewer_id');
    }
}