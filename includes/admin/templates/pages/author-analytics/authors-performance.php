<?php use WP_STATISTICS\Admin_Template; ?>

<div class="metabox-holder" id="authors-performance">
    <div class="postbox-container" id="wps-postbox-container-1">
        <?php
            $items = [
                [
                    'title_text'   => esc_html__('Authors', 'wp-statistics'),
                    'tooltip_text' => esc_html__('Authors tooltip', 'wp-statistics'),
                    'icon_class'   => 'authors',
                    'total'        => '2.5K',
                    'active'       => '46',
                    'avg'          => '10.56',
                    'avg_title'    => esc_html__('Post/Authors', 'wp-statistics')
                ],
                [
                    'title_text'   => esc_html__('Views', 'wp-statistics'),
                    'tooltip_text' => esc_html__('Views tooltip', 'wp-statistics'),
                    'icon_class'   => 'views',
                    'total'        => '35.1M',
                    'avg'          => '16.2K',
                    'avg_title'    => esc_html__('Avg. Per Post', 'wp-statistics')
                ],
                [
                    'title_text'   => esc_html__('Words', 'wp-statistics'),
                    'tooltip_text' => esc_html__('Words tooltip', 'wp-statistics'),
                    'icon_class'   => 'words',
                    'total'        => '25.2M',
                    'avg'          => '8.2K',
                    'avg_title'    => esc_html__('Avg. Per Post', 'wp-statistics')
                ],
                [
                    'title_text'   => esc_html__('Comments', 'wp-statistics'),
                    'tooltip_text' => esc_html__('Comments tooltip', 'wp-statistics'),
                    'icon_class'   => 'comments',
                    'total'        => '61K',
                    'avg'          => '300',
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
                'title_text'   => esc_html__('Publishing Overview', 'wp-statistics'),
                'tooltip_text'   => esc_html__('Publishing Overview tooltip', 'wp-statistics'),
                'title_description'   => esc_html__('Last 12 Months', 'wp-statistics'),
            ]);
            
            Admin_Template::get_template(['layout/author-analytics/top-authors'], [
                'title_text'    => esc_html__('Top Authors', 'wp-statistics'),
                'tooltip_text'  => esc_html__('Top Authors tooltip', 'wp-statistics'),
            ]);
            
            Admin_Template::get_template(['layout/author-analytics/published-posts'], [
                'title_text'   => esc_html__('Views/Published Posts', 'wp-statistics'),
                'tooltip_text'   => esc_html__('Views/Published Posts tooltip', 'wp-statistics'),
            ]);
        ?>
    </div>
</div>