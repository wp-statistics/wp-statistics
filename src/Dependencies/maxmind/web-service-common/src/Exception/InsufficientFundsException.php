<?php

declare(strict_types=1);

namespace WP_Statistics\Dependencies\MaxMind\Exception;

/**
 * Thrown when the account is out of credits.
 */
class InsufficientFundsException extends InvalidRequestException
{
}
