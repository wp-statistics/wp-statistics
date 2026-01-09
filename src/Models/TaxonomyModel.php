<?php

namespace WP_Statistics\Models;

use WP_Statistics\Abstracts\BaseModel;

/**
 * Model class for taxonomy analytics.
 *
 * @deprecated 15.0.0 Use AnalyticsQueryHandler with taxonomy groupBy instead.
 * @see \WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler
 */
class TaxonomyModel extends BaseModel
{
    /**
     * Count terms.
     *
     * @deprecated 15.0.0 Use AnalyticsQueryHandler instead.
     *
     * @param array $args Query arguments.
     * @return int Always returns 0.
     */
    public function countTerms($args = [])
    {
        _deprecated_function(__METHOD__, '15.0.0', 'AnalyticsQueryHandler');
        return 0;
    }

    /**
     * Get taxonomies data.
     *
     * @deprecated 15.0.0 Use AnalyticsQueryHandler instead.
     *
     * @param array $args Query arguments.
     * @return array Always returns empty array.
     */
    public function getTaxonomiesData($args = [])
    {
        _deprecated_function(__METHOD__, '15.0.0', 'AnalyticsQueryHandler');
        return [];
    }

    /**
     * Get terms data.
     *
     * @deprecated 15.0.0 Use AnalyticsQueryHandler instead.
     *
     * @param array $args Query arguments.
     * @return array Always returns empty array.
     */
    public function getTermsData($args = [])
    {
        _deprecated_function(__METHOD__, '15.0.0', 'AnalyticsQueryHandler');
        return [];
    }

    /**
     * Get terms report data.
     *
     * @deprecated 15.0.0 Use AnalyticsQueryHandler instead.
     *
     * @param array $args Query arguments.
     * @return array Always returns empty array.
     */
    public function getTermsReportData($args = [])
    {
        _deprecated_function(__METHOD__, '15.0.0', 'AnalyticsQueryHandler');
        return [];
    }
}
