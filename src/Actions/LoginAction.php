<?php

namespace MagicLink\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class LoginAction extends ResponseAction
{
    protected $user;

    protected $guard;

    /**
     * Constructor to action.
     *
     * @param  mixed  $httpResponse
     * @param  string  $guard
     */
    public function __construct(Authenticatable $user, $httpResponse = null, string $guard = 'web')
    {
        $this->storeUser($user);

        $this->httpResponse = $this->serializeResponse($httpResponse);

        $this->guard = $guard;
    }

    /**
     * Execute Action.
     */
    public function run()
    {
        $this->loggin($this->user, $this->guard);

        return parent::run();
    }

    private function storeUser($user)
    {
        $this->user = $user instanceof Model
            ? $this->getUserPrimaryKey($user)
            : $user;
    }

    private function getUserPrimaryKey($user)
    {
        $key = $user->getKeyName();

        return $user->$key;
    }

    private function loggin($user, $guard)
    {
        $auth = Auth::guard($guard);

        if ($user instanceof Authenticatable) {
            $auth->login($user);
        } else {
            $auth->loginUsingId($user);
        }
    }
}
