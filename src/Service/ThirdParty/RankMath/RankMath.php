<?php
namespace WP_Statistics\Service\ThirdParty\RankMath;

use WP_STATISTICS\DB;
use WP_Statistics\Utils\Query;

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

        // Check if rankmath objects table exist
        global $wpdb;
		if (!DB::ExistTable($wpdb->prefix . 'rank_math_analytics_objects')) {
			return $result;
		}

        // Get post by object id from objects table
        $data = Query::select('*')
            ->from('rank_math_analytics_objects')
            ->where('object_id', '=', $postId)
            ->getRow();

        $result = [
            'page_score' => $data->page_score ?? null,
            'seo_score'  => $data->seo_score ?? null
        ];

        return $result;
    }
}