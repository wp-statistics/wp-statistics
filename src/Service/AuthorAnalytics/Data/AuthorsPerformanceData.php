<?php 

namespace WP_Statistics\Service\AuthorAnalytics\Data;
use WP_STATISTICS\Helper;

class AuthorsPerformanceData
{

    /**
     * Generates SQL conditions based on the given arguments.
     *
     * @param array $args An array of arguments to generate the SQL conditions.
     * @return string The generated SQL conditions.
     */
    private static function generateSqlConditions($args)
    {
        global $wpdb;

        $sql = " WHERE post_status = 'publish'";

        // Post type condition
        if (!empty($args['post_type'])) {
            $sql .= $wpdb->prepare(" AND post_type = '%s'", $args['post_type']);
        } else {
            $postTypes = "'" . implode("', '", Helper::get_list_post_type()) . "'";
            $sql        .= " AND post_type IN ($postTypes)";
        }

        // Date condition
        if (!empty($args['from']) && !empty($args['to'])) {
            $sql .= $wpdb->prepare(' AND (Date(post_date) BETWEEN %s AND %s)', $args['from'], $args['to']);
        }

        return $sql;
    }

    /**
     * Counts the authors based on the given arguments.
     *
     * @param array $args An array of arguments to filter the count.
     * @return int The total number of distinct authors. Returns 0 if no authors are found.
     */
    public static function countAuthors($args = [])
    {
        global $wpdb;

        $sql    = "SELECT COUNT(DISTINCT post_author) FROM {$wpdb->posts}";
        $sql    .= self::generateSqlConditions($args);
        
        $result = $wpdb->get_var($sql);

        return $result ? $result : 0;
    }

    
    /**
     * Calculates the average number of posts per author based on the given arguments.
     *
     * @param array $args An array of arguments to filter the count.
     * @return float|int The average number of posts per author. Returns 0 if no authors are found.
     */
    public static function averagePostsPerAuthor($args = [])
    {
        global $wpdb;

        $sql    = "SELECT COUNT(ID) FROM {$wpdb->posts}";
        $sql    .= self::generateSqlConditions($args);
        
        $totalPosts     = $wpdb->get_var($sql);
        $totalAuthors   = self::countAuthors($args);

        return $totalPosts ? $totalPosts / $totalAuthors : 0;
    }
}