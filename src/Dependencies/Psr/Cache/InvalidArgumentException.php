<?php

namespace WP_Statistics\Dependencies\Psr\Cache;

/**
 * Exception interface for invalid cache arguments.
 *
 * Any time an invalid argument is passed into a method it must throw an
 * exception class which implements WP_Statistics\Dependencies\Psr\Cache\InvalidArgumentException.
 */
interface InvalidArgumentException extends CacheException
{
}
