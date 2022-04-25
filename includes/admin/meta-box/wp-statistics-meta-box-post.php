<?php

namespace WP_STATISTICS\MetaBox;

use WP_STATISTICS\Admin_Template;
use WP_STATISTICS\TimeZone;

class post
{
    /**
     * Get WordPress Post Chart Box
     *
     * @param array $args
     * @return array
     * @throws \Exception
     */
    public static function get($args = array())
    {

        // Set Not Publish Content
        $not_publish = array('content' => __('This post is not yet published.', 'wp-statistics'));

        // Check Isset POST ID
        if (!isset($args['ID']) || $args['ID'] < 1) {
            return $not_publish;
        }

        // Get Post Information
        $post = get_post($args['ID']);

        // Check Number Days
        $days = (isset($args['days']) ? $args['days'] : 20);

        // Check Not Publish Post
        if ($post->post_status != 'publish' && $post->post_status != 'private') {
            return $not_publish;
        }

        // Prepare Object
        $stats = $date = array();

        // Prepare Date time
        $days_list = TimeZone::getListDays(array('from' => TimeZone::getTimeAgo($days)));

        // Get List Of Days
        foreach ($days_list as $k => $v) {
            $date[] = $v['format'];
        }

        // Prepare State
        $post_type = \WP_STATISTICS\Pages::get_post_type($post->ID);

        // Get Number Search every Days
        foreach (array_keys($days_list) as $d) {
            $stats[] = wp_statistics_pages($d, '', $post->ID, null, null, $post_type);
        }

        // Push Basic Chart Data
        $response = array(
            'days'       => $days,
            'title'      => __('Number of Hits', 'wp-statistics'),
            'post_title' => get_the_title($post->ID),
            'date'       => $date,
            'state'      => $stats
        );

        // Check For No Data Meta Box
        if (count(array_filter($response['state'])) < 1) {
            $response['no_data'] = 1;
        }

        $response['visitors'] = apply_filters('wp_statistics_meta_box_post_visitors',
            Admin_Template::get_template(array('meta-box/pages-visitor-preview'), null, true),
            $post
        );

        // Response
        return $response;
    }


}