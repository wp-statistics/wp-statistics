<?php

namespace WP_Statistics\Components;

use InvalidArgumentException;
use WP_STATISTICS\TimeZone;
use WP_STATISTICS\User;

class DateRange
{
    const USER_DATE_RANGE_META_KEY = 'wp_statistics_user_date_range';

    public static function getDefault()
    {
        return apply_filters('wp_statistics_default_user_date_range', self::get('30days'));
    }

    /**
     * Stores the given date range in the user's meta data.
     *
     * @param array $range An array containing 'from' and 'to' date strings.
     * @return void
     */
    public static function store($range)
    {
        $isFromValid = isset($range['from']) ? TimeZone::isValidDate($range['from']) : false;
        $isToValid   = isset($range['to']) ? TimeZone::isValidDate($range['to']) : false;

        if ($isFromValid && $isToValid) {
            User::saveMeta(self::USER_DATE_RANGE_META_KEY, $range);
        }
    }

    /**
     * Retrieves the date range stored in the user's meta data. Returns default date range if not set.
     *
     * @return array
     */
    public static function retrieve()
    {
        $storedRange = User::getMeta(self::USER_DATE_RANGE_META_KEY, true);

        return !empty($storedRange) ? $storedRange : self::getDefault();
    }

    /**
     * Gets a string and returns the specified date range.
     *
     * @param string $name The name of the date range.
     * @param bool $prevPeriod Whether to retrieve the previous period. Defaults to false.
     * @param bool $excludeToday Whether to exclude today from the date range. Defaults to false.
     * @return array The date range.
     */
    public static function get($name, $prevPeriod = false, $excludeToday = false)
    {
        $periods = self::getPeriods();

        if (!isset($periods[$name])) return [];

        $range = $periods[$name]['period'];

        if ($prevPeriod) {
            $range = $periods[$name]['prev_period'];
        }

        if ($excludeToday) {
            if ($name !== 'today' && $range['to'] === date('Y-m-d')) {
                $range['to'] = date('Y-m-d', strtotime('-1 day'));
            }
        }

        return $range;
    }

    /**
     * Returns an array of predefined date periods.
     *
     * Each date period is represented as an array with two keys: 'period' and 'prev_period'.
     * The 'period' key represents the actual date period of the given string
     * The 'prev_period' key represents the date range before to the current date period.
     *
     * @return array An array of predefined date periods.
     */
    public static function getPeriods()
    {
        return [
            'today'     => [
                'period'    => [
                    'from'  => date('Y-m-d'),
                    'to'    => date('Y-m-d')
                ],
                'prev_period' => [
                    'from'  => date('Y-m-d', strtotime('-1 day')),
                    'to'    => date('Y-m-d', strtotime('-1 day'))
                ],
            ],

            'yesterday' => [
                'period' => [
                    'from'  => date('Y-m-d', strtotime('-1 day')),
                    'to'    => date('Y-m-d', strtotime('-1 day')),
                ],
                'prev_period' => [
                    'from'  => date('Y-m-d', strtotime('-2 day')),
                    'to'    => date('Y-m-d', strtotime('-2 day')),
                ],
            ],

            'this_month' => [
                'period' => [
                    'from'  => date('Y-m-d', strtotime('first day of this month')),
                    'to'    => date('Y-m-d', strtotime('last day of this month')),
                ],
                'prev_period' => [
                    'from'  => date('Y-m-d', strtotime('first day of last month')),
                    'to'    => date('Y-m-d', strtotime('last day of last month')),
                ]
            ],

            'last_month' => [
                'period' => [
                    'from'  => date('Y-m-d', strtotime('first day of -1 month')),
                    'to'    => date('Y-m-d', strtotime('last day of -1 month')),
                ],
                'prev_period' => [
                    'from'  => date('Y-m-d', strtotime('first day of -2 months')),
                    'to'    => date('Y-m-d', strtotime('last day of -2 months')),
                ]
            ],

            '7days'     => [
                'period' => [
                    'from'  => date('Y-m-d', strtotime('-6 days')),
                    'to'    => date('Y-m-d')
                ],
                'prev_period' => [
                    'from'  => date('Y-m-d', strtotime('-13 days')),
                    'to'    => date('Y-m-d', strtotime('-7 days'))
                ]
            ],

            '14days'    => [
                'period' => [
                    'from'  => date('Y-m-d', strtotime('-13 days')),
                    'to'    => date('Y-m-d')
                ],
                'prev_period' => [
                    'from'  => date('Y-m-d', strtotime('-27 days')),
                    'to'    => date('Y-m-d', strtotime('-14 days'))
                ]
            ],

            '30days'    => [
                'period' => [
                    'from'  => date('Y-m-d', strtotime('-29 days')),
                    'to'    => date('Y-m-d')
                ],
                'prev_period' => [
                    'from'  => date('Y-m-d', strtotime('-59 days')),
                    'to'    => date('Y-m-d', strtotime('-30 days'))
                ]
            ],

            '90days'    => [
                'period' => [
                    'from'  => date('Y-m-d', strtotime('-89 days')),
                    'to'    => date('Y-m-d')
                ],
                'prev_period' => [
                    'from'  => date('Y-m-d', strtotime('-179 days')),
                    'to'    => date('Y-m-d', strtotime('-90 days'))
                ]
            ],

            '6months'  => [
                'period' => [
                    'from'  => date('Y-m-d', strtotime('-6 months')),
                    'to'    => date('Y-m-d'),
                ],
                'prev_period' => [
                    'from'  => date('Y-m-d', strtotime('-12 months')),
                    'to'    => date('Y-m-d', strtotime('-6 months')),
                ]
            ],

            '12months'  => [
                'period' => [
                    'from'  => date('Y-m-d', strtotime('-12 months')),
                    'to'    => date('Y-m-d'),
                ],
                'prev_period' => [
                    'from'  => date('Y-m-d', strtotime('-24 months')),
                    'to'    => date('Y-m-d', strtotime('-12 months')),
                ]
            ],

            'this_year' => [
                'period' => [
                    'from'  => date('Y-01-01'),
                    'to'    => date('Y-12-31'),
                ],
                'prev_period' => [
                    'from'  => date('Y-01-01', strtotime('-1 year')),
                    'to'    => date('Y-12-31', strtotime('-1 year')),
                ]
            ],

            'last_year' => [
                'period' => [
                    'from'  => date('Y-01-01', strtotime('-1 year')),
                    'to'    => date('Y-12-31', strtotime('-1 year')),
                ],
                'prev_period' => [
                    'from'  => date('Y-01-01', strtotime('-2 years')),
                    'to'    => date('Y-12-31', strtotime('-2 years')),
                ]
            ]
        ];
    }

