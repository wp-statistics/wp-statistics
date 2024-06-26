<?php

use WP_STATISTICS\Admin_Template;

?>

<div class="metabox-holder wps-category-analytics">
    <div class="postbox-container" id="wps-postbox-container-1">
        <?php
        $args1 = [
            'title'   => sprintf(esc_html__('Published Contents', 'wp-statistics'), $postTypePlural),
            'tooltip' => esc_html__('Published Contents tooltip', 'wp-statistics'),
            'total'   => '1,256',
        ];
        Admin_Template::get_template(['layout/category-analytics/overview-card'], $args1);

        $args2 = [
            'title'     => esc_html__('Views', 'wp-statistics'),
            'tooltip'   => esc_html__('Views tooltip', 'wp-statistics'),
            'total'     => '21.2M',
            'avg'       => '183K',
            'avg_title' => esc_html__('Avg. per Content', 'wp-statistics')
        ];
        Admin_Template::get_template(['layout/category-analytics/overview-card'], $args2);

        $args3 = [
            'title'     => esc_html__('Visitors', 'wp-statistics'),
            'tooltip'   => esc_html__('Visitors tooltip', 'wp-statistics'),
            'total'     => '21.2M',
            'avg'       => '183K',
            'avg_title' => esc_html__('Avg. per Content', 'wp-statistics')
        ];
        Admin_Template::get_template(['layout/category-analytics/overview-card'], $args3);

        $args4 = [
            'title'     => esc_html__('Words', 'wp-statistics'),
            'tooltip'   => esc_html__('Words tooltip', 'wp-statistics'),
            'total'     => '21.2M',
            'avg'       => '183K',
            'avg_title' => esc_html__('Avg. per Content', 'wp-statistics')
        ];
        Admin_Template::get_template(['layout/category-analytics/overview-card'], $args4);

        $args5 = [
            'title'     => esc_html__('Comments', 'wp-statistics'),
            'tooltip'   => esc_html__('Comments tooltip', 'wp-statistics'),
            'total'     => '13',
            'avg'       => '12',
            'avg_title' => esc_html__('Avg. per Content', 'wp-statistics')
        ];
        Admin_Template::get_template(['layout/category-analytics/overview-card'], $args5);
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
            'type'        => 'category',
            'description' => esc_html__('Last 15 Days', 'wp-statistics'),
        ];
        Admin_Template::get_template(['layout/category-analytics/performance-chart'], $performance);

        $topPick = [
            'title'   => esc_html__('Top Contents', 'wp-statistics'),
            'tooltip' => esc_html__('Top Contents tooltip', 'wp-statistics')
        ];
        Admin_Template::get_template(['layout/category-analytics/top-picks'], $topPick);

        $topAuthors = [
            'title'   => esc_html__('Top Authors', 'wp-statistics'),
            'tooltip' => esc_html__('Top Authors tooltip', 'wp-statistics')
        ];
        Admin_Template::get_template(['layout/category-analytics/top-authors'], $topAuthors);

        $summary = [
            'title'   => esc_html__('Summary', 'wp-statistics'),
            'tooltip' => esc_html__('Summary tooltip', 'wp-statistics'),
        ];
        Admin_Template::get_template(['layout/category-analytics/summary'], $summary);

        $topCountries = [
            'title'   => esc_html__('Top Countries', 'wp-statistics'),
            'tooltip' => esc_html__('Top Countries tooltip', 'wp-statistics'),
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
        ];
        Admin_Template::get_template(['layout/category-analytics/top-referring'], $topReferring);
        ?>
    </div>

</div>