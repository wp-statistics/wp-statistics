<?php
use WP_Statistics\Components\DateRange;
use WP_Statistics\Components\View;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;
?>
<div class="metabox-holder wps-referral-overview">
    <div class="postbox-container" id="wps-postbox-container-1">

        <?php
        View::load("components/objects/glance-card", ['metrics' => [
            ['label' => esc_html__('Visitors', 'wp-statistics'), 'tooltip' => esc_html__('tooltip', 'wp-statistics'), 'value' => '31.1K', 'change' => '8.3'],
            ['label' => esc_html__('Views', 'wp-statistics'), 'tooltip' => esc_html__('tooltip', 'wp-statistics'), 'value' => '31.1K', 'change' => '-1.3'],
            ['label' => esc_html__('Top Country', 'wp-statistics'), 'tooltip' => esc_html__('tooltip', 'wp-statistics'), 'value' => 'France'],
            ['label' => esc_html__('Top Referrer', 'wp-statistics'), 'tooltip' => esc_html__('tooltip', 'wp-statistics'), 'link-title' => 'google.com',  'link-href' => 'http://google.com',],
            ['label' => esc_html__('Logged-in Share', 'wp-statistics'), 'tooltip' => esc_html__('tooltip', 'wp-statistics'), 'value' => '125.4K', 'change' => '0'],
        ]]);

        ?>
        <div class="wps-card">
            <div class="wps-card__title">
                <h2><?php esc_html_e('Traffic Summary', 'wp-statistics') ?></h2>
            </div>
            <div class="wps-meta-traffic-summary">
                <div class="c-live">
                    <div>
                        <span class="c-live__status"></span>
                        <span class="c-live__title"><?php esc_html_e('Online Users', 'wp-statistics') ?></span>
                    </div>
                    <div class="c-live__online">
                        <span class="c-live__online--value"><?php echo esc_html(number_format_i18n($data['summary']['online'])) ?></span>
                        <a class="c-live__value" href="<?php echo esc_url(Menus::admin_url('visitors', ['tab' => 'online'])) ?>"><span class="c-live__online--arrow"></span></a>
                    </div>
                </div>
                <div class="o-table-wrapper">
                    <table width="100%" class="o-table o-table--wps-summary-stats">
                        <thead>
                            <tr>
                                <th width="50%"><?php esc_html_e('Time', 'wp-statistics') ?></th>
                                <th><?php esc_html_e('Visitors', 'wp-statistics') ?></th>
                                <th><?php esc_html_e('Views', 'wp-statistics') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><b><?php esc_html_e('Today', 'wp-statistics') ?></b></td>
                                <td><a href="<?php echo esc_url(Menus::admin_url('visitors', array_merge(['tab' => 'visitors'], DateRange::get('today')))); ?>"><span class="quickstats-values"><?php echo esc_html(number_format_i18n($data['summary']['visitors']['today'])); ?></span></a></td>
                                <td><a href="<?php echo esc_url(Menus::admin_url('visitors', array_merge(['tab' => 'views'], DateRange::get('today')))); ?>"><span class="quickstats-values"><?php echo esc_html(number_format_i18n($data['summary']['views']['today'])); ?></span></a></td>
                            </tr>
                            <tr>
                                <td><b><?php esc_html_e('Yesterday', 'wp-statistics') ?></b></td>
                                <td><a href="<?php echo esc_url(Menus::admin_url('visitors', array_merge(['tab' => 'visitors'], DateRange::get('yesterday')))); ?>"><span class="quickstats-values"><?php echo esc_html(number_format_i18n($data['summary']['visitors']['yesterday'])); ?></span></a></td>
                                <td><a href="<?php echo esc_url(Menus::admin_url('visitors', array_merge(['tab' => 'views'], DateRange::get('yesterday')))); ?>"><span class="quickstats-values"><?php echo esc_html(number_format_i18n($data['summary']['views']['yesterday'])); ?></span></a></td>
                            </tr>
                            <tr>
                                <td><b><?php esc_html_e('7-Day Avg', 'wp-statistics') ?></b></td>
                                <td><a href="<?php echo esc_url(Menus::admin_url('visitors', array_merge(['tab' => 'visitors'], DateRange::get('7days', true)))); ?>"><span class="quickstats-values"><?php echo esc_html(Helper::divideNumbers($data['summary']['visitors']['7days'], 7)); ?></span></a></td>
                                <td><a href="<?php echo esc_url(Menus::admin_url('visitors', array_merge(['tab' => 'views'], DateRange::get('7days', true)))); ?>"><span class="quickstats-values"><?php echo esc_html(Helper::divideNumbers($data['summary']['views']['7days'], 7)); ?></span></a></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php

        View::load("components/charts/horizontal-bar", [
            'title'        => esc_html__('Logged-in Users', 'wp-statistics'),
            'unique_id'    => 'visitors-logged-in-users',
            'footer_title' => esc_html__('View Logged-in Users', 'wp-statistics'),
            'footer_link'  => Menus::admin_url('visitors', ['tab' => 'logged-in-users'])
        ]);

        View::load("components/charts/horizontal-bar", [
            'title'     => esc_html__('Device Categories', 'wp-statistics'),
            'unique_id' => 'visitors-device-categories'
        ]);

        View::load("components/charts/horizontal-bar", [
            'title'        => esc_html__('Top Countries', 'wp-statistics'),
            'unique_id'    => 'visitors-top-countries',
            'footer_title' => esc_html__('View Countries', 'wp-statistics'),
            'footer_link'  => Menus::admin_url('geographic', ['tab' => 'countries'])
        ]);
        ?>

        <div class="wps-card">
            <div class="wps-card__title">
                <h2><?php esc_html_e('Top Referrers', 'wp-statistics') ?></h2>
            </div>
            <?php View::load("components/tables/top-referrers", ['data' => []]); ?>
            <div class="wps-card__footer">
                <div class="wps-card__footer__more">
                    <a class="wps-card__footer__more__link" href="<?php echo Menus::admin_url('referrals', ['tab' => 'referrers']) ?>">
                        <?php esc_html_e('View Referrers', 'wp-statistics') ?>
                    </a>
                </div>
            </div>
        </div>

        <?php
        View::load("components/charts/horizontal-bar", [
            'title'        => esc_html__('Top Browsers', 'wp-statistics'),
            'unique_id'    => 'visitors-top-browsers',
            'footer_title' => esc_html__('View Browsers', 'wp-statistics'),
            'footer_link'  => Menus::admin_url('devices', ['tab' => 'browsers'])
        ]);
        ?>

    </div>
    <div class="postbox-container" id="wps-postbox-container-2">
        <div class="wps-card">
            <div class="wps-card__title">
                <h2><?php esc_html_e('Referred Visitors', 'wp-statistics') ?></h2>
            </div>
            <?php View::load("components/charts/traffic-trends", ['chart_id' => 'referredVisitors']); ?>
        </div>


        <div class="wps-card">
            <div class="wps-card__title">
                <h2><?php esc_html_e('Top Entry Pages', 'wp-statistics') ?></h2>
            </div>
            <?php
            View::load("components/tables/visitors-top-entry-pages", ['data' => []]);
            ?>
            <div class="wps-p-0">
                <?php
                View::load("components/objects/card-footer", [
                    'href'  => Menus::admin_url('pages', ['tab' => 'entry-pages']),
                    'title' => __('View Entry Pages', 'wp-statistics'),
                ]);
                ?>
            </div>
        </div>

        <div class="wps-card">
            <div class="wps-card__title">
                <h2><?php esc_html_e('Peak Hour Heat-Line', 'wp-statistics') ?></h2>
            </div>
            <div id="wp-statistics-hourly-usage-widget" class="inside">
                <div class="o-wrap wps-p-0">
                    <div class="wps-postbox-chart--data">
                        <div class="wps-postbox-chart--items"></div>
                        <div class="wps-postbox-chart--previousPeriod">
                            <?php esc_html_e('Previous period', 'wp-statistics'); ?>
                        </div>
                    </div>
                    <div class="wps-postbox-chart--container">
                        <canvas id="hourly-usage-chart" style="width: 100%;" height="217"></canvas>
                    </div>
                </div>
            </div>
        </div>


        <div class="wps-card">
            <div class="wps-card__title">
                <h2><?php esc_html_e('Most Active Visitors', 'wp-statistics') ?></h2>
            </div>
            <?php
            View::load("components/tables/most-active-visitors", ['data' => [], 'isTodayOrFutureDate' => null]);
            ?>
            <div class="wps-p-0">
                <?php
                View::load("components/objects/card-footer", [
                    'href'  => Menus::admin_url('visitors', ['tab' => 'visitors']),
                    'title' => __('View Top Visitors', 'wp-statistics'),
                ]);
                ?>
            </div>
        </div>

        <div class="wps-card">
            <div class="wps-card__title">
                <h2><?php esc_html_e('Global Visitor Map', 'wp-statistics') ?></h2>
            </div>
            <div class="inside wps-geo-map">
                <?php
                $distribution = [
                    "data" => [
                        "data"     => [
                            "6",
                            "144",
                            "9",
                            "36",
                            "24",
                            "5",
                            "224",
                            "1",
                            "838",
                            "505",
                            "5",
                            "2",
                            "15",
                            "51",
                            "4",
                            "251",
                            "394",
                            "19",
                            "172",
                            "7",
                            "26",
                            "2",
                            "17",
                            "1",
                            "579",
                            "2",
                            "44",
                            "4",
                            "893",
                            "13",
                            "3",
                            "1085",
                            "14",
                            "176",
                            "15",
                            "727",
                            "232",
                            "33",
                            "23",
                            "4",
                            "1",
                            "41",
                            "466",
                            "6478",
                            "476",
                            "59",
                            "76",
                            "49",
                            "144",
                            "263",
                            "1592",
                            "255",
                            "556",
                            "1",
                            "4",
                            "2731",
                            "1",
                            "1695",
                            "4",
                            "22",
                            "5",
                            "33",
                            "2",
                            "1",
                            "4",
                            "8",
                            "3",
                            "340",
                            "33",
                            "1",
                            "2",
                            "357",
                            "13",
                            "155",
                            "8",
                            "385",
                            "616",
                            "428",
                            "140",
                            "1070",
                            "51",
                            "3250",
                            "15",
                            "1474",
                            "2",
                            "10",
                            "44",
                            "1688",
                            "75",
                            "7",
                            "33",
                            "1",
                            "467",
                            "13",
                            "15",
                            "7",
                            "17",
                            "2",
                            "2",
                            "36",
                            "5",
                            "113",
                            "45",
                            "83",
                            "6",
                            "210",
                            "2",
                            "41",
                            "6",
                            "7",
                            "36",
                            "10",
                            "16",
                            "6",
                            "3",
                            "2",
                            "18",
                            "10",
                            "2",
                            "2",
                            "338",
                            "212",
                            "9",
                            "8",
                            "3",
                            "1",
                            "224",
                            "10",
                            "2546",
                            "219",
                            "68",
                            "110",
                            "18",
                            "31",
                            "93",
                            "6",
                            "3",
                            "181",
                            "329",
                            "1550",
                            "17",
                            "12",
                            "258",
                            "23",
                            "8",
                            "16",
                            "357",
                            "113",
                            "291",
                            "17",
                            "104",
                            "3",
                            "6",
                            "7",
                            "724",
                            "323",
                            "87",
                            "189",
                            "3",
                            "10",
                            "10",
                            "6",
                            "13",
                            "10",
                            "4",
                            "1",
                            "17",
                            "342",
                            "2",
                            "67",
                            "1",
                            "478",
                            "7",
                            "274",
                            "15",
                            "236",
                            "25",
                            "5675",
                            "36",
                            "23",
                            "1",
                            "55",
                            "1",
                            "469",
                            "3",
                            "10",
                            "17",
                            "235",
                            "8",
                            "10"
                        ],
                        "raw_data" => [
                            "6",
                            "144",
                            "9",
                            "36",
                            "24",
                            "5",
                            "224",
                            "1",
                            "838",
                            "505",
                            "5",
                            "2",
                            "15",
                            "51",
                            "4",
                            "251",
                            "394",
                            "19",
                            "172",
                            "7",
                            "26",
                            "2",
                            "17",
                            "1",
                            "579",
                            "2",
                            "44",
                            "4",
                            "893",
                            "13",
                            "3",
                            "1085",
                            "14",
                            "176",
                            "15",
                            "727",
                            "232",
                            "33",
                            "23",
                            "4",
                            "1",
                            "41",
                            "466",
                            "6478",
                            "476",
                            "59",
                            "76",
                            "49",
                            "144",
                            "263",
                            "1592",
                            "255",
                            "556",
                            "1",
                            "4",
                            "2731",
                            "1",
                            "1695",
                            "4",
                            "22",
                            "5",
                            "33",
                            "2",
                            "1",
                            "4",
                            "8",
                            "3",
                            "340",
                            "33",
                            "1",
                            "2",
                            "357",
                            "13",
                            "155",
                            "8",
                            "385",
                            "616",
                            "428",
                            "140",
                            "1070",
                            "51",
                            "3250",
                            "15",
                            "1474",
                            "2",
                            "10",
                            "44",
                            "1688",
                            "75",
                            "7",
                            "33",
                            "1",
                            "467",
                            "13",
                            "15",
                            "7",
                            "17",
                            "2",
                            "2",
                            "36",
                            "5",
                            "113",
                            "45",
                            "83",
                            "6",
                            "210",
                            "2",
                            "41",
                            "6",
                            "7",
                            "36",
                            "10",
                            "16",
                            "6",
                            "3",
                            "2",
                            "18",
                            "10",
                            "2",
                            "2",
                            "338",
                            "212",
                            "9",
                            "8",
                            "3",
                            "1",
                            "224",
                            "10",
                            "2546",
                            "219",
                            "68",
                            "110",
                            "18",
                            "31",
                            "93",
                            "6",
                            "3",
                            "181",
                            "329",
                            "1550",
                            "17",
                            "12",
                            "258",
                            "23",
                            "8",
                            "16",
                            "357",
                            "113",
                            "291",
                            "17",
                            "104",
                            "3",
                            "6",
                            "7",
                            "724",
                            "323",
                            "87",
                            "189",
                            "3",
                            "10",
                            "10",
                            "6",
                            "13",
                            "10",
                            "4",
                            "1",
                            "17",
                            "342",
                            "2",
                            "67",
                            "1",
                            "478",
                            "7",
                            "274",
                            "15",
                            "236",
                            "25",
                            "5675",
                            "36",
                            "23",
                            "1",
                            "55",
                            "1",
                            "469",
                            "3",
                            "10",
                            "17",
                            "235",
                            "8",
                            "10"
                        ]
                    ]
                ];

                View::load("metabox/global-visitor-distribution", $distribution);
                ?>
            </div>
        </div>


    </div>
</div>