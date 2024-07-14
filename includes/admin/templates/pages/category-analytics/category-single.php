<?php

use WP_STATISTICS\Admin_Template;
use WP_STATISTICS\Helper;

?>

<div class="metabox-holder wps-category-analytics">
    <div class="postbox-container" id="wps-postbox-container-1">
        <?php

        $args = [
            'title'         => esc_html__('Published Contents', 'wp-statistics'),
            'tooltip'       => esc_html__('Published Contents tooltip', 'wp-statistics'),
            'avg'           => Helper::formatNumberWithUnit($data['overview']['published']['total']),
            'avg_title'     => esc_html__('Total', 'wp-statistics'),
            'selected'      => Helper::formatNumberWithUnit($data['overview']['published']['recent']),
            'selected_title'=> esc_html__('Selected Period', 'wp-statistics')
        ];
        Admin_Template::get_template(['layout/category-analytics/overview-card'], $args);

        $args1 = [
            'title'         => esc_html__('Views', 'wp-statistics'),
            'tooltip'       => esc_html__('Views tooltip', 'wp-statistics'),
            'selected'      => Helper::formatNumberWithUnit($data['overview']['views']['recent']),
            'selected_title'=> esc_html__('Selected Period', 'wp-statistics'),
            'avg'           => Helper::formatNumberWithUnit($data['overview']['views']['avg']),
            'avg_title'     => esc_html__('Avg. per Content', 'wp-statistics')
        ];
        Admin_Template::get_template(['layout/category-analytics/overview-card'], $args1);

        $args2 = [
            'title'         => esc_html__('Visitors', 'wp-statistics'),
            'tooltip'       => esc_html__('Visitors tooltip', 'wp-statistics'),
            'selected'      => Helper::formatNumberWithUnit($data['overview']['visitors']['recent']),
            'selected_title'=> esc_html__('Selected Period', 'wp-statistics'),
            'avg'           => Helper::formatNumberWithUnit($data['overview']['visitors']['avg']),
            'avg_title'     => esc_html__('Avg. per Content', 'wp-statistics')
        ];
        Admin_Template::get_template(['layout/category-analytics/overview-card'], $args2);

        $args3 = [
            'title'         => esc_html__('Words', 'wp-statistics'),
            'tooltip'       => esc_html__('Words tooltip', 'wp-statistics'),
            'selected'      => Helper::formatNumberWithUnit($data['overview']['words']['recent']),
            'selected_title'=> esc_html__('Selected Period', 'wp-statistics'),
            'avg'           => Helper::formatNumberWithUnit($data['overview']['words']['avg']),
            'avg_title'     => esc_html__('Avg. per Content', 'wp-statistics'),
            'total'         => Helper::formatNumberWithUnit($data['overview']['words']['total']),
            'total_avg'     => Helper::formatNumberWithUnit($data['overview']['words']['total_avg'])
        ];
        Admin_Template::get_template(['layout/category-analytics/overview-card'], $args3);

        $args4 = [
            'title'             => esc_html__('Comments', 'wp-statistics'),
            'tooltip'           => esc_html__('Comments tooltip', 'wp-statistics'),
            'selected'          => Helper::formatNumberWithUnit($data['overview']['comments']['recent'], 1),
            'selected_title'    => esc_html__('Selected Period', 'wp-statistics'),
            'avg'               => Helper::formatNumberWithUnit($data['overview']['comments']['avg'], 1),
            'avg_title'         => esc_html__('Avg. per Content', 'wp-statistics'),
            'total'             => Helper::formatNumberWithUnit($data['overview']['comments']['total'], 1),
            'total_avg'         => Helper::formatNumberWithUnit($data['overview']['comments']['total_avg'], 1)
        ];
        Admin_Template::get_template(['layout/category-analytics/overview-card'], $args4);
        ?>

        <?php
        $operatingSystems = [
            'title'     => esc_html__('Operating Systems', 'wp-statistics'),
            'tooltip'   => esc_html__('Operating Systems tooltip', 'wp-statistics'),
            'unique_id' => 'category_operating_systems'
        ];
        Admin_Template::get_template(['layout/category-analytics/pie-chart'], $operatingSystems);

        $browsers = [
            'title'     => esc_html__('Browsers', 'wp-statistics'),
            'tooltip'   => esc_html__('Browsers tooltip', 'wp-statistics'),
            'unique_id' => 'category_browsers'
        ];
        Admin_Template::get_template(['layout/category-analytics/pie-chart'], $browsers);

        $deviceModels = [
            'title'     => esc_html__('Device Models', 'wp-statistics'),
            'tooltip'   => esc_html__('Device Models tooltip', 'wp-statistics'),
            'unique_id' => 'category_device_models'
        ];
        Admin_Template::get_template(['layout/category-analytics/pie-chart'], $deviceModels);

        $deviceUsage = [
            'title'     => esc_html__('Device Usage', 'wp-statistics'),
            'tooltip'   => esc_html__('Device Usage tooltip', 'wp-statistics'),
            'unique_id' => 'category_device_usage'
        ];
        Admin_Template::get_template(['layout/category-analytics/pie-chart'], $deviceUsage);
        ?>
    </div>

    <div class="postbox-container" id="wps-postbox-container-2">
        <?php
        $performance = [
            'title'       => esc_html__('Performance', 'wp-statistics'),
            'tooltip'     => esc_html__('Performance tooltip', 'wp-statistics'),
            'type'        => 'categorySingle',
            'description' => esc_html__('Last 15 Days', 'wp-statistics'),
            'data'        => $data['performance']
        ];
        Admin_Template::get_template(['layout/category-analytics/performance-chart'], $performance);

        $topPick = [
            'title'     => esc_html__('Top Contents', 'wp-statistics'),
            'tooltip'   => esc_html__('Top Contents tooltip', 'wp-statistics'),
            'data'      => $data['posts']
        ];
        Admin_Template::get_template(['layout/category-analytics/top-picks'], $topPick);

        $summary = [
            'title'   => esc_html__('Summary', 'wp-statistics'),
            'tooltip' => esc_html__('Summary tooltip', 'wp-statistics'),
            'data'    => $data['visits_summary']
        ];
        Admin_Template::get_template(['layout/category-analytics/summary'], $summary);

        $topCountries = [
            'title'   => esc_html__('Top Countries', 'wp-statistics'),
            'tooltip' => esc_html__('Top Countries tooltip', 'wp-statistics'),
            'data'    => $data['visitors_country']
        ];
        Admin_Template::get_template(['layout/category-analytics/top-countries'], $topCountries);

        $engines = [
            'title'   => esc_html__('Search Engines', 'wp-statistics'),
            'tooltip' => esc_html__('Search Engines tooltip', 'wp-statistics'),
        ];
        Admin_Template::get_template(['layout/category-analytics/search-engines'], $engines);

        $topReferring = [
            'title'   => esc_html__('Top Referring', 'wp-statistics'),
            'tooltip' => esc_html__('Top Referring tooltip', 'wp-statistics'),
            'data'    => $data['referrers']
        ];
        Admin_Template::get_template(['layout/category-analytics/top-referring'], $topReferring);
        ?>
    </div>

</div>