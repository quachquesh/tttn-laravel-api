<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketGroup extends Model
{
    protected $fillable = [
        'member_id',
        'member_target',
        'ticket_type',
        'reason',
        'group_now',
        'group_going',
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
