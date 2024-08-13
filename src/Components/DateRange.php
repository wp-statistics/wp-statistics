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
        return apply_filters('wp_statistics_default_date_range', self::get('30days'));
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
     * @throws InvalidArgumentException If the specified date range is invalid.
     * @return array The date range.
     */
    public static function get($name, $prevPeriod = false, $excludeToday = false)
    {
        $periods = self::getPeriods();

        if (!isset($periods[$name])) {
            throw new InvalidArgumentException(esc_html__('Invalid date range.', 'wp-statistics'));
        }

        $range = $periods[$name]['period'];

        if ($prevPeriod) {
            $range = $periods[$name]['prev_period'];
        }

        if ($excludeToday) {
            if ($name !== 'today' && $range['to'] === date('Y-m-d')) {
                $range['to'] = date('Y-m-d', '-1 day');
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
}