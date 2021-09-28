<?php

namespace MagicLink\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

class LoginAction extends ResponseAction
{
    protected $user;

    protected $guard;

    /**
     * Constructor to action.
     *
     * @param mixed       $httpResponse
     * @param string|null $guard
     */
    public function __construct(Authenticatable $user, $httpResponse = null, string $guard = 'web')
    {
        $this->user = $user;

        $this->httpResponse = $this->serializeResponse($httpResponse);

        $this->guard = $guard;
    }

    /**
     * Execute Action.
     */
    public function run()
    {
        Auth::guard($this->guard)->login($this->user);

        return parent::run();
    }
}
