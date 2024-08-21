<?php

namespace WP_Statistics\Components;

use WP_STATISTICS\TimeZone;
use WP_STATISTICS\User;
use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Request;

class DateRange
{
    public static $defaultFormat = 'Y-m-d';
    public static $defaultPeriod = '30days';
    const USER_DATE_RANGE_META_KEY = 'wp_statistics_user_date_filter';

    public static function validate($period)
    {
        if (empty($period)) {
            return false;
        }

        if (is_string($period) && !isset(self::getPeriods()[$period])) {
            return false;
        }

        if (is_array($period) && !isset($period['from'], $period['to'])) {
            return false;
        }

        return true;
    }

    /**
     * Stores the given date range in the user's meta data.
     *
     * @param array $range An array containing 'from' and 'to' date strings.
     */
    public static function store($range)
    {
        $period  = '';
        $periods = self::getPeriods();

        // If range is not set, or is invalid, use default
        if (!self::validate($range)) {
            $range = self::get(self::$defaultPeriod);
        }
        
        // If range is among the predefined periods, store the period key
        foreach ($periods as $key => $item) {
            if ($item['period']['from'] === $range['from'] && $item['period']['to'] === $range['to']) {
                $period = $key;
                break;
            }
        }

        // If it's custom range, store the range
        if (empty($period)) {
            $period = $range;
        }

        User::saveMeta(self::USER_DATE_RANGE_META_KEY, $period);
    }

    /**
     * Retrieves the period stored in the user's meta data, or from request object. 
     *
     * @return string|array Could be a period name like '30days' or an array containing 'from' and 'to' date strings.
     */
    public static function retrieve()
    {
        $result  = [];
        $period  = User::getMeta(self::USER_DATE_RANGE_META_KEY, true);

        if (!self::validate($period)) {
            $period = self::$defaultPeriod;
        }

        // Predefined date periods like '30days', 'this_month', etc...
        if (is_string($period)) {
            $periods = self::getPeriods();
            $result = [
                'type'  => 'period',
                'value' => $period,
                'range' => $periods[$period]['period']
            ];
        }

        // Custom date range store in usermeta like ['from' => '2024-01-01', 'to' => '2024-01-31']
        if (is_array($period)) {
            $result = [
                'type'  => 'custom',
                'value' => $period,
                'range' => $period
            ];
        }

        // Manual date range from request object
        if (Request::has('from') && Request::has('to')) {
            $result = [
                'type'  => 'manual',
                'value' => Request::getParams(['from', 'to']),
                'range' => Request::getParams(['from', 'to'])
            ];
        }

        return $result;
    }

    /**
     * Gets a string and returns the specified date range. By default returns the stored period in usermeta.
     *
     * @param string|bool $name The name of the date range. By default false.
     * @param bool $excludeToday Whether to exclude today from the date range. Defaults to false.
     * @return array The date range.
     */
    public static function get($period = false, $excludeToday = false)
    {
        $range = [];

        // If period is not provided, retrieve it
        if (!$period) {
            $storedRange = self::retrieve();
            $range       = $storedRange['range'];
        } else {
            $periods = self::getPeriods();

            if (isset($periods[$period])) {
                $range = $periods[$period]['period'];
            }
        }

        if (!empty($range) && $excludeToday) {
            if ($period !== 'today' && $range['to'] === date(self::$defaultFormat)) {
                $range['to'] = date(self::$defaultFormat, strtotime('-1 day'));
            }
        }

        return $range;
    }

