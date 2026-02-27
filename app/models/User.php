<?php

declare(strict_types=1);

namespace app\models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    /** @var string */
    protected $table = 'users';

    /** @var list<string> */
    protected $fillable = [
        'name',
        'email',
    ];

    /** @var list<string> */
    protected $hidden = [
        'password',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
