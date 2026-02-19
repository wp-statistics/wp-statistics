<?php

declare(strict_types=1);

namespace WP_Statistics\Dependencies\MaxMind\WebService\Http;

/**
 * Interface Request.
 *
 * @internal
 */
interface Request
{
    public function __construct(string $url, array $options);

    public function post(string $body): array;

    public function get(): array;
}
