<?php

namespace MagicLink\Controllers;

use Illuminate\Http\Request;
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

    public function access(Request $request, $token)
    {
        $magicLink = $request->attributes->get(MagiclinkMiddleware::REQUEST_ATTRIBUTE);

        if (! $magicLink instanceof MagicLink) {
            return $this->badResponse();
        }

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
}
