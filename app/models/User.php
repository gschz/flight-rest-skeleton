<?php

declare(strict_types=1);

namespace app\models;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static \Illuminate\Database\Eloquent\Collection<int, static> all(array|mixed $columns = ['*'])
 * @method static static create(array<string, mixed> $attributes = [])
 * @method static static|null find(mixed $id, array<int, string>|string $columns = ['*'])
 * @method static static findOrFail(mixed $id, array<int, string>|string $columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Builder<static> where(string|array<string, mixed> $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 */
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
