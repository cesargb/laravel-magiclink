<?php

namespace Cesargb\MagicLink\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

class LoginAction extends ResponseAction implements ActionInterface
{
    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $user;

    protected $guard;

    /**
     * Constructor to action.
     *
     * @param Illuminate\Contracts\Auth\Authenticatable $user
     * @param null|string|\Symfony\Component\HttpFoundation\Response $response
     * @param null|string $guard
     */
    public function __construct(Authenticatable $user, $response = null, string $guard = 'web')
    {
        $this->user = $user;

        $this->response = $this->serializeResponse($response);

        $this->guard = $guard;
    }

    /**
     * Execute Action.
     */
    public function run()
    {
        Auth::guard($this->guard)->login($this->user);

        return $this->callResponse(unserialize($this->response));
    }
}
