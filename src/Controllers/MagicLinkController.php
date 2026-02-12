<?php

namespace MagicLink\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use MagicLink\Exceptions\LegacyActionFormatException;
use MagicLink\MagicLink;

class MagicLinkController extends Controller
{
    public function access($token)
    {
        try {
            return MagicLink::getMagicLinkByToken($token)->run();
        } catch (LegacyActionFormatException $e) {
            Log::error('Legacy action format detected for token: '.$token.'. Error: '.$e->getMessage());

            return response()->json(['message' => 'This magic link is no longer valid. Please request a new one.'], 419);
        }
    }
}
