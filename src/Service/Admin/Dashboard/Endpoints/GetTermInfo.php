<?php

namespace WP_Statistics\Service\Admin\Dashboard\Endpoints;

use WP_Statistics\Service\Admin\ReactApp\Contracts\PageActionInterface;
use WP_Statistics\Utils\Request;

/**
 * Get Term Info endpoint handler.
 *
 * Returns term metadata (name, slug, taxonomy) for a given term ID.
 * Used as a fallback when analytics data doesn't include term info.
 *
 * @since 15.0.0
 */
class GetTermInfo implements PageActionInterface
{
    protected const PREFIX = 'wp_statistics';

    public static function getActionName()
    {
        $instance = new static();
        return static::PREFIX . '_' . $instance->getEndpointName();
    }

    public function getEndpointName()
    {
        return 'get_term_info';
    }

    public function handleQuery()
    {
        $data    = Request::getRequestData();
        $termId  = isset($data['term_id']) ? absint($data['term_id']) : 0;

        if (empty($termId)) {
            return [
                'success' => false,
                'error'   => [
                    'code'    => 'missing_term_id',
                    'message' => __('Term ID is required.', 'wp-statistics'),
                ],
            ];
        }

        $term = get_term($termId);

        if (is_wp_error($term) || !$term) {
            return [
                'success' => false,
                'error'   => [
                    'code'    => 'term_not_found',
                    'message' => __('Term not found.', 'wp-statistics'),
                ],
            ];
        }

        return [
            'success' => true,
            'data'    => [
                'name'     => $term->name,
                'slug'     => $term->slug,
                'taxonomy' => $term->taxonomy,
            ],
        ];
    }
}
