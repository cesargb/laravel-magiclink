<?php

namespace MagicLink\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class LoginAction extends ResponseAction
{
    protected $authIdentifier;

    protected $guard;

    /**
     * Constructor to action.
     *
     * @param  mixed  $httpResponse
     * @param  string  $guard
     */
    public function __construct(Authenticatable $user, $httpResponse = null, string $guard = 'web')
    {
        $this->authIdentifier = $user->getAuthIdentifier();

        $this->httpResponse = $this->serializeResponse($httpResponse);

        $this->guard = $guard;
    }

    /**
     * Execute Action.
     */
    public function run()
    {
        Auth::guard($this->guard)->loginUsingId($this->authIdentifier);

        return parent::run();
    }
}
