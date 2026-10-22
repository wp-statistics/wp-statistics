<?php

namespace WP_Statistics\Traits;

/**
 * Kept so code that still uses this trait keeps loading.
 *
 * The Tracker Debugger no longer stores PHP errors raised during hits, so
 * errorListener() does nothing. WordPress's debug log covers PHP errors.
 *
 * @deprecated 14.16.19
 */
trait ErrorLoggerTrait
{
    /**
     * @deprecated 14.16.19 No longer logs anything.
     *
     * @return void
     */
    protected static function errorListener()
    {
    }
}
