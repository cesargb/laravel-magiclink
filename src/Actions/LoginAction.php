<?php

namespace Cesargb\MagicLink\Actions;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Opis\Closure\SerializableClosure;
use Symfony\Component\HttpFoundation\Response;

class LoginAction implements ActionInterface
{
    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $user;

    protected $response;

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

    protected function serializeResponse($response)
    {
        $response = $this->formattedResponse($response);

        if ($response instanceof Closure) {
            return serialize(new SerializableClosure($response));
        }

        if ($response instanceof View) {
            return serialize($response->render());
        }

        return serialize($response);
    }

    protected function formattedResponse($response)
    {
        if (is_null($response)) {
            return redirect('/');
        }

        if (is_callable($response)) {
            return Closure::fromCallable($response);
        }

        return $response;
    }

    /**
     * Execute Action.
     */
    public function run()
    {
        Auth::guard($this->guard)->login($this->user);

        return $this->callResponse(unserialize($this->response));
    }

    protected function callResponse($response)
    {
        if ($response instanceof Response) {
            return $response;
        }

        if (is_callable($response)) {
            return $response();
        }

        if (is_string($response)) {
            return $response;
        }
    }
}
