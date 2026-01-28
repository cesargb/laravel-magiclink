<?php

namespace MagicLink\Actions;

use Closure;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Laravel\SerializableClosure\SerializableClosure;
use Laravel\SerializableClosure\Serializers\Signed;
use MagicLink\MagicLink;
use MagicLink\Security\Serializable\Serializable;

class ResponseAction extends ActionAbstract
{
    protected $httpResponse;

    /**
     * Constructor to action.
     *
     * @param  mixed  $httpResponse
     */
    public function __construct($httpResponse = null)
    {
        $this->response($httpResponse);
    }

    public function response($response): self
    {
        $this->httpResponse = $this->serializeResponse($response);

        return $this;
    }

    public function redirect($response): self
    {
        $this->httpResponse = $this->serializeResponse($response);

        return $this;
    }

    protected function serializeResponse($httpResponse)
    {
        return Serializable::serialize($this->formattedResponse($httpResponse));
    }

    protected function formattedResponse($response)
    {
        if (is_null($response)) {
            return new RedirectResponse(
                config('magiclink.url.redirect_default', '/'),
                302
            );
        }

        if ($response instanceof RedirectResponse) {
            return new RedirectResponse(
                $response->getTargetUrl(),
                $response->getStatusCode()
            );
        }

        if (is_callable($response)) {
            return new SerializableClosure(Closure::fromCallable($response));
        }

        if ($response instanceof View) {
            return $response->render();
        }

        return $response;
    }

    /**
     * Execute Action.
     */
    public function run()
    {
        try {
            return $this->callResponse(Serializable::unserialize($this->httpResponse));
        } catch (Exception $e) {
        }

        return $this->callResponse(unserialize($this->httpResponse, ['allowed_classes' => [
            RedirectResponse::class,
            SerializableClosure::class,
            Signed::class,
        ]]));
    }

    protected function callResponse($httpResponse)
    {
        if (is_callable($httpResponse)) {
            return $httpResponse(MagicLink::find($this->magiclinkId));
        }

        return $httpResponse;
    }
}
