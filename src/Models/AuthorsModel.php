<?php

namespace WP_Statistics\Models;

use WP_Statistics\Abstracts\BaseModel;

/**
 * Model class for author analytics.
 *
 * @deprecated 15.0.0 Use AnalyticsQueryHandler with author groupBy instead.
 * @see \WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler
 */
class AuthorsModel extends BaseModel
{
    /**
     * Counts the authors based on the given arguments.
     *
     * @deprecated 15.0.0 Use AnalyticsQueryHandler instead.
     *
     * @param array $args An array of arguments to filter the count.
     * @return int Always returns 0.
     */
    public function countAuthors($args = [])
    {
        _deprecated_function(__METHOD__, '15.0.0', 'AnalyticsQueryHandler');
        return 0;
    }

    /**
     * Get top viewing authors.
     *
     * @deprecated 15.0.0 Use AnalyticsQueryHandler instead.
     *
     * @param array $args An array of arguments.
     * @return array Always returns empty array.
     */
    public function getTopViewingAuthors($args = [])
    {
        _deprecated_function(__METHOD__, '15.0.0', 'AnalyticsQueryHandler');
        return [];
    }

    /**
     * Get authors by post publishes.
     *
     * @deprecated 15.0.0 Use AnalyticsQueryHandler instead.
     *
     * @param array $args An array of arguments.
     * @return array Always returns empty array.
     */
    public function getAuthorsByPostPublishes($args = [])
    {
        _deprecated_function(__METHOD__, '15.0.0', 'AnalyticsQueryHandler');
        return [];
    }

    /**
     * Get authors by comments per post.
     *
     * @deprecated 15.0.0 Use AnalyticsQueryHandler instead.
     *
     * @param array $args An array of arguments.
     * @return array Always returns empty array.
     */
    public function getAuthorsByCommentsPerPost($args = [])
    {
        _deprecated_function(__METHOD__, '15.0.0', 'AnalyticsQueryHandler');
        return [];
    }

    /**
     * Get authors by views per post.
     *
     * @deprecated 15.0.0 Use AnalyticsQueryHandler instead.
     *
     * @param array $args An array of arguments.
     * @return array Always returns empty array.
     */
    public function getAuthorsByViewsPerPost($args = [])
    {
        _deprecated_function(__METHOD__, '15.0.0', 'AnalyticsQueryHandler');
        return [];
    }

    /**
     * Get authors by words per post.
     *
     * @deprecated 15.0.0 Use AnalyticsQueryHandler instead.
     *
     * @param array $args An array of arguments.
     * @return array Always returns empty array.
     */
    public function getAuthorsByWordsPerPost($args = [])
    {
        _deprecated_function(__METHOD__, '15.0.0', 'AnalyticsQueryHandler');
        return [];
    }

    /**
     * Get authors report data.
     *
     * @deprecated 15.0.0 Use AnalyticsQueryHandler instead.
     *
     * @param array $args An array of arguments.
     * @return array Always returns empty array.
     */
    public function getAuthorsReportData($args = [])
    {
        _deprecated_function(__METHOD__, '15.0.0', 'AnalyticsQueryHandler');
        return [];
    }

    /**
     * Get authors pages data.
     *
     * @deprecated 15.0.0 Use AnalyticsQueryHandler instead.
     *
     * @param array $args An array of arguments.
     * @return array Always returns empty array.
     */
    public function getAuthorsPagesData($args = [])
    {
        _deprecated_function(__METHOD__, '15.0.0', 'AnalyticsQueryHandler');
        return [];
    }
}
