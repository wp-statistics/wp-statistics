<?php

namespace WP_Statistics;

class Helper
{
    /**
     * Is the given string a JSON object?
     *
     * @param string $string
     *
     * @return bool
     */
    public static function isJson($string)
    {
        json_decode($string);

        return json_last_error() === JSON_ERROR_NONE;
    }
}
