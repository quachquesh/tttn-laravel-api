<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupMember extends Model
{
    protected $fillable = [
        'member_id',
        'group_id',
        'role'
    ];

    protected $hidden = [

    ];
}
