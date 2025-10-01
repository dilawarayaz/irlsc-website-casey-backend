<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class UserImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'image_path',
        'is_primary',
        'order',
    ];

    protected $appends = ['image_url'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Accessor for full URL
   public function getImageUrlAttribute()
{
    return asset('storage/' . $this->image_path);
}

}
