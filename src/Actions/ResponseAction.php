<?php

namespace MagicLink\Actions;

use Closure;
use Illuminate\View\View;
use Opis\Closure\SerializableClosure;
use Symfony\Component\HttpFoundation\Response;

class ResponseAction implements ActionInterface
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
    public function __construct($response = null)
    {
        $this->response = $this->serializeResponse($response);
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
            return redirect(config('magiclink.url.redirect_default', '/'));
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
        return $this->callResponse(unserialize($this->response));
    }

    protected function callResponse($response)
    {
        if (is_callable($response)) {
            return $response();
        }

        return $response;
    }
}
