<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'department_id',
        'app_password'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'app_password'
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

    /**
     * User belongs to a role
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * User belongs to a department
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * DSR created by user (employee)
     */
    public function dsrs()
    {
        return $this->hasMany(Dsr::class, 'user_id');
    }

    /**
     * DSRs where user is mentor
     */
    public function mentoredDsrs()
    {
        return $this->hasMany(Dsr::class, 'mentor_id');
    }

    public function reviewedDsrs()
    {
        return $this->hasMany(Dsr::class, 'code_reviewer_id');
    }


    public function isAdmin(): bool
    {
        return $this->role?->role === 'admin';
    }

    public function isEmployee(): bool
    {
        return $this->role?->role === 'employee';
    }

    public function isMentor(): bool
    {
        return $this->role?->role === 'mentor';
    }

    public function isCodeReviewer(): bool
    {
        return $this->role?->role === 'code_reviewer';
    }
}
