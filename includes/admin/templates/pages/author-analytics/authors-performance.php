<div class="metabox-holder" id="authors-performance">
    <div class="postbox-container" id="wps-postbox-container-1">
        <?php
        use WP_STATISTICS\Admin_Template;

        // Item 1
        $args1 = array(
            'title_text'   => esc_html__('Authors', 'wp-statistics'),
            'tooltip_text'   => esc_html__('Authors tooltip', 'wp-statistics'),
            'icon_class'   => 'authors',
            'total'   => '2.5K',
            'active'   => '46',
            'avg'   => '10.56',
            'avg_title'   => esc_html__('Post/Authors', 'wp-statistics'),
        );
         Admin_Template::get_template(array('layout/author-analytics/performance-summary'), $args1);

        // Item 2
        $args2 = array(
            'title_text'   => esc_html__('Views', 'wp-statistics'),
            'tooltip_text'   => esc_html__('Views tooltip', 'wp-statistics'),
            'icon_class'   => 'views',
            'total'   => '35.1M',
            'active'   => null,
            'avg'   => '16.2K',
            'avg_title'   => esc_html__('Avg. Per Post', 'wp-statistics'),
        );
        Admin_Template::get_template(array('layout/author-analytics/performance-summary'), $args2);

        // Item 3
        $args3 = array(
            'title_text'   => esc_html__('Words', 'wp-statistics'),
            'tooltip_text'   => esc_html__('Words tooltip', 'wp-statistics'),
            'icon_class'   => 'words',
            'total'   => '25.2M',
            'active'   => null,
            'avg'   => '8.2K',
            'avg_title'   => esc_html__('Avg. Per Post', 'wp-statistics'),
        );
        Admin_Template::get_template(array('layout/author-analytics/performance-summary'), $args3);

        // Item 4
        $args4 = array(
            'title_text'   => esc_html__('Comments', 'wp-statistics'),
            'tooltip_text'   => esc_html__('Comments tooltip', 'wp-statistics'),
            'icon_class'   => 'comments',
            'total'   => '61K',
            'active'   => null,
            'avg'   => '300',
            'avg_title'   => esc_html__('Avg. Per Post', 'wp-statistics'),
        );
        Admin_Template::get_template(array('layout/author-analytics/performance-summary'), $args4);
       ?>
    </div>

    <div class="postbox-container" id="wps-postbox-container-2">
        <?php
            $overview_args = array(
                'title_text'   => esc_html__('Publishing Overview', 'wp-statistics'),
                'tooltip_text'   => esc_html__('Publishing Overview tooltip', 'wp-statistics'),
                'title_description'   => esc_html__('Last 12 Months', 'wp-statistics'),
             );
            Admin_Template::get_template(array('layout/author-analytics/publishing-overview'), $overview_args);
        ?>

        <?php
        $top_authors_args = array(
            'title_text'   => esc_html__('Top Authors', 'wp-statistics'),
            'tooltip_text'   => esc_html__('Top Authors tooltip', 'wp-statistics'),
        );
        Admin_Template::get_template(array('layout/author-analytics/top-authors'), $top_authors_args);
        ?>

        <?php
        $posts_args = array(
            'title_text'   => esc_html__('Views/Published Posts', 'wp-statistics'),
            'tooltip_text'   => esc_html__('Views/Published Posts tooltip', 'wp-statistics'),
        );
        Admin_Template::get_template(array('layout/author-analytics/published-posts'), $posts_args);
        ?>
    </div>
</div>