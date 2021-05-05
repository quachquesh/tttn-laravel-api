<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotifyAttach extends Model
{
    protected $fillable = [
        'notify_id',
        'file_name',
        'link'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];
}
