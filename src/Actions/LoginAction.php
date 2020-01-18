<?php

namespace Cesargb\MagicLink\Actions;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Opis\Closure\SerializableClosure;
use Symfony\Component\HttpFoundation\Response;

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
     * @param \Illuminate\Database\Eloquent\Model $user
     * @param string|\Symfony\Component\HttpFoundation\Response $response
     */
    public function __construct($user, $response = null, $guard = 'web')
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
