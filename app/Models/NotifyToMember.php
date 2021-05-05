<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotifyToMember extends Model
{
    protected $fillable = [
        'member_id',
        'notify_id',
        'status'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password'
    ];
}
