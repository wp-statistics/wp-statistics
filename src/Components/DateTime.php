<?php

namespace WP_Statistics\Components;

class DateTime
{
    public static $defaultDateFormat = 'Y-m-d';
    public static $defaultTimeFormat = 'g:i a';

    /**
     * Gets the start of week string.
     *
     * This function returns the string value of the start of week day.
     *
     * @return string The start of week string (e.g. 'monday', 'tuesday', etc.)
     */
    public static function getStartOfWeek()
    {
        $startDay = intval(get_option('start_of_week', 0));

        switch ($startDay) {
            case 0:
                return 'sunday';
            case 1:
                return 'monday';
            case 2:
                return 'tuesday';
            case 3:
                return 'wednesday';
            case 4:
                return 'thursday';
            case 5:
                return 'friday';
            case 6:
                return 'saturday';
            default:
                return 'monday';
        }
    }
}