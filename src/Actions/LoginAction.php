<?php

namespace MagicLink\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

class LoginAction extends ResponseAction
{
    protected $authIdentifier;

    protected $guard;

    protected bool $remember = false;

    /**
     * Constructor to action.
     *
     * @param  mixed  $httpResponse
     */
    public function __construct(Authenticatable $user, $httpResponse = null, ?string $guard = null)
    {
        $this->authIdentifier = $user->getAuthIdentifier();

        $this->response($httpResponse);

        $this->guard = $guard;
    }

    public function remember(bool $remember = true): self
    {
        $this->remember = $remember;

        return $this;
    }

    public function guard(string $guard): self
    {
        $this->guard = $guard;

        return $this;
    }

    /**
     * Execute Action.
     */
    public function run()
    {
        Auth::guard($this->guard)->loginUsingId($this->authIdentifier, $this->remember);

        return parent::run();
    }
}
