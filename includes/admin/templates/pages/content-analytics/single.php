<?php

use WP_STATISTICS\Admin_Template;
use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Request;
use WP_Statistics\Components\View;

$postType = get_post_type(Request::get('post_id'));
?>

<div class="metabox-holder wps-content-analytics">
    <div class="postbox-container" id="wps-postbox-container-1">
        <?php
        $args1 = [
            'title'          => esc_html__('Views', 'wp-statistics'),
            'tooltip'        => sprintf(esc_html__('Total views of this %s and views during the selected period.', 'wp-statistics'), strtolower($postType)),
            'avg'            => Helper::formatNumberWithUnit($data['overview']['views']['total']),
            'avg_title'      => esc_html__('Total', 'wp-statistics'),
            'selected'       => Helper::formatNumberWithUnit($data['overview']['views']['recent']),
            'selected_title' => esc_html__('Selected Period', 'wp-statistics')
        ];
        Admin_Template::get_template(['layout/content-analytics/overview-card'], $args1);

        $args2 = [
            'title'          => esc_html__('Visitors', 'wp-statistics'),
            'tooltip'        => sprintf(esc_html__('Total unique visitors to this %s and visitors during the selected period.', 'wp-statistics'), strtolower($postType)),
            'avg'            => Helper::formatNumberWithUnit($data['overview']['visitors']['total']),
            'avg_title'      => esc_html__('Total', 'wp-statistics'),
            'selected'       => Helper::formatNumberWithUnit($data['overview']['visitors']['recent']),
            'selected_title' => esc_html__('Selected Period', 'wp-statistics'),
        ];
        Admin_Template::get_template(['layout/content-analytics/overview-card'], $args2);

        $args3 = [
            'title'    => esc_html__('Words', 'wp-statistics'),
            'tooltip'  => sprintf(esc_html__('Total number of words in this %s.', 'wp-statistics'), strtolower($postType)),
            'selected' => Helper::formatNumberWithUnit($data['overview']['words']['total']),
        ];
        Admin_Template::get_template(['layout/content-analytics/overview-card'], $args3);

        if (post_type_supports($postType, 'comments')) {
            $args4 = [
                'title'    => esc_html__('Comments', 'wp-statistics'),
                'tooltip'  => sprintf(esc_html__('Total comments on this %s.', 'wp-statistics'), strtolower($postType)),
                'selected' => Helper::formatNumberWithUnit($data['overview']['comments']['total'], 1),
            ];
            Admin_Template::get_template(['layout/content-analytics/overview-card'], $args4);
        }

        $operatingSystems = [
            'title'     => esc_html__('Operating Systems', 'wp-statistics'),
            'tooltip'   => esc_html__('Distribution of visitors by their operating systems.', 'wp-statistics'),
            'unique_id' => 'content_operating_systems'
        ];
        View::load("components/charts/horizontal-bar", $operatingSystems);

        $browsers = [
            'title'     => esc_html__('Browsers', 'wp-statistics'),
            'tooltip'   => esc_html__('Distribution of visitors by their web browsers.', 'wp-statistics'),
            'unique_id' => 'content_browsers'
        ];
        View::load("components/charts/horizontal-bar", $browsers);

        $deviceModels = [
            'title'     => esc_html__('Device Models', 'wp-statistics'),
            'tooltip'   => esc_html__('Distribution of visitors by their device models.', 'wp-statistics'),
            'unique_id' => 'content_device_models'
        ];
        View::load("components/charts/horizontal-bar", $deviceModels);

        $deviceUsage = [
            'title'     => esc_html__('Device Usage', 'wp-statistics'),
            'tooltip'   => esc_html__('Distribution of visitors by their device types.', 'wp-statistics'),
            'unique_id' => 'content_device_usage'
        ];
        View::load("components/charts/horizontal-bar", $deviceUsage);
        ?>
    </div>

    <div class="postbox-container" id="wps-postbox-container-2">
        <?php
        $performance = [
            'title'       => esc_html__('Performance', 'wp-statistics'),
            'tooltip'     => esc_html__('A graph showing the number of views, visitors over the last 15 days.', 'wp-statistics'),
            'type'        => 'single',
            'description' => esc_html__('Last 15 Days', 'wp-statistics'),
            'data'        => $data['performance']
        ];
        View::load("components/charts/performance", $performance);

        $summary = [
            'title'   => esc_html__('Summary', 'wp-statistics'),
            'tooltip' => esc_html__('From today to last year, a breakdown of visitors and views.', 'wp-statistics'),
            'data'    => $data['visits_summary']
        ];
        Admin_Template::get_template(['layout/content-analytics/summary'], $summary);

        $topCountries = [
            'title'   => esc_html__('Top Countries', 'wp-statistics'),
            'tooltip' => esc_html__('The countries from which the most visitors are coming.', 'wp-statistics'),
            'data'    => $data['visitors_country']
        ];
        Admin_Template::get_template(['layout/content-analytics/top-countries'], $topCountries);

        $engines = [
            'title'     => esc_html__('Search Engines', 'wp-statistics'),
            'tooltip'   => esc_html__('Search engine traffic over the selected period.', 'wp-statistics'),
            'unique_id' => 'content-search-engines-chart'
        ];
        View::load("components/charts/search-engines", $engines);

        $topReferring = [
            'title'   => esc_html__('Top Referring', 'wp-statistics'),
            'tooltip' => esc_html__('The top referring domains.', 'wp-statistics'),
            'data'    => $data['referrers']
        ];
        Admin_Template::get_template(['layout/content-analytics/top-referring'], $topReferring);
        ?>
    </div>

</div>