    /**
     * Compare two dates.
     *
     * @param mixed $date1 A date string, array, or period name.
     * @param string $operator The operator to use for comparison.
     * @param mixed $date2 A date string, array, or period name.
     *
     * @return bool Whether the date ranges match the comparison operator.
     * @example 
     * DateRange::compare($date, '=', 'today') 
     * DateRange::compare($date, 'in', 'this_month') 
     * DateRange::compare($date1, '!=', $date2) 
     * DateRange::compare($date, 'in', ['from' => '2024-01-01', 'to' => '2024-01-31']) 
     */
    public static function compare($date1, $operator, $date2)
    {
        $range1 = self::resolveDate($date1);
        $range2 = self::resolveDate($date2);

        if (empty($range1) || empty($range2)) return false;

        $from1  = strtotime($range1['from']);
        $to1    = strtotime($range1['to']);

        $from2  = strtotime($range2['from']);
        $to2    = strtotime($range2['to']);

        switch ($operator) {
            case 'in':
            case 'between':
                return $from1 >= $from2 && $to1 <= $to2;

            case '<':
                return $to1 < $from2;

            case '<=':
                return $to1 <= $from2;

            case '>':
                return $from1 > $to2;

            case '>=':
                return $from1 >= $to2;

            case '!=':
            case 'not':
                return $from1 != $from2 || $to1 != $to2;

            case '=':
            case 'is':
            default:
                return $from1 == $from2 && $to1 == $to2;
        }
    }

    /**
     * Resolves the given date input to a 'from' and 'to' date array.
     *
     * @param mixed $date A date string, array, or period name.
     * @return array|bool An array containing 'from' and 'to' date strings. False if the date is invalid.
     */
    private static function resolveDate($date)
    {
        // If date is an array
        if (is_array($date)) {
            if (isset($date['from'], $date['to'])) {
                return $date;
            }

            if (count($date) == 2) {
                return ['from' => $date[0], 'to' => $date[1]];
            }
        }

        // If date is a simple date string
        if (TimeZone::isValidDate($date)) {
            return ['from' => $date, 'to' => $date];
        }

        // If date is a period name (string), get the range from the periods
        if (is_string($date)) {
            return self::get($date);
        }

        return false;
    }
}