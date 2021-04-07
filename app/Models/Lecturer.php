<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Lecturer as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class Lecturer extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'email',
        'password',
        'role',
        'isActive',
        'first_name',
        'last_name',
        'sex',
        'birthday',
        'phone_number',
        'address',
        'create_by'
    ];

    protected $hidden = [
        'password'
    ];
}
