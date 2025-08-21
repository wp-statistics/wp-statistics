<?php
namespace WP_Statistics\Service\ThirdParty\RankMath;

use WP_STATISTICS\DB;

class RankMath
{
    /**
     * Check if the Rank Math plugin is active.
     *
     * @return bool
     */
    public function isActive()
    {
        return is_plugin_active('seo-by-rank-math/rank-math.php');
    }

    /**
     * Check if the Rank Math pro is active.
     *
     * @return bool
     */
    public function isProVersion()
    {
        return is_plugin_active('seo-by-rank-math-pro/rank-math-pro.php');
    }

    /**
     * Retrieves the SEO score for a given post ID from the post meta table.
     *
     * @param int $postId
     * @return int
     */
    public function getSeoScore($postId)
    {
        return get_post_meta($postId, 'rank_math_seo_score', true) ?? 0;
    }

    /**
     * Retrieves the post data for a given post ID from the `rank_math_analytics_objects` table.
     *
     * @param int $postId The ID of the post to retrieve data for.
     * @return array
     */
    public function getPostData($postId)
    {
        $result = [];

        // Check if post ID is set
        if (empty($postId)) {
            return $result;
        }

        // Check if necessary classes exist
        if (!class_exists('\RankMath\Rest\Rest_Helper')) {
            return $result;
        }

        // Build route endpoint
        $route = '/' . \RankMath\Rest\Rest_Helper::BASE . '/an/post/' . $postId;

        $request  = new \WP_REST_Request('GET', $route);
        $response = rest_do_request($request);

        // Check if response is an error
        if ($response->is_error() || $response->get_status() !== 200) {
            return $result;
        }

        $data = $response->get_data();

        $result = [
            'page_score' => $data['page_score'] ?? null,
            'seo_score'  => $data['seo_score'] ?? null
        ];

        return $result;
    }
}