<?php

namespace MagicLink\Test\TestSupport;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;

class CustomUserProvider implements UserProvider
{
    public function retrieveById($identifier)
    {
        return new CustomAutenticable($identifier);
    }

    public function retrieveByToken($identifier, $token)
    {
        print_r([__LINE__ => $identifier]);
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        print_r([__LINE__ => $user]);
    }

    public function retrieveByCredentials(array $credentials)
    {
        print_r([__LINE__ => $credentials]);

        //return User::where('username', $username)->first();
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        print_r([__LINE__ => $credentials]);
    }
}
