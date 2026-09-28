<?php

namespace MagicLink\Test\TestSupport;

use MagicLink\Controllers\MagicLinkController;

/**
 * Verifies the access() signature stays backward compatible: a subclass overriding it with
 * the original access($token) signature, and calling parent::access($token) directly, must
 * keep working. See ControllerWithoutMiddlewareTest / CustomControllerTest for the request
 * attribute contract itself.
 */
class CustomMagicLinkController extends MagicLinkController
{
    public function access($token)
    {
        return parent::access($token);
    }
}
