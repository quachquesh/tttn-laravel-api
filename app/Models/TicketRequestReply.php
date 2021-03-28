<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketRequestReply extends Model
{
    protected $fillable = [
        'ticket_id',
        'reply_by',
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
