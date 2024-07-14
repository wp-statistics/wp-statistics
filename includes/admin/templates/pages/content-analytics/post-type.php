<?php
use WP_STATISTICS\Admin_Template;
use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Request;

$postType         = Request::get('tab', 'post');
$postTypeSingular = Helper::getPostTypeName($postType, true);
$postTypePlural   = Helper::getPostTypeName($postType);
?>

<div class="metabox-holder wps-content-analytics">
    <div class="postbox-container" id="wps-postbox-container-1">
        <?php
        $args = [
            'title'         => sprintf(esc_html__('Published %s', 'wp-statistics'), $postTypePlural),
            'tooltip'       => sprintf(esc_html__('The total number of %s published in the selected period.', 'wp-statistics'), strtolower($postTypePlural)),
            'selected'      => Helper::formatNumberWithUnit($data['overview']['published']['recent']),
            'selected_title'=> esc_html__('Selected Period', 'wp-statistics'),
            'avg'           => Helper::formatNumberWithUnit($data['overview']['published']['total']),
            'avg_title'     => esc_html__('Total', 'wp-statistics')
        ];
        Admin_Template::get_template(['layout/content-analytics/overview-card'], $args);

        $args = [
            'title'         => esc_html__('Views', 'wp-statistics'),
            'tooltip'       => sprintf(esc_html__('Total views of your %s in the selected period. Avg per  %s is the total views divided by the number of published %s in that period.', 'wp-statistics'), strtolower($postTypePlural), strtolower($postTypeSingular), strtolower($postTypePlural)),
            'selected'      => Helper::formatNumberWithUnit($data['overview']['views']['recent']),
            'selected_title'=> esc_html__('Selected Period', 'wp-statistics'),
            'avg'           => Helper::formatNumberWithUnit($data['overview']['views']['avg']),
            'avg_title'     => sprintf(esc_html__('Avg. per %s', 'wp-statistics'), $postTypeSingular),
        ];
        Admin_Template::get_template(['layout/content-analytics/overview-card'], $args);

        $args = [
            'title'      => esc_html__('Visitors', 'wp-statistics'),
            'tooltip'    => sprintf(esc_html__('Total unique visitors in the selected period. Avg per %s is the total visitors divided by the number of published %s in that period.', 'wp-statistics'), strtolower($postTypeSingular), strtolower($postTypePlural)),
            'selected'      => Helper::formatNumberWithUnit($data['overview']['visitors']['recent']),
            'selected_title'=> esc_html__('Selected Period', 'wp-statistics'),
            'avg'        => Helper::formatNumberWithUnit($data['overview']['visitors']['avg']),
            'avg_title'  => sprintf(esc_html__('Avg. per %s', 'wp-statistics'), $postTypeSingular),
        ];
        Admin_Template::get_template(['layout/content-analytics/overview-card'], $args);

        $args = [
            'title'         => esc_html__('Words', 'wp-statistics'),
            'tooltip'       => esc_html__('Words tooltip', 'wp-statistics'),
            'selected'      => Helper::formatNumberWithUnit($data['overview']['words']['recent']),
            'selected_title'=> esc_html__('Selected Period', 'wp-statistics'),
            'avg'           => Helper::formatNumberWithUnit($data['overview']['words']['avg']),
            'avg_title'     => esc_html__('Avg. per Content', 'wp-statistics'),
            'total'         => Helper::formatNumberWithUnit($data['overview']['words']['total']),
            'total_avg'     => Helper::formatNumberWithUnit($data['overview']['words']['total_avg'])
        ];
        Admin_Template::get_template(['layout/content-analytics/overview-card'], $args);

        if (post_type_supports($postType, 'comments')) {
            $args = [
                'title'             => esc_html__('Comments', 'wp-statistics'),
                'tooltip'           => esc_html__('Comments tooltip', 'wp-statistics'),
                'selected'          => Helper::formatNumberWithUnit($data['overview']['comments']['recent']),
                'selected_title'    => esc_html__('Selected Period', 'wp-statistics'),
                'avg'               => Helper::formatNumberWithUnit($data['overview']['comments']['avg']),
                'avg_title'         => esc_html__('Avg. per Content', 'wp-statistics'),
                'total'             => Helper::formatNumberWithUnit($data['overview']['comments']['total']),
                'total_avg'         => Helper::formatNumberWithUnit($data['overview']['comments']['total_avg'])
            ];
            Admin_Template::get_template(['layout/content-analytics/overview-card'], $args);
        }
        ?>

        <?php

        $categories = [
            'title'      => esc_html__('Top Categories', 'wp-statistics'),
            'tooltip'    => sprintf(esc_html__('The most popular categories by number of published posts %s.', 'wp-statistics'), strtolower($postTypePlural)),
            'taxonomies' => $data['taxonomies']
        ];
        Admin_Template::get_template(['layout/content-analytics/top-categories'], $categories);

        $operatingSystems = [
            'title'     => esc_html__('Operating Systems', 'wp-statistics'),
            'tooltip'   => esc_html__('Distribution of visitors by their operating systems.', 'wp-statistics'),
            'unique_id' => 'content_operating_systems'
        ];
        Admin_Template::get_template(['layout/content-analytics/pie-chart'], $operatingSystems);

        $browsers = [
            'title'     => esc_html__('Browsers', 'wp-statistics'),
            'tooltip'   => esc_html__('Distribution of visitors by their web browsers.', 'wp-statistics'),
            'unique_id' => 'content_browsers'
        ];
        Admin_Template::get_template(['layout/content-analytics/pie-chart'], $browsers);

        $deviceModels = [
            'title'     => esc_html__('Device Models', 'wp-statistics'),
            'tooltip'   => esc_html__('Distribution of visitors by their device models.', 'wp-statistics'),
            'unique_id' => 'content_device_models'
        ];
        Admin_Template::get_template(['layout/content-analytics/pie-chart'], $deviceModels);

        $deviceUsage = [
            'title'     => esc_html__('Device Usage', 'wp-statistics'),
            'tooltip'   => esc_html__('Distribution of visitors by their device types.', 'wp-statistics'),
            'unique_id' => 'content_device_usage'
        ];
        Admin_Template::get_template(['layout/content-analytics/pie-chart'], $deviceUsage);
        ?>
    </div>

    <div class="postbox-container" id="wps-postbox-container-2">
        <?php
        $performance = [
            'title'       => esc_html__('Performance', 'wp-statistics'),
            'tooltip'     => esc_html__('A graph showing the number of views, visitors, and published posts over the last 15 days.', 'wp-statistics'),
            'type'        => 'post-type',
            'description' => esc_html__('Last 15 Days', 'wp-statistics'),
            'data'        => $data['performance']
        ];
        Admin_Template::get_template(['layout/content-analytics/performance-chart'], $performance);

        $topPages = [
            'title'   => sprintf(esc_html__('Top %s', 'wp-statistics'), $postTypePlural),
            'tooltip' => sprintf(esc_html__('Displays the most popular, most commented, and most recent  %s in the selected period.', 'wp-statistics'), strtolower($postTypePlural)),
            'data'    => $data['posts']
        ];
        Admin_Template::get_template(['layout/content-analytics/top-picks'], $topPages);

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
            'title'   => esc_html__('Search Engines', 'wp-statistics'),
            'tooltip' => esc_html__('Search engine traffic over the selected period.', 'wp-statistics'),
        ];
        Admin_Template::get_template(['layout/content-analytics/search-engines'], $engines);

        $topReferring = [
            'title'   => esc_html__('Top Referring', 'wp-statistics'),
            'tooltip' => esc_html__('The top referring domains.', 'wp-statistics'),
            'data'    => $data['referrers']
        ];
        Admin_Template::get_template(['layout/content-analytics/top-referring'], $topReferring);
        ?>
    </div>

</div>