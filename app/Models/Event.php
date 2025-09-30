<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'location',
        'address',
        'cover_image',
        'images',
        'category',
        'price',
        'max_attendees',
        'organizer_id',
        'tags',
    ];

    protected $casts = [
        'tags' => 'array',
        'images' => 'array',
        'price' => 'decimal:2',
    ];

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attendees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_attendees');
    }

    public function likes(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_likes');
    }
}