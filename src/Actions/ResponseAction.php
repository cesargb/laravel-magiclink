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

    protected $httpResponse;

    protected $guard;

    /**
     * Constructor to action.
     *
     * @param \Illuminate\Database\Eloquent\Model $user
     * @param mixed $httpResponse
     */
    public function __construct($httpResponse = null)
    {
        $this->httpResponse = $this->serializeResponse($httpResponse);
    }

    protected function serializeResponse($httpResponse)
    {
        $httpResponse = $this->formattedResponse($httpResponse);

        if ($httpResponse instanceof Closure) {
            return serialize(new SerializableClosure($httpResponse));
        }

        if ($httpResponse instanceof View) {
            return serialize($httpResponse->render());
        }

        return serialize($httpResponse);
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
        return $this->callResponse(unserialize($this->httpResponse));
    }

    protected function callResponse($httpResponse)
    {
        if (is_callable($httpResponse)) {
            return $httpResponse();
        }

        return $httpResponse;
    }
}
