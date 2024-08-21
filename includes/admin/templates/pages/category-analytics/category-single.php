<?php

use WP_STATISTICS\Admin_Template;
use WP_STATISTICS\Helper;
use WP_Statistics\Components\View;

?>

<div class="metabox-holder wps-category-analytics">
    <div class="postbox-container" id="wps-postbox-container-1">
        <?php

        $args = [
            'title'          => esc_html__('Published Contents', 'wp-statistics'),
            'tooltip'        => esc_html__('The number of published content items with this term during the selected period, as well as the total number of published contents.', 'wp-statistics'),
            'avg'            => Helper::formatNumberWithUnit($data['overview']['published']['total']),
            'avg_title'      => esc_html__('Total', 'wp-statistics'),
            'selected'       => Helper::formatNumberWithUnit($data['overview']['published']['recent']),
            'selected_title' => esc_html__('Selected Period', 'wp-statistics')
        ];
        Admin_Template::get_template(['layout/category-analytics/overview-card'], $args);

        $args1 = [
            'title'          => esc_html__('Views', 'wp-statistics'),
            'tooltip'        => esc_html__('Total views of published content with this term in the selected period. Average per content is the total views divided by the number of published contents in that period.', 'wp-statistics'),
            'selected'       => Helper::formatNumberWithUnit($data['overview']['views']['recent']),
            'selected_title' => esc_html__('Selected Period', 'wp-statistics'),
            'avg'            => Helper::formatNumberWithUnit($data['overview']['views']['avg']),
            'avg_title'      => esc_html__('Avg. per Content', 'wp-statistics')
        ];
        Admin_Template::get_template(['layout/category-analytics/overview-card'], $args1);

        $args2 = [
            'title'          => esc_html__('Visitors', 'wp-statistics'),
            'tooltip'        => esc_html__('Total unique visitors for contents with this term during the selected period. The average per content is calculated by dividing the total visitors by the number of published contents in that period.', 'wp-statistics'),
            'selected'       => Helper::formatNumberWithUnit($data['overview']['visitors']['recent']),
            'selected_title' => esc_html__('Selected Period', 'wp-statistics'),
            'avg'            => Helper::formatNumberWithUnit($data['overview']['visitors']['avg']),
            'avg_title'      => esc_html__('Avg. per Content', 'wp-statistics')
        ];
        Admin_Template::get_template(['layout/category-analytics/overview-card'], $args2);

        $args3 = [
            'title'          => esc_html__('Words', 'wp-statistics'),
            'tooltip'        => esc_html__('Total word count and average per content based on published contents in the selected period. Also shows total word count and average per content for all time.', 'wp-statistics'),
            'selected'       => Helper::formatNumberWithUnit($data['overview']['words']['recent']),
            'selected_title' => esc_html__('Selected Period', 'wp-statistics'),
            'avg'            => Helper::formatNumberWithUnit($data['overview']['words']['avg']),
            'avg_title'      => esc_html__('Avg. per Content', 'wp-statistics'),
            'total'          => Helper::formatNumberWithUnit($data['overview']['words']['total']),
            'total_avg'      => Helper::formatNumberWithUnit($data['overview']['words']['total_avg'])
        ];
        Admin_Template::get_template(['layout/category-analytics/overview-card'], $args3);

        $args4 = [
            'title'          => esc_html__('Comments', 'wp-statistics'),
            'tooltip'        => esc_html__('Total comments and average per content based on published contents in the selected period. Also shows total comments and average per content for all time.', 'wp-statistics'),
            'selected'       => Helper::formatNumberWithUnit($data['overview']['comments']['recent'], 1),
            'selected_title' => esc_html__('Selected Period', 'wp-statistics'),
            'avg'            => Helper::formatNumberWithUnit($data['overview']['comments']['avg'], 1),
            'avg_title'      => esc_html__('Avg. per Content', 'wp-statistics'),
            'total'          => Helper::formatNumberWithUnit($data['overview']['comments']['total'], 1),
            'total_avg'      => Helper::formatNumberWithUnit($data['overview']['comments']['total_avg'], 1)
        ];
        Admin_Template::get_template(['layout/category-analytics/overview-card'], $args4);
        ?>

        <?php
        $operatingSystems = [
            'title'     => esc_html__('Operating Systems', 'wp-statistics'),
            'tooltip'   => esc_html__('Distribution of visitors by their operating systems.', 'wp-statistics'),
            'unique_id' => 'category_operating_systems'
        ];
        View::load("components/charts/horizontal-bar", $operatingSystems);

        $browsers = [
            'title'     => esc_html__('Browsers', 'wp-statistics'),
            'tooltip'   => esc_html__('Distribution of visitors by their web browsers.', 'wp-statistics'),
            'unique_id' => 'category_browsers'
        ];
        View::load("components/charts/horizontal-bar", $browsers);

        $deviceModels = [
            'title'     => esc_html__('Device Models', 'wp-statistics'),
            'tooltip'   => esc_html__('Distribution of visitors by their device models.', 'wp-statistics'),
            'unique_id' => 'category_device_models'
        ];
        View::load("components/charts/horizontal-bar", $deviceModels);

        $deviceUsage = [
            'title'     => esc_html__('Device Usage', 'wp-statistics'),
            'tooltip'   => esc_html__('Distribution of visitors by their device types.', 'wp-statistics'),
            'unique_id' => 'category_device_usage'
        ];
        View::load("components/charts/horizontal-bar", $deviceUsage);
        ?>
    </div>

    <div class="postbox-container" id="wps-postbox-container-2">
        <?php
        $performance = [
            'title'       => esc_html__('Performance', 'wp-statistics'),
            'tooltip'     => esc_html__('Shows the number of views, visitors, and published contents with this term over the last 15 days.', 'wp-statistics'),
            'type'        => 'categorySingle',
            'description' => esc_html__('Last 15 Days', 'wp-statistics'),
            'data'        => $data['performance']
        ];
        View::load("components/charts/performance", $performance);

        $topPick = [
            'title'   => esc_html__('Top Contents', 'wp-statistics'),
            'tooltip' => esc_html__('Shows the most popular, most commented, and most recent content with this term.', 'wp-statistics'),
            'data'    => $data['posts']
        ];
        Admin_Template::get_template(['layout/category-analytics/top-picks'], $topPick);

        $summary = [
            'title'   => esc_html__('Summary', 'wp-statistics'),
            'tooltip' => esc_html__('Summary of views and visitors over various time periods, including today, yesterday, the last 7 days, and the last 30 days.', 'wp-statistics'),
            'data'    => $data['visits_summary']
        ];
        Admin_Template::get_template(['layout/category-analytics/summary'], $summary);

        $topCountries = [
            'title'   => esc_html__('Top Countries', 'wp-statistics'),
            'tooltip' => esc_html__('The countries from which the most visitors are coming.', 'wp-statistics'),
            'data'    => $data['visitors_country']
        ];
        Admin_Template::get_template(['layout/category-analytics/top-countries'], $topCountries);

        $engines = [
            'title'     => esc_html__('Search Engines', 'wp-statistics'),
            'tooltip'   => esc_html__('Search engine traffic over the selected period.', 'wp-statistics'),
            'unique_id' => 'category-search-engines-chart'
        ];
        View::load("components/charts/search-engines", $engines);

        $topReferring = [
            'title'   => esc_html__('Top Referring', 'wp-statistics'),
            'tooltip' => esc_html__('The top referring domains.', 'wp-statistics'),
            'data'    => $data['referrers']
        ];
        Admin_Template::get_template(['layout/category-analytics/top-referring'], $topReferring);
        ?>
    </div>

</div>