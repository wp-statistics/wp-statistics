<?php

namespace WP_Statistics\Utils;

class Math
{
    /**
     * Calculate percentage change between a previous and current value.
     *
     * @param int|float $previous
     * @param int|float $current
     * @param int       $decimals Number of decimals to round to.
     * @param string    $zeroPreviousBehavior Behavior when previous is 0:
     *                                       - 'zero'    => always return 0
     *                                       - 'hundred' => return 100 if current > 0, else 0
     *
     * @return float
     */
    public static function percentageChange($previous, $current, $decimals = 0, $zeroPreviousBehavior = 'zero')
    {
        $previous = (float) $previous;
        $current  = (float) $current;

        if ($previous == 0.0) {
            if ($zeroPreviousBehavior === 'hundred') {
                return $current > 0.0 ? 100.0 : 0.0;
            }

            return 0.0;
        }

        if ($current == $previous) {
            return 0.0;
        }

        return round((($current - $previous) / $previous) * 100, (int) $decimals);
    }
}

