<?php

namespace MagicLink\Controllers;

use Carbon\Carbon;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use MagicLink\Exceptions\LegacyActionFormatException;
use MagicLink\MagicLink;
use MagicLink\Middlewares\MagiclinkMiddleware;
use MagicLink\Responses\Concerns\HandlesInvalidResponse;
use TypeError;

class MagicLinkController extends Controller
{
    use HandlesInvalidResponse;

    public function access($token)
    {
        $magicLink = request()->attributes->get(MagiclinkMiddleware::REQUEST_ATTRIBUTE);

        if (! $magicLink instanceof MagicLink) {
            return $this->runLegacyFallback($token);
        }

        return $this->run($magicLink, $token);
    }

    private function run(MagicLink $magicLink, $token)
    {
        try {
            return $magicLink->run();
        } catch (LegacyActionFormatException $e) {
            Log::error('Legacy action format detected for token: '.$token.'. Error: '.$e->getMessage());

            return response()->json([
                'message' => 'This magic link is no longer valid. Please request a new one.',
                'code' => 'legacy_action_format',
            ], 419);
        } catch (TypeError $e) {
            Log::error('Type error when executing magic link with token: '.$token.'. Error: '.$e->getMessage());

            return response()->json([
                'message' => 'This magic link contains unsupported data types. Please request a new one.',
                'code' => 'type_error',
            ], 419);
        }
    }

    private function runLegacyFallback($token)
    {
        // Compatibility path: the request didn't go through MagiclinkMiddleware (a custom
        // route, a replaced or extended middleware...). Deprecated since 2.29.0, this will be
        // removed in 3.0, where such requests will always be rejected. Prefer letting
        // MagiclinkMiddleware set the request attribute above.
        trigger_error(
            'MagicLinkController::access() was called without a MagicLink resolved by '
            .'MagiclinkMiddleware. This bypasses its access-code and visit-limit checks, and '
            .'will be rejected outright starting in 3.0. Make sure the route runs through '
            .'MagiclinkMiddleware, or set the '.MagiclinkMiddleware::class.'::REQUEST_ATTRIBUTE '
            .'request attribute yourself.',
            E_USER_DEPRECATED
        );

        $magicLink = MagicLink::getMagicLinkByToken($token);

        if (! $magicLink) {
            return $this->badResponse();
        }

        if ($magicLink->available_at !== null && Carbon::parse($magicLink->available_at)->isPast()) {
            return $this->badResponse();
        }

        return $this->run($magicLink, $token);
    }
}
