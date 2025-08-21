<?php

use WP_Statistics\Components\DateRange;
use WP_Statistics\Components\DateTime;
use WP_Statistics\Components\View;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Option;
use WP_Statistics\Utils\Url;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHandler;

$pluginHandler               = new PluginHandler();
$isActive                    = $pluginHandler->isPluginActive('wp-statistics-data-plus');
$isTrackLoggedInUsersEnabled = Option::get('visitors_log');
?>
<div class="metabox-holder wps-referral-overview">
    <div class="postbox-container" id="wps-postbox-container-1">

        <?php
        $metrics = [
            ['label' => esc_html__('Visitors', 'wp-statistics'), 'value' => Helper::formatNumberWithUnit($data['glance']['visitors']['value']), 'change' => $data['glance']['visitors']['change']],
            ['label' => esc_html__('Views', 'wp-statistics'), 'value' => Helper::formatNumberWithUnit($data['glance']['views']['value']), 'change' => $data['glance']['views']['change']],
            ['label' => esc_html__('Top Country', 'wp-statistics'), 'value' => $data['glance']['country']],
            ['label' => esc_html__('Top Referrer', 'wp-statistics'), 'link-title' => $data['glance']['referrer'], 'link-href' => Url::formatUrl($data['glance']['referrer'])],
        ];

        if ($isTrackLoggedInUsersEnabled) {
            $metrics[] = ['label' => esc_html__('Logged-in Share', 'wp-statistics'), 'value' => $data['glance']['logged_in']['value'], 'change' => $data['glance']['logged_in']['change']];
        }

        $metrics = apply_filters('wp_statistics_visitors_overview_glance_metrics', $metrics);

        View::load("components/objects/glance-card", ['metrics' => $metrics, 'two_column' => true]);
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
                            <td><a href="<?php echo esc_url(Menus::admin_url('visitors', array_merge(['tab' => 'visitors'], DateRange::get('7days', true)))); ?>"><span class="quickstats-values"><?php echo esc_html(ceil(Helper::divideNumbers($data['summary']['visitors']['7days'], 7, 1))); ?></span></a></td>
                            <td><a href="<?php echo esc_url(Menus::admin_url('visitors', array_merge(['tab' => 'views'], DateRange::get('7days', true)))); ?>"><span class="quickstats-values"><?php echo esc_html(ceil(Helper::divideNumbers($data['summary']['views']['7days'], 7, 1))); ?></span></a></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php
        if ($isTrackLoggedInUsersEnabled) {
            View::load("components/charts/horizontal-bar", [
                'title'        => esc_html__('Logged-in Users', 'wp-statistics'),
                'unique_id'    => 'visitors-logged-in-users',
                'footer_title' => esc_html__('View Logged-in Users', 'wp-statistics'),
                'footer_link'  => Menus::admin_url('visitors', ['tab' => 'logged-in-users'])
            ]);
        }

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
            <?php View::load("components/tables/top-referrers", ['data' => $data['referrers']]); ?>
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
        <div class="wps-card wps-card__traffic-trends">
            <div class="wps-card__title">
                <h2><?php esc_html_e('Traffic Trends', 'wp-statistics') ?></h2>
            </div>
            <?php View::load("components/charts/traffic-trends", ['chart_id' => 'trafficChart']); ?>
        </div>

        <?php if ($isActive): ?>
            <div class="wps-card">
                <div class="wps-card__title">
                    <h2><?php esc_html_e('Top Entry Pages', 'wp-statistics') ?></h2>
                </div>
                <?php
                View::load("components/tables/visitors-top-entry-pages", ['data' => $data['entry_pages']]);
                ?>
                <div class="wps-p-0">
                    <?php
                    View::load("components/objects/card-footer", [
                        'href'  => Menus::admin_url('pages', ['tab' => 'entry-pages']),
                        'title' => esc_html__('View Entry Pages', 'wp-statistics'),
                    ]);
                    ?>
                </div>
            </div>
        <?php endif; ?>

        <?php do_action('wp_statistics_visitors_overview_hourly_traffic_widget') ?>

        <div class="wps-card">
            <div class="wps-card__title">
                <h2><?php esc_html_e('Most Active Visitors', 'wp-statistics') ?></h2>
            </div>
            <?php
            View::load("components/tables/most-active-visitors", ['data' => $data['visitors'], 'isTodayOrFutureDate' => DateTime::isTodayOrFutureDate(DateRange::get()['to'])]);
            ?>
            <div class="wps-p-0">
                <?php
                View::load("components/objects/card-footer", [
                    'href'  => Menus::admin_url('visitors', ['tab' => 'top-visitors']),
                    'title' => esc_html__('View Top Visitors', 'wp-statistics'),
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
                View::load("metabox/global-visitor-distribution", ['data' => $data['map_chart']]);
                ?>
            </div>
        </div>
    </div>
</div>