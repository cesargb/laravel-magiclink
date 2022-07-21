<?php

namespace MagicLink\Test\TestSupport;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;

/**
 * @property string $email
 */
class User extends Model implements AuthorizableContract, AuthenticatableContract
{
    use Authorizable, Authenticatable;

    protected $fillable = ['email'];
    public $timestamps = false;
    protected $table = 'users';
}
