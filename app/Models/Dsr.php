<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dsr extends Model
{
    use HasFactory;
    protected $table = 'dsr';

    protected $fillable = [
        'user_id',
        'mentor_id',
        'code_reviewer_id',
        'work_date',
        'data',
        'status',
    ];

    protected $casts = [
        'data' => 'array',
        'work_date' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mentor()
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    public function codeReviewer()
    {
        return $this->belongsTo(User::class, 'code_reviewer_id');
    }
}
