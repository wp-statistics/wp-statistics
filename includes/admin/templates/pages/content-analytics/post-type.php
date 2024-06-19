<?php 
use WP_STATISTICS\Admin_Template;
use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Request;

$postType           = Request::get('tab', 'post');
$postTypeSingular   = Helper::getPostTypeName($postType, true);
$postTypePlural     = Helper::getPostTypeName($postType);
?>

<div class="metabox-holder wps-content-analytics">
    <div class="postbox-container" id="wps-postbox-container-1">
        <?php
            $args1 = [
                'title'         => sprintf(esc_html__('Published %s', 'wp-statistics'), $postTypePlural),
                'tooltip'       => esc_html__('Published Pages tooltip', 'wp-statistics'),
                'icon_class'   => 'posts',
                'total'        => Helper::formatNumberWithUnit($data['overview']['published']['total']),
            ];
            Admin_Template::get_template(['layout/content-analytics/overview-card'], $args1);

            $args2 = [
                'title'         => esc_html__('Views', 'wp-statistics'),
                'tooltip'       => esc_html__('Views tooltip', 'wp-statistics'),
                'icon_class'   => 'views',
                'total'        => Helper::formatNumberWithUnit($data['overview']['views']['total']),
                'avg'          => Helper::formatNumberWithUnit($data['overview']['views']['avg']),
                'avg_title'    => sprintf(esc_html__('Avg. per %s', 'wp-statistics'), $postTypeSingular),
            ];
            Admin_Template::get_template(['layout/content-analytics/overview-card'], $args2);

            $args3 = [
                'title'         => esc_html__('Visitors', 'wp-statistics'),
                'tooltip'       => esc_html__('Visitors tooltip', 'wp-statistics'),
                'icon_class'   => 'visitors',
                'total'        => Helper::formatNumberWithUnit($data['overview']['visitors']['total']),
                'avg'          => Helper::formatNumberWithUnit($data['overview']['visitors']['avg']),
                'avg_title'    => sprintf(esc_html__('Avg. per %s', 'wp-statistics'), $postTypeSingular),
            ];
            Admin_Template::get_template(['layout/content-analytics/overview-card'], $args3);

            $args4 = [
                'title'         => esc_html__('Words', 'wp-statistics'),
                'tooltip'       => esc_html__('Words tooltip', 'wp-statistics'),
                'icon_class'   => 'words',
                'total'        => Helper::formatNumberWithUnit($data['overview']['words']['total']),
                'avg'          => Helper::formatNumberWithUnit($data['overview']['words']['avg']),
                'avg_title'    => sprintf(esc_html__('Avg. per %s', 'wp-statistics'), $postTypeSingular),
            ];
            Admin_Template::get_template(['layout/content-analytics/overview-card'], $args4);

            if (post_type_supports($postType, 'comments')) {
                $args5 = [
                    'title'         => esc_html__('Comments', 'wp-statistics'),
                    'tooltip'       => esc_html__('Comments tooltip', 'wp-statistics'),
                    'icon_class'   => 'comments',
                    'total'        => Helper::formatNumberWithUnit($data['overview']['comments']['total']),
                    'avg'          => Helper::formatNumberWithUnit($data['overview']['comments']['avg']),
                    'avg_title'    => sprintf(esc_html__('Avg. per %s', 'wp-statistics'), $postTypeSingular),
                ];
                Admin_Template::get_template(['layout/content-analytics/overview-card'], $args5);
            }
        ?>

        <?php
            $operatingSystems = [
                'title'     => esc_html__('Operating Systems', 'wp-statistics'),
                'tooltip'   => esc_html__('Operating Systems tooltip', 'wp-statistics'),
                'unique_id' => 'content_operating_systems'
            ];
            Admin_Template::get_template(['layout/content-analytics/pie-chart'], $operatingSystems);
        ?>

        <?php
            $browsers = [
                'title'     => esc_html__('Browsers', 'wp-statistics'),
                'tooltip'   => esc_html__('Browsers tooltip', 'wp-statistics'),
                'unique_id' => 'content_browsers'
            ];
            Admin_Template::get_template(['layout/content-analytics/pie-chart'], $browsers);
        ?>

        <?php
            $deviceModels = [
                'title'     => esc_html__('Device Models', 'wp-statistics'),
                'tooltip'   => esc_html__('Device Models tooltip', 'wp-statistics'),
                'unique_id' => 'content_device_models'
            ];
            Admin_Template::get_template(['layout/content-analytics/pie-chart'], $deviceModels);
        ?>

        <?php
            $deviceUsage = [
                'title'     => esc_html__('Device Usage', 'wp-statistics'),
                'tooltip'   => esc_html__('Device Usage tooltip', 'wp-statistics'),
                'unique_id' => 'content_device_usage'
            ];
            Admin_Template::get_template(['layout/content-analytics/pie-chart'], $deviceUsage);
        ?>
    </div>

    <div class="postbox-container" id="wps-postbox-container-2">
        <?php
            $performance = [
                'title'       => esc_html__('Performance', 'wp-statistics'),
                'tooltip'     => esc_html__('Performance tooltip', 'wp-statistics'),
                'type'        => 'post-type',
                'description' => esc_html__('Last 15 Days', 'wp-statistics'),
                'data'        => $data['performance']
            ];
            Admin_Template::get_template(['layout/content-analytics/performance-chart'], $performance);

            $topPages = [
                'title'     => sprintf(esc_html__('Top %s', 'wp-statistics'), $postTypePlural),
                'tooltip'   => esc_html__('Top Pages tooltip', 'wp-statistics'),
                'type'      => esc_html__('page', 'wp-statistics'),
                'data'      => $data['posts']
            ];
            Admin_Template::get_template(['layout/content-analytics/top-picks'], $topPages);

            $summary = [
                'title'   => esc_html__('Summary', 'wp-statistics'),
                'tooltip' => esc_html__('Summary tooltip', 'wp-statistics'),
                'data'    => $data['visits_summary']
            ];
            Admin_Template::get_template(['layout/content-analytics/summary'], $summary);

            $topCountries = [
                'title'   => esc_html__('Top Countries', 'wp-statistics'),
                'tooltip' => esc_html__('Top Countries tooltip', 'wp-statistics'),
                'data'    => $data['visitors_data']['country']
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