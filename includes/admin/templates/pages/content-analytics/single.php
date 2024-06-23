<?php 
use WP_STATISTICS\Admin_Template;
use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Request;

$postType = get_post_type(Request::get('post_id'));
?>

<div class="metabox-holder wps-content-analytics">
    <div class="postbox-container" id="wps-postbox-container-1">
        <?php
            $args1 = [
                'title'         => esc_html__('Views', 'wp-statistics'),
                'tooltip'       => esc_html__('Views tooltip', 'wp-statistics'),
                'icon_class'    => 'views',
                'total'         => Helper::formatNumberWithUnit($data['overview']['views']['total'])
            ];
            Admin_Template::get_template(['layout/content-analytics/overview-card'], $args1);

            $args2 = [
                'title'         => esc_html__('Visitors', 'wp-statistics'),
                'tooltip'       => esc_html__('Visitors tooltip', 'wp-statistics'),
                'icon_class'    => 'visitors',
                'total'         => Helper::formatNumberWithUnit($data['overview']['visitors']['total']),
            ];
            Admin_Template::get_template(['layout/content-analytics/overview-card'], $args2);

            $args3 = [
                'title'         => esc_html__('Words', 'wp-statistics'),
                'tooltip'       => esc_html__('Words tooltip', 'wp-statistics'),
                'icon_class'    => 'words',
                'total'         => Helper::formatNumberWithUnit($data['overview']['words']['total']),
            ];
            Admin_Template::get_template(['layout/content-analytics/overview-card'], $args3);

            if (post_type_supports($postType, 'comments')) {
                $args4 = [
                    'title'   => esc_html__('Comments', 'wp-statistics'),
                    'tooltip' => esc_html__('Comments tooltip', 'wp-statistics'),
                    'icon_class'   => 'comments',
                    'total'        => Helper::formatNumberWithUnit($data['overview']['comments']['total']),
                ];
                Admin_Template::get_template(['layout/content-analytics/overview-card'], $args4);
            }

            $operatingSystems = [
                'title'   => esc_html__('Operating Systems', 'wp-statistics'),
                'tooltip' => esc_html__('Operating Systems tooltip', 'wp-statistics'),
                'unique_id'    => 'content_operating_systems'
            ];
            Admin_Template::get_template(['layout/content-analytics/pie-chart'], $operatingSystems);

            $browsers = [
                'title'   => esc_html__('Browsers', 'wp-statistics'),
                'tooltip' => esc_html__('Browsers tooltip', 'wp-statistics'),
                'unique_id'    => 'content_browsers'
            ];
            Admin_Template::get_template(['layout/content-analytics/pie-chart'], $browsers);

            $deviceModels = [
                'title'   => esc_html__('Device Models', 'wp-statistics'),
                'tooltip' => esc_html__('Device Models tooltip', 'wp-statistics'),
                'unique_id'    => 'content_device_models'
            ];
            Admin_Template::get_template(['layout/content-analytics/pie-chart'], $deviceModels);

            $deviceUsage = [
                'title'   => esc_html__('Device Usage', 'wp-statistics'),
                'tooltip' => esc_html__('Device Usage tooltip', 'wp-statistics'),
                'unique_id'    => 'content_device_usage'
            ];
            Admin_Template::get_template(['layout/content-analytics/pie-chart'], $deviceUsage);
        ?>
    </div>

    <div class="postbox-container" id="wps-postbox-container-2">
        <?php
            $performance = [
                'title'       => esc_html__('Performance', 'wp-statistics'),
                'tooltip'     => esc_html__('Performance tooltip', 'wp-statistics'),
                'type'        => 'single',
                'description' => esc_html__('Last 15 Days', 'wp-statistics'),
                'data'        => ['views' => 0, 'visitors' => 0]
            ];
            Admin_Template::get_template(['layout/content-analytics/performance-chart'], $performance);

            $summary = [
                'title'   => esc_html__('Summary', 'wp-statistics'),
                'tooltip' => esc_html__('Summary tooltip', 'wp-statistics'),
                'data'    => $data['visits_summary']
            ];
            Admin_Template::get_template(['layout/content-analytics/summary'], $summary);

            $topCountries = [
                'title'   => esc_html__('Top Countries', 'wp-statistics'),
                'tooltip' => esc_html__('Top Countries tooltip', 'wp-statistics'),
            ];
            Admin_Template::get_template(['layout/content-analytics/top-countries'], $topCountries);

            $engines = [
                'title'   => esc_html__('Search Engines', 'wp-statistics'),
                'tooltip' => esc_html__('Search Engines tooltip', 'wp-statistics'),
            ];
            Admin_Template::get_template(['layout/content-analytics/search-engines'], $engines);

            $topReferring = [
                'title'   => esc_html__('Top Referring', 'wp-statistics'),
                'tooltip' => esc_html__('Top Referring tooltip', 'wp-statistics'),
            ];
            Admin_Template::get_template(['layout/content-analytics/top-referring'], $topReferring);
        ?>
    </div>

</div>