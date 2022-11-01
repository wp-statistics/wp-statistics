<?php

namespace WP_STATISTICS\MetaBox;

use WP_STATISTICS\TimeZone;

abstract class MetaBoxAbstract
{
    protected static $filterType;
    protected static $fromDate;
    protected static $toDate;
    protected static $startDate;
    protected static $endDate;
    protected static $dateFilter;
    protected static $countDays;
    protected static $daysList;

    abstract public static function get();

    public static function filterByDate($args)
    {
        if (!empty($args['from']) and !empty($args['to'])) {
            self::$filterType = 'between';
            self::$dateFilter = 'custom';
            self::$countDays  = TimeZone::getNumberDayBetween($args['from'], $args['to']);
            self::$fromDate   = $args['from'];
            self::$toDate     = $args['to'];
            self::$daysList   = TimeZone::getListDays([
                'from' => $args['from'],
                'to'   => $args['to']
            ]);
        } else {
            if (!empty($args['ago']) && array_key_exists($args['ago'], TimeZone::getDateFilters())) {
                self::$filterType = 'filter';
                self::$dateFilter = $args['ago'];
                $dateFilter       = TimeZone::calculateDateFilter(self::$dateFilter);
                self::$countDays  = TimeZone::getNumberDayBetween($dateFilter['from'], $dateFilter['to']);
                self::$fromDate   = $dateFilter['from'];
                self::$toDate     = $dateFilter['to'];
                self::$daysList   = TimeZone::getListDays([
                    'from' => $dateFilter['from'],
                    'to'   => $dateFilter['to']
                ]);
            } elseif (!empty($args['ago']) && is_numeric($args['ago']) and $args['ago'] > 0) {
                self::$filterType = 'ago';
                self::$countDays  = intval($args['ago']);
                self::$daysList   = TimeZone::getListDays([
                    'from' => TimeZone::getTimeAgo(self::$countDays)
                ]);
            } else {
                self::$filterType = 'ago';
                self::$countDays  = 30;
                self::$daysList   = TimeZone::getListDays([
                    'from' => TimeZone::getTimeAgo(self::$countDays)
                ]);
            }
        }
        $daysListKeys    = array_keys(self::$daysList);
        self::$startDate = reset($daysListKeys);
        self::$endDate   = end($daysListKeys);
        if (empty(self::$fromDate)) {
            self::$fromDate = self::$startDate;
        }
        if (empty(self::$toDate)) {
            self::$toDate = self::$endDate;
        }
    }

    public static function response($response)
    {
        $defaults = [
            'days'              => self::$countDays,
            'from'              => self::$startDate,
            'to'                => self::$endDate,
            'type'              => self::$filterType,
            'filter'            => self::$dateFilter,
            'filter_start_date' => wp_date('M j, Y', strtotime(self::$startDate)),
            'filter_end_date'   => wp_date('M j, Y', strtotime(self::$endDate)),
        ];
        $response = wp_parse_args($response, $defaults);
        return $response;
    }
}