<?php 
use WP_STATISTICS\Admin_Template;
use WP_STATISTICS\Helper;
?>

<div class="metabox-holder" id="authors-performance">
    <div class="postbox-container" id="wps-postbox-container-1">
        <?php
            $items = [
                [
                    'title'        => esc_html__('Authors', 'wp-statistics'),
                    'tooltip'      => esc_html__('Authors tooltip', 'wp-statistics'),
                    'icon_class'   => 'authors',
                    'total'        => Helper::formatNumberWithUnit($data['authors']['total']),
                    'active'       => Helper::formatNumberWithUnit($data['authors']['active']),
                    'avg'          => Helper::formatNumberWithUnit($data['authors']['avg']),
                    'avg_title'    => esc_html__('Post/Authors', 'wp-statistics')
                ],
                [
                    'title'        => esc_html__('Views', 'wp-statistics'),
                    'tooltip'      => esc_html__('Views tooltip', 'wp-statistics'),
                    'icon_class'   => 'views',
                    'total'        => Helper::formatNumberWithUnit($data['views']['total']),
                    'avg'          => Helper::formatNumberWithUnit($data['views']['avg']),
                    'avg_title'    => esc_html__('Avg. Per Post', 'wp-statistics')
                ],
                [
                    'title'        => esc_html__('Words', 'wp-statistics'),
                    'tooltip'      => esc_html__('Words tooltip', 'wp-statistics'),
                    'icon_class'   => 'words',
                    'total'        => Helper::formatNumberWithUnit($data['posts']['words']['total']),
                    'avg'          => Helper::formatNumberWithUnit($data['posts']['words']['avg']),
                    'avg_title'    => esc_html__('Avg. Per Post', 'wp-statistics')
                ],
                [
                    'title'        => esc_html__('Comments', 'wp-statistics'),
                    'tooltip'      => esc_html__('Comments tooltip', 'wp-statistics'),
                    'icon_class'   => 'comments',
                    'total'        => Helper::formatNumberWithUnit($data['posts']['comments']['total']),
                    'avg'          => Helper::formatNumberWithUnit($data['posts']['comments']['avg']),
                    'avg_title'    => esc_html__('Avg. Per Post', 'wp-statistics')
                ],
            ];

            foreach ($items as $args) {
                Admin_Template::get_template(['layout/author-analytics/performance-summary'], $args);
            }
        ?>
    </div>

    <div class="postbox-container" id="wps-postbox-container-2">
        <?php
            Admin_Template::get_template(['layout/author-analytics/publishing-overview'], [
                'title'         => esc_html__('Publishing Overview', 'wp-statistics'),
                'tooltip'       => esc_html__('Publishing Overview tooltip', 'wp-statistics'),
                'description'   => esc_html__('Last 12 Months', 'wp-statistics'),
            ]);
            
            Admin_Template::get_template(['layout/author-analytics/top-authors'], [
                'title'    => esc_html__('Top Authors', 'wp-statistics'),
                'tooltip'  => esc_html__('Top Authors tooltip', 'wp-statistics'),
            ]);
            
            Admin_Template::get_template(['layout/author-analytics/published-posts'], [
                'title'     => esc_html__('Views/Published Posts', 'wp-statistics'),
                'tooltip'   => esc_html__('Views/Published Posts tooltip', 'wp-statistics'),
            ]);
        ?>
    </div>
</div>