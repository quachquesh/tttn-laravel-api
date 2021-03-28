<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionReply extends Model
{
    protected $fillable = [
        'member_id',
        'question_id',
        'content'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];
}
