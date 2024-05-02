<div class="metabox-holder" id="authors-performance">
    <div class="postbox-container" id="wps-postbox-container-1">
        <div class="wps-card">
            <div class="wps-author-info">
                <div>
                    <?php $user = wp_get_current_user();
                    if ($user) : ?>
                        <img src="<?php echo esc_url(get_avatar_url($user->ID)); ?>" alt="username"/>
                    <?php endif ?>
                    <a href="" title="Author Name" class="wps-author-info__name">Author Name</a>
                    <p class="wps-author-info__desc">Author for more than 10 years</p>
                </div>
                <ul class="wps-author-info__socials">
                    <li>
                        <a href="mailto:mail.com" title=""><i class="mail"></i></a>
                    </li>
                    <li>
                        <a href="tel:123456" title=""><i class="phone"></i></a>
                    </li>
                    <li>
                        <a href="" title=""><i class="blog"></i></a>
                    </li>
                </ul>
            </div>

        </div>
        <?php

        use WP_STATISTICS\Admin_Template;

        function generate_item_args($title, $tooltip, $icon, $total, $avg = null, $avg_title = null)
        {
            return array(
                'title_text'   => esc_html__($title, 'wp-statistics'),
                'tooltip_text' => esc_html__($tooltip, 'wp-statistics'),
                'icon_class'   => $icon,
                'total'        => $total,
                'avg'          => $avg,
                'avg_title'    => $avg_title ? esc_html__('Avg. Per Post', 'wp-statistics') : null,
            );
        }

        $items = array(
            generate_item_args('Published Posts', 'Published Posts tooltip', 'authors', '2.5K'),
            generate_item_args('Views', 'Views tooltip', 'views', '12.3k', '1.1k', 'Avg. Per Post'),
            generate_item_args('Visitors', 'Visitors tooltip', 'visitors', '8.2K', '850', 'Avg. Per Post'),
            generate_item_args('Words', 'Words tooltip', 'words', '61K', '300', 'Avg. Per Post'),
            generate_item_args('Comments', 'Comments tooltip', 'comments', '61K', '300', 'Avg. Per Post')
        );

        foreach ($items as $args) {
            Admin_Template::get_template(array('layout/author-analytics/performance-summary'), $args);
        }
        ?>

        <?php
        $topCategories = array(
            'title_text'   => esc_html__('Top Categories', 'wp-statistics'),
            'tooltip_text' => esc_html__('Top Categories tooltip', 'wp-statistics'),
        );
        Admin_Template::get_template(array('layout/author-analytics/top-categories'), $topCategories);
        ?>

        <?php
        $operating_system = array(
            'title_text'   => esc_html__('Operating Systems', 'wp-statistics'),
            'tooltip_text' => esc_html__('Operating Systems tooltip', 'wp-statistics'),
        );
        Admin_Template::get_template(array('layout/author-analytics/operating-systems'), $operating_system);
        ?>

        <?php
        $browsers = array(
            'title_text'   => esc_html__('Browsers', 'wp-statistics'),
            'tooltip_text' => esc_html__('Browsers tooltip', 'wp-statistics'),
        );
        Admin_Template::get_template(array('layout/author-analytics/browsers'), $browsers);
        ?>
    </div>
    <div class="postbox-container" id="wps-postbox-container-2">
        <?php
        $overview_args = array(
            'title_text'        => esc_html__('Publishing Overview', 'wp-statistics'),
            'tooltip_text'      => esc_html__('Publishing Overview tooltip', 'wp-statistics'),
            'title_description' => esc_html__('Last 12 Months', 'wp-statistics'),
        );
        Admin_Template::get_template(array('layout/author-analytics/publishing-overview'), $overview_args);
        ?>

        <?php
        $top_posts = array(
            'title_text'   => esc_html__('Top Posts', 'wp-statistics'),
            'tooltip_text' => esc_html__('Top Posts tooltip', 'wp-statistics'),
        );
        Admin_Template::get_template(array('layout/author-analytics/top-posts'), $top_posts);
        ?>


        <?php
        $author_summary = array(
            'title_text'   => esc_html__('Summary', 'wp-statistics'),
            'tooltip_text' => esc_html__('Summary tooltip', 'wp-statistics'),
        );
        Admin_Template::get_template(array('layout/author-analytics/author-summary'), $author_summary);
        ?>

        <?php
        $top_countries = array(
            'title_text'   => esc_html__('Top Countries', 'wp-statistics'),
            'tooltip_text' => esc_html__('Top Countries tooltip', 'wp-statistics'),
        );
        Admin_Template::get_template(array('layout/author-analytics/top-countries'), $top_countries);
        ?>
    </div>
</div>
