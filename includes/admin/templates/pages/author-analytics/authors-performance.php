<?php 
use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Request;
use WP_STATISTICS\Admin_Template;

$postType = Request::get('pt', 'post');
?>

<div class="metabox-holder" id="authors-performance">
    <div class="postbox-container" id="wps-postbox-container-1">
        <?php
            $items = [
                [
                    'title'        => esc_html__('Authors', 'wp-statistics'),
                    'tooltip'      => esc_html__('Total number of authors contributing content. Active authors have published at least one post in the selected period.', 'wp-statistics'),
                    'icon_class'   => 'authors',
                    'total'        => Helper::formatNumberWithUnit($data['authors']['total']),
                    'active'       => Helper::formatNumberWithUnit($data['authors']['active']),
                    'avg'          => Helper::formatNumberWithUnit($data['authors']['avg']),
                    'avg_title'    => esc_html__('Post/Authors', 'wp-statistics')
                ],
                [
                    'title'        => esc_html__('Views', 'wp-statistics'),
                    'tooltip'      => esc_html__('Total number of views across all posts by all authors. Average views per post is calculated by dividing total views by the number of posts.', 'wp-statistics'),
                    'icon_class'   => 'views',
                    'total'        => Helper::formatNumberWithUnit($data['views']['total']),
                    'avg'          => Helper::formatNumberWithUnit($data['views']['avg']),
                    'avg_title'    => esc_html__('Avg. Per Post', 'wp-statistics')
                ],
                [
                    'title'        => esc_html__('Words', 'wp-statistics'),
                    'tooltip'      => esc_html__('Total number of words written by all authors. Average words per post is calculated by dividing total words by the number of posts.', 'wp-statistics'),
                    'icon_class'   => 'words',
                    'total'        => Helper::formatNumberWithUnit($data['posts']['words']['total']),
                    'avg'          => Helper::formatNumberWithUnit($data['posts']['words']['avg']),
                    'avg_title'    => esc_html__('Avg. Per Post', 'wp-statistics')
                ]
            ];

            if (post_type_supports($postType, 'comments')) {
                $items[] = [
                    'title'        => esc_html__('Comments', 'wp-statistics'),
                    'tooltip'      => esc_html__('Total number of comments received on posts by all authors. Average comments per post is calculated by dividing total comments by the number of posts.', 'wp-statistics'),
                    'icon_class'   => 'comments',
                    'total'        => Helper::formatNumberWithUnit($data['posts']['comments']['total']),
                    'avg'          => Helper::formatNumberWithUnit($data['posts']['comments']['avg']),
                    'avg_title'    => esc_html__('Avg. Per Post', 'wp-statistics')
                ];
            }

            foreach ($items as $args) {
                Admin_Template::get_template(['layout/author-analytics/performance-summary'], $args);
            }
        ?>
    </div>

    <div class="postbox-container" id="wps-postbox-container-2">
        <?php
            Admin_Template::get_template(['layout/author-analytics/publishing-overview'], [
                'title'         => esc_html__('Publishing Overview', 'wp-statistics'),
                'tooltip'       => esc_html__('This heatmap displays the publishing activity of authors over the past 12 months. Darker squares represent more published posts.', 'wp-statistics'),
                'description'   => esc_html__('Last 12 Months', 'wp-statistics'),
                'data'          => $data
            ]);
            
            Admin_Template::get_template(['layout/author-analytics/top-authors'], [
                'title'    => esc_html__('Top Authors', 'wp-statistics'),
                'tooltip'  => esc_html__('This section ranks authors based on various performance metrics such as views, publishing frequency, comments per post, and average words per post. Use the tabs to switch between different metrics to see how each author is performing.', 'wp-statistics'),
                'data'     => $data
            ]);
            
            Admin_Template::get_template(['layout/author-analytics/published-posts'], [
                'title'     => esc_html__('Views/Published Posts', 'wp-statistics'),
                'tooltip'   => esc_html__('This scatter plot shows the relationship between the number of posts published by an author and the number of views those posts have received. Each point represents an author.', 'wp-statistics'),
                'data'      => $data
            ]);
        ?>
    </div>
</div>