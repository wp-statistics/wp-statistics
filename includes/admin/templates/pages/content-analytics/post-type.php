<div class="metabox-holder wps-content-analytics">
    <div class="postbox-container" id="wps-postbox-container-1">
        <?php

        use WP_STATISTICS\Admin_Template;

        // Item 1
        $args1 = array(
            'title_text'   => esc_html__('Published Pages', 'wp-statistics'),
            'tooltip_text' => esc_html__('Published Pages tooltip', 'wp-statistics'),
            'icon_class'   => 'posts',
            'total'        => '2.5K',
        );
        Admin_Template::get_template(array('layout/content-analytics/overview-card'), $args1);

        // Item 2
        $args2 = array(
            'title_text'   => esc_html__('Views', 'wp-statistics'),
            'tooltip_text' => esc_html__('Views tooltip', 'wp-statistics'),
            'icon_class'   => 'views',
            'total'        => '35.1M',
            'avg'          => '16.2K',
            'avg_title'    => esc_html__('Avg. Per Page', 'wp-statistics'),
        );
        Admin_Template::get_template(array('layout/content-analytics/overview-card'), $args2);

        // Item 3
        $args3 = array(
            'title_text'   => esc_html__('Visitors', 'wp-statistics'),
            'tooltip_text' => esc_html__('Visitors tooltip', 'wp-statistics'),
            'icon_class'   => 'visitors',
            'total'        => '35.1M',
            'avg'          => '10.2K',
            'avg_title'    => esc_html__('Avg. Per Page', 'wp-statistics'),
        );
        Admin_Template::get_template(array('layout/content-analytics/overview-card'), $args3);

        // Item 4
        $args4 = array(
            'title_text'   => esc_html__('Words', 'wp-statistics'),
            'tooltip_text' => esc_html__('Words tooltip', 'wp-statistics'),
            'icon_class'   => 'words',
            'total'        => '35.1M',
            'avg'          => '10.2K',
            'avg_title'    => esc_html__('Avg. Per Page', 'wp-statistics'),
        );
        Admin_Template::get_template(array('layout/content-analytics/overview-card'), $args4);

        // Item 5
        $args5 = array(
            'title_text'   => esc_html__('Comments', 'wp-statistics'),
            'tooltip_text' => esc_html__('Comments tooltip', 'wp-statistics'),
            'icon_class'   => 'comments',
            'total'        => '35.1M',
            'avg'          => '300',
            'avg_title'    => esc_html__('Avg. Per Page', 'wp-statistics'),
        );
        Admin_Template::get_template(array('layout/content-analytics/overview-card'), $args5);
        ?>

        <?php
        $operating_systems = array(
            'title_text'       => esc_html__('Operating Systems', 'wp-statistics'),
            'tooltip_text'     => esc_html__('Operating Systems tooltip', 'wp-statistics'),
            'labels'           => ['Windows', 'macOs', 'iOS', 'Android', 'Linux', 'Other'],
            'background_color' => ['#F7D399', '#99D3FB', '#D7BDE2', '#D7BDE2', '#EBA39B', '#F5CBA7'],
            'data'             => [30, 20, 10, 5, 7, 5]
        );
        Admin_Template::get_template(array('layout/content-analytics/pie-chart'), $operating_systems);
        ?>

        <?php
        $browsers = array(
            'title_text'       => esc_html__('Browsers', 'wp-statistics'),
            'tooltip_text'     => esc_html__('Browsers tooltip', 'wp-statistics'),
            'labels'           => ['Chrome', 'Firefox', 'Safari', 'Opera', 'edge', 'Other'],
            'background_color' => ['#F7D399', '#99D3FB', '#D7BDE2', '#D7BDE2', '#EBA39B', '#F5CBA7'],
            'data'             => [30, 20, 10, 7, 6, 5]
        );
        Admin_Template::get_template(array('layout/content-analytics/pie-chart'), $browsers);
        ?>

        <?php
        $device_models = array(
            'title_text'       => esc_html__('Device Models', 'wp-statistics'),
            'tooltip_text'     => esc_html__('Device Models tooltip', 'wp-statistics'),
            'labels'           => ['Macintosh', 'iPhone', 'G6', 'A3', 'Galaxy A52', 'Other'],
            'background_color' => ['#F7D399', '#99D3FB', '#D7BDE2', '#D7BDE2', '#EBA39B', '#F5CBA7'],
            'data'             => [30, 20, 10, 7, 6, 5]
        );
        Admin_Template::get_template(array('layout/content-analytics/pie-chart'), $device_models);
        ?>

        <?php
        $device_usage = array(
            'title_text'       => esc_html__('Device Usage', 'wp-statistics'),
            'tooltip_text'     => esc_html__('Device Usage tooltip', 'wp-statistics'),
            'labels'           => ['Desktop', 'Mobile:smart', 'Tablet', 'Signage', 'Television', 'Other'],
            'background_color' => ['#F7D399', '#99D3FB', '#D7BDE2', '#D7BDE2', '#EBA39B', '#F5CBA7'],
            'data'             => [30, 20, 10, 7, 6, 5]
        );
        Admin_Template::get_template(array('layout/content-analytics/pie-chart'), $device_usage);
        ?>
    </div>

    <div class="postbox-container" id="wps-postbox-container-2">
        <?php
        $performance = array(
            'title_text'   => esc_html__('Performance', 'wp-statistics'),
            'tooltip_text' => esc_html__('Performance tooltip', 'wp-statistics'),
            'description_text' => esc_html__('Last 15 Days', 'wp-statistics'),
        );
        Admin_Template::get_template(array('layout/content-analytics/performance-chart'), $performance);
        ?>

        <?php
        $top_pages = array(
            'title_text'   => esc_html__('Top Pages', 'wp-statistics'),
            'tooltip_text' => esc_html__('Top Pages tooltip', 'wp-statistics'),
            'type'         => esc_html__('page', 'wp-statistics'),
        );
        Admin_Template::get_template(array('layout/content-analytics/top-picks'), $top_pages);
        ?>

        <?php
        $summary = array(
            'title_text'   => esc_html__('Summary', 'wp-statistics'),
            'tooltip_text' => esc_html__('Summary tooltip', 'wp-statistics'),
        );
        Admin_Template::get_template(array('layout/content-analytics/summary'), $summary);
        ?>

        <?php
        $top_countries = array(
            'title_text'   => esc_html__('Top Countries', 'wp-statistics'),
            'tooltip_text' => esc_html__('Top Countries tooltip', 'wp-statistics'),
        );
        Admin_Template::get_template(array('layout/content-analytics/top-countries'), $top_countries);
        ?>

        <?php
        $engines = array(
            'title_text'   => esc_html__('Search Engines', 'wp-statistics'),
            'tooltip_text' => esc_html__('Search Engines tooltip', 'wp-statistics'),
        );
        Admin_Template::get_template(array('layout/content-analytics/search-engines'), $engines);
        ?>

        <?php
        $top_referring = array(
            'title_text'   => esc_html__('Top Referring', 'wp-statistics'),
            'tooltip_text' => esc_html__('Top Referring tooltip', 'wp-statistics'),
        );
        Admin_Template::get_template(array('layout/content-analytics/top-referring'), $top_referring);
        ?>
    </div>

</div>