<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketRequest extends Model
{
    protected $fillable = [
        'user_send',
        'user_take',
        'title',
        'content',
        'status'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];
}
