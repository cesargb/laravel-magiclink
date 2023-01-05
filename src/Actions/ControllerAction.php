<?php

namespace MagicLink\Actions;

class ControllerAction extends ActionAbstract
{
    private string $controllerClass;
    private ?string $method;

    public function __construct(string $controllerClass, ?string $method = null)
    {
        $this->controllerClass = $controllerClass;

        $this->method = $method;
    }

    /**
     * Execute Action.
     */
    public function run()
    {
        $controller = app()->make($this->controllerClass);

        if ($this->method) {
            return $controller->{$this->method}();
        }

        return $controller();
    }
}
