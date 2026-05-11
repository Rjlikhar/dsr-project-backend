<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $table = 'dsr_groups';

    protected $fillable = [
        'name',
        'email',
    ];

    /**
     * A group can have many users
     */
    public function users()
    {
        return $this->hasMany(User::class, 'group_id');
    }
}
