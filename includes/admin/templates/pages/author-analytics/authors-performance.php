<?php 
use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Request;
use WP_STATISTICS\Admin_Template;

$postType            = Request::get('pt', 'post');
$postTypeSingularLabel       = Helper::getPostTypeName($postType, true);
$postTypePluralLabel = Helper::getPostTypeName($postType);
?>

<div class="metabox-holder" id="authors-performance">
    <div class="postbox-container" id="wps-postbox-container-1">
        <?php
            $items = [
                [
                    'title'        => esc_html__('Authors', 'wp-statistics'),
                    'tooltip'      => sprintf(
                        esc_html__('Total number of authors contributing content. Active authors have published at least one %s in the selected period.', 'wp-statistics'), 
                        strtolower($postTypeSingularLabel)
                    ),
                    'icon_class'   => 'authors',
                    'total'        => Helper::formatNumberWithUnit($data['authors']['total']),
                    'published'    => Helper::formatNumberWithUnit($data['authors']['published']),
                    'active'       => Helper::formatNumberWithUnit($data['authors']['active']),
                    'avg'          => Helper::formatNumberWithUnit($data['authors']['avg']),
                    'avg_title'    => sprintf(esc_html__('%s/Authors', 'wp-statistics'), $postTypeSingularLabel)
                ],
                [
                    'title'        => esc_html__('Views', 'wp-statistics'),
                    'tooltip'      => sprintf(
                        esc_html__('Total number of views across all %1$s by all authors. Average views per %2$s is calculated by dividing total views by the number of %1$s.', 'wp-statistics'), 
                        strtolower($postTypePluralLabel), 
                        strtolower($postTypeSingularLabel)
                    ),
                    'icon_class'   => 'views',
                    'total'        => Helper::formatNumberWithUnit($data['views']['total']),
                    'avg'          => Helper::formatNumberWithUnit($data['views']['avg']),
                    'avg_title'    => sprintf(esc_html__('Avg. Per %s', 'wp-statistics'), $postTypeSingularLabel)
                ],
                [
                    'title'        => esc_html__('Words', 'wp-statistics'),
                    'tooltip'      => sprintf(
                        esc_html__('Total number of words written by all authors. Average words per %1$s is calculated by dividing total words by the number of %2$s.', 'wp-statistics'), 
                        strtolower($postTypeSingularLabel), 
                        strtolower($postTypePluralLabel)
                    ),
                    'icon_class'   => 'words',
                    'total'        => Helper::formatNumberWithUnit($data['posts']['words']['total']),
                    'avg'          => Helper::formatNumberWithUnit($data['posts']['words']['avg']),
                    'avg_title'    => sprintf(esc_html__('Avg. Per %s', 'wp-statistics'), $postTypeSingularLabel)
                ]
            ];

            if (post_type_supports($postType, 'comments')) {
                $items[] = [
                    'title'        => esc_html__('Comments', 'wp-statistics'),
                    'tooltip'      => sprintf(
                        esc_html__('Total number of comments received on %1$s by all authors. Average comments per %2$s is calculated by dividing total comments by the number of %1$s.', 'wp-statistics'), 
                        strtolower($postTypePluralLabel), 
                        strtolower($postTypeSingularLabel)
                    ),
                    'icon_class'   => 'comments',
                    'total'        => Helper::formatNumberWithUnit($data['posts']['comments']['total']),
                    'avg'          => Helper::formatNumberWithUnit($data['posts']['comments']['avg']),
                    'avg_title'    => sprintf(esc_html__('Avg. Per %s', 'wp-statistics'), $postTypeSingularLabel)
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
                'tooltip'       => sprintf(
                    esc_html__('This heatmap displays the publishing activity of authors over the past 12 months. Darker squares represent more published %s.', 'wp-statistics'), 
                    strtolower($postTypePluralLabel)
                ),
                'description'   => esc_html__('Last 12 Months', 'wp-statistics'),
                'data'          => $data
            ]);
            
            Admin_Template::get_template(['layout/author-analytics/top-authors'], [
                'title'    => esc_html__('Top Authors', 'wp-statistics'),
                'tooltip'  => sprintf(
                    esc_html__('This section ranks authors based on various performance metrics such as views, publishing frequency, comments per %1$s, and average words per %1$s. Use the tabs to switch between different metrics to see how each author is performing.', 'wp-statistics'), 
                    strtolower($postTypeSingularLabel)
                ),
                'data'     => $data
            ]);
            
            Admin_Template::get_template(['layout/author-analytics/published-posts'], [
                'title'     => sprintf(esc_html__('Views/Published %s', 'wp-statistics'), $postTypePluralLabel),
                'tooltip'   => sprintf(
                    esc_html__('This scatter plot shows the relationship between the number of %1$s published by an author and the number of views those %1$s have received. Each point represents an author.', 'wp-statistics'), 
                    strtolower($postTypePluralLabel)
                ),
                'data'      => $data
            ]);
        ?>
    </div>
</div>