    /**
     * Get the previous period based on a period name or custom date range. 
     * By default it returns result based on the stored period in usermeta.
     *
     * @param mixed $period The name of the period (e.g., '30days', 'this_month') or custom date range. 
     * @return array The previous period's date range.
     */
    public static function getPrevPeriod($period = false)
    {
        // If period is not provided, retrieve it
        if (!$period) {
            $period = self::retrieve();
            $period = $period['value'];
        }

        // Check if the period name exists in the predefined periods
        $periods = self::getPeriods();
        if (is_string($period)) {
            return $periods[$period]['prev_period'];
        }

        // If it's a custom date range, calculate the previous period dynamically
        if (is_array($period)) {
            $range  = self::resolveDate($period);
            $from   = strtotime($range['from']);
            $to     = strtotime($range['to']);

            // Calculate the number of days in the current period
            $daysInPeriod = ($to - $from) / (60 * 60 * 24);

            // Calculate the previous period dates
            $prevTo     = $from - 1;
            $prevFrom   = $prevTo - $daysInPeriod * 60 * 60 * 24;

            return [
                'from'  => date(self::$defaultFormat, $prevFrom),
                'to'    => date(self::$defaultFormat, $prevTo)
            ];
        }

        // Fallback to default period if period is invalid
        return self::getPrevPeriod(self::$defaultPeriod);
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
                    'from'  => date(self::$defaultFormat),
                    'to'    => date(self::$defaultFormat)
                ],
                'prev_period' => [
                    'from'  => date(self::$defaultFormat, strtotime('-1 day')),
                    'to'    => date(self::$defaultFormat, strtotime('-1 day'))
                ],
            ],

            'yesterday' => [
                'period' => [
                    'from'  => date(self::$defaultFormat, strtotime('-1 day')),
                    'to'    => date(self::$defaultFormat, strtotime('-1 day')),
                ],
                'prev_period' => [
                    'from'  => date(self::$defaultFormat, strtotime('-2 day')),
                    'to'    => date(self::$defaultFormat, strtotime('-2 day')),
                ],
            ],

            'this_week' => [
                'period' => [
                    'from' => date(self::$defaultFormat, strtotime(Helper::getStartOfWeek() . ' this week')),
                    'to'   => date(self::$defaultFormat, strtotime('next ' . Helper::getStartOfWeek()) - 1)
                ],
                'prev_period' => [
                    'from' => date(self::$defaultFormat, strtotime(Helper::getStartOfWeek() . ' last week')),
                    'to'   => date(self::$defaultFormat, strtotime(Helper::getStartOfWeek() . ' this week') - 1)
                ]
            ],

            'last_week' => [
                'period' => [
                    'from' => date(self::$defaultFormat, strtotime(Helper::getStartOfWeek() . ' last week')),
                    'to'   => date(self::$defaultFormat, strtotime(Helper::getStartOfWeek() . ' this week') - 1)
                ],
                'prev_period' => [
                    'from' => date(self::$defaultFormat, strtotime(Helper::getStartOfWeek() . ' -2 weeks')),
                    'to'   => date(self::$defaultFormat, strtotime(Helper::getStartOfWeek() . ' last week') - 1)
                ]
            ],

            'this_month' => [
                'period' => [
                    'from'  => date(self::$defaultFormat, strtotime('first day of this month')),
                    'to'    => date(self::$defaultFormat, strtotime('last day of this month')),
                ],
                'prev_period' => [
                    'from'  => date(self::$defaultFormat, strtotime('first day of last month')),
                    'to'    => date(self::$defaultFormat, strtotime('last day of last month')),
                ]
            ],

            'last_month' => [
                'period' => [
                    'from'  => date(self::$defaultFormat, strtotime('first day of -1 month')),
                    'to'    => date(self::$defaultFormat, strtotime('last day of -1 month')),
                ],
                'prev_period' => [
                    'from'  => date(self::$defaultFormat, strtotime('first day of -2 months')),
                    'to'    => date(self::$defaultFormat, strtotime('last day of -2 months')),
                ]
            ],

            '7days'     => [
                'period' => [
                    'from'  => date(self::$defaultFormat, strtotime('-6 days')),
                    'to'    => date(self::$defaultFormat)
                ],
                'prev_period' => [
                    'from'  => date(self::$defaultFormat, strtotime('-13 days')),
                    'to'    => date(self::$defaultFormat, strtotime('-7 days'))
                ]
            ],

            '14days'    => [
                'period' => [
                    'from'  => date(self::$defaultFormat, strtotime('-13 days')),
                    'to'    => date(self::$defaultFormat)
                ],
                'prev_period' => [
                    'from'  => date(self::$defaultFormat, strtotime('-27 days')),
                    'to'    => date(self::$defaultFormat, strtotime('-14 days'))
                ]
            ],

            '30days'    => [
                'period' => [
                    'from'  => date(self::$defaultFormat, strtotime('-29 days')),
                    'to'    => date(self::$defaultFormat)
                ],
                'prev_period' => [
                    'from'  => date(self::$defaultFormat, strtotime('-59 days')),
                    'to'    => date(self::$defaultFormat, strtotime('-30 days'))
                ]
            ],

            '90days'    => [
                'period' => [
                    'from'  => date(self::$defaultFormat, strtotime('-89 days')),
                    'to'    => date(self::$defaultFormat)
                ],
                'prev_period' => [
                    'from'  => date(self::$defaultFormat, strtotime('-179 days')),
                    'to'    => date(self::$defaultFormat, strtotime('-90 days'))
                ]
            ],

            '6months'  => [
                'period' => [
                    'from'  => date(self::$defaultFormat, strtotime('-6 months')),
                    'to'    => date(self::$defaultFormat),
                ],
                'prev_period' => [
                    'from'  => date(self::$defaultFormat, strtotime('-12 months')),
                    'to'    => date(self::$defaultFormat, strtotime('-6 months')),
                ]
            ],

            '12months'  => [
                'period' => [
                    'from'  => date(self::$defaultFormat, strtotime('-12 months')),
                    'to'    => date(self::$defaultFormat),
                ],
                'prev_period' => [
                    'from'  => date(self::$defaultFormat, strtotime('-24 months')),
                    'to'    => date(self::$defaultFormat, strtotime('-12 months')),
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