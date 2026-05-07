<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    /**
     * Mass assignable fields
     */
    protected $fillable = [
        'name',
        'receiver_email',
    ];

    /**
     * A role can have many users
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
