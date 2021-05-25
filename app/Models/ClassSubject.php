<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassSubject extends Model
{
    protected $fillable = [
        'subject_id',
        'name',
        'description',
        'img',
        'isActive',
        'key',
        'semester',
        'maximum_group_member',
        'student_create_group'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];
}
