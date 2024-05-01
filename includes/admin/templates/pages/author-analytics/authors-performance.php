<div class="metabox-holder" id="authors-performance">
    <div class="postbox-container" id="wps-postbox-container-1">
        <?php
        use WP_STATISTICS\Admin_Template;

        function generate_item_args($title, $tooltip, $icon, $total, $active = null, $avg = null, $avg_title = null) {
            return array(
                'title_text'   => esc_html__($title, 'wp-statistics'),
                'tooltip_text' => esc_html__($tooltip, 'wp-statistics'),
                'icon_class'   => $icon,
                'total'        => $total,
                'active'       => $active,
                'avg'          => $avg,
                'avg_title'    => esc_html__($avg_title ?? 'Avg. Per Post', 'wp-statistics'),
            );
        }

        $items = array(
            generate_item_args('Authors', 'Authors tooltip', 'authors', '2.5K', '46', '10.56', 'Post/Authors'),
            generate_item_args('Views', 'Views tooltip', 'views', '35.1M', null, '16.2K'),
            generate_item_args('Words', 'Words tooltip', 'words', '25.2M', null, '8.2K'),
            generate_item_args('Comments', 'Comments tooltip', 'comments', '61K', null, '300')
        );

        foreach ($items as $args) {
            Admin_Template::get_template(array('layout/author-analytics/performance-summary'), $args);
        }
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