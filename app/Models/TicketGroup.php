<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketGroup extends Model
{
    protected $fillable = [
        'member_id',
        'group_id',
        'status',
        'ticket_type'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];
}
