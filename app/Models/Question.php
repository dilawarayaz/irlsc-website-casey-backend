<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = ['key', 'category', 'question', 'type', 'required', 'options'];

    protected $casts = [
        'required' => 'boolean',
        'options' => 'array',
    ];
}