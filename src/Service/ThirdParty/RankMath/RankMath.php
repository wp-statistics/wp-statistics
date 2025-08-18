<?php
namespace WP_Statistics\Service\ThirdParty\RankMath;

class RankMath
{
    public function getPostData($postId)
    {
        $result = [];

        // Check if Rank Math free or pro is active
        if (!is_plugin_active('seo-by-rank-math/rank-math.php')) {
            return $result;
	    }

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
            'page_score' => $data['page_score'] ?? 0,
            'seo_score'  => $data['seo_score'] ?? 0
        ];

        return $result;
    }
}