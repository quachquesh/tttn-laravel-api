<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotifyReply extends Model
{
    protected $fillable = [
        'notify_id',
        'reply_by_member',
        'reply_by_lecturer',
        'content'
    ];

    protected $hidden = [

    ];
}
