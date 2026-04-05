<?php

namespace WP_Statistics\Service\Consent;

final class TrackingLevel
{
    public const FULL      = 'full';
    public const ANONYMOUS = 'anonymous';
    public const NONE      = 'none';

    private function __construct()
    {
    }

    public static function all(): array
    {
        return [
            'full'      => self::FULL,
            'anonymous' => self::ANONYMOUS,
            'none'      => self::NONE,
        ];
    }
}
