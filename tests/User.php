<?php

namespace Cesargb\MagicLink\Test;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Cesargb\MagicLink\Traits\HasMagicLink;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class User extends Model implements AuthorizableContract, AuthenticatableContract
{
    use Authorizable, Authenticatable, HasMagicLink;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['email'];
    public $timestamps = false;
    protected $table = 'users';
}
