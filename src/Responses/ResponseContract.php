<?php

namespace MagicLink\Responses;

interface ResponseContract
{
    public function __invoke($options = []);
}
