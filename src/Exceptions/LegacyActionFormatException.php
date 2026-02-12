<?php

namespace MagicLink\Exceptions;

use RuntimeException;

class LegacyActionFormatException extends RuntimeException
{
    /**
     * Create a new LegacyActionFormatException instance.
     *
     * @param  \Exception|null  $previous
     * @return static
     */
    public static function detected($previous = null)
    {
        return new static(
            'Legacy action format detected. Please run the migration command to update your magic links: php artisan magiclink:migrate',
            0,
            $previous
        );
    }
}
