<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'video_path',
        'original_name',
        'mime_type',
        'size',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
