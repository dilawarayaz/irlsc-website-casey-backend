<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatchRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'relationship_type',
        'min_age',
        'max_age',
        'status',
        'handled_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
