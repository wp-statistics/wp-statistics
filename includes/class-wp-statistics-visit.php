<?php

namespace WP_STATISTICS;

class Visit
{
    /**
     * Check Active Record Visits
     *
     * @return mixed
     */
    public static function active()
    {
        return (has_filter('wp_statistics_active_visits')) ? apply_filters('wp_statistics_active_visits', true) : Option::get('visits');
    }

    /**
     * Record Users Visit in DB
     */
    public static function record()
    {
        global $wpdb;

        // Check to see if we're a returning visitor.
        $result = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM %i ORDER BY ID DESC", DB::table('visit'))
        );


        // if we have not a Visitor in This Day then create new row or Update before row in DB
        if (is_null($result) || ($result->last_counter != TimeZone::getCurrentDate('Y-m-d'))) {
            $wpdb->query(
                $wpdb->prepare('INSERT INTO %s (last_visit, last_counter, visit) VALUES ( %s, %s, %d) ON DUPLICATE KEY UPDATE visit = visit + %s', DB::table('visit'),  TimeZone::getCurrentDate(), TimeZone::getCurrentDate('Y-m-d'), Visitor::getCoefficient(),  Visitor::getCoefficient())
            );
        } else {
            $wpdb->query(
                $wpdb->prepare(
                    'UPDATE %s SET `visit` = `visit` + %s, `last_visit` = %s WHERE `last_counter` = %s', 
                    DB::table('visit'), 
                    Visitor::getCoefficient(),
                    TimeZone::getCurrentDate(),
                    $result->last_counter
                )
            );
        }
    }
}
