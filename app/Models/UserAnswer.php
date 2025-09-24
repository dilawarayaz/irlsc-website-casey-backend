<?php

// New Model: app/Models/UserAnswer.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAnswer extends Model
{
    protected $table = 'user_answers'; // Or 'questiondata' if you prefer

    protected $fillable = ['user_id', 'question_id', 'answer'];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}