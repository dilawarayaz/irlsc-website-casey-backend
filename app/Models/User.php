<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // <-- add this

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable; // <-- add HasApiTokens

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'bio',
        'age',
        'location',
        'occupation',
        'education',
        'interests',
        'looking_for',
        'relationship_goals',
        'profile_type',
        'profile_picture',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];


    function profile()
    {
        return $this->hasOne(Profile::class);
    }
    public function images()
    {
        return $this->hasMany(UserImage::class, 'user_id');
    }
    public function answers()
    {
        return $this->hasMany(\App\Models\UserAnswer::class);
    }
    public function events()
    {
        return $this->hasMany(Event::class, 'organizer_id');
    }
    public function videos()
    {
        return $this->hasMany(\App\Models\UserVideo::class);
    }
    public function getProfilePictureAttribute($value)
    {
        if ($value) {
            return asset('storage/' . $value);
        }
        return null;
    }
}
