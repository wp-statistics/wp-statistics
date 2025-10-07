<?php
use WP_Statistics\Components\DateRange;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Option;
use WP_Statistics\Components\View;
use WP_STATISTICS\Helper;

$visitors     = $data['summary']['7days']['data']['current']['visitors'];
$prevVisitors = $data['summary']['7days']['data']['prev']['visitors'];
$views        = $data['summary']['7days']['data']['current']['hits'];
$prevViews    = $data['summary']['7days']['data']['prev']['hits'];
$userOnline   = new \WP_STATISTICS\UserOnline();
?>

<div class="wps-meta-traffic-summary">

    <?php if ($userOnline::active()) : ?>
        <div class="c-live">
            <div>
                <span class="c-live__status"></span>
                <span class="c-live__title"><?php esc_html_e('Online Users', 'wp-statistics'); ?></span>
            </div>
            <div class="c-live__online">
                <span class="c-live__online--value"><?php echo esc_html($data['online']) ?></span>
                <a class="c-live__value" href="<?php echo Menus::admin_url('visitors', ['tab' => 'online']) ?>" aria-label="<?php esc_attr_e('View online visitors', 'wp-statistics'); ?>"><span class="c-live__online--arrow"></span></a>
            </div>
        </div>
    <?php endif ?>

    <div class="o-wrap">
        <div class="wps-postbox-chart--title">
            <span class="wps-chart--title"><?php esc_html_e('Last 7 days (exclude today)', 'wp-statistics'); ?></span>
            <div class="wps-postbox-chart--info">
                <div class="wps-postbox-chart--previousPeriod">
                    <?php esc_html_e('Previous period', 'wp-statistics') ?>
                </div>
            </div>
        </div>

        <div class="wps-postbox-chart--data">
            <div class="wps-postbox-chart--items"></div>
            <div class="wps-postbox-chart--items">
                <div class="wps-postbox-chart--item__data">
                    <div class="wps-postbox-chart--item">
                        <span><span class="wps-postbox-chart--item--color"></span><?php esc_html_e('Visitors', 'wp-statistics'); ?></span>
                        <div>
                            <div class="current-data">
                                <span><?php echo esc_html(Helper::formatNumberWithUnit($visitors, 1)) ?></span>
                                <span class="current-data-percent diffs__change <?php echo $visitors > $prevVisitors ? 'plus' : ''; ?> <?php echo $visitors < $prevVisitors ? 'minus' : ''; ?>">
                                    <span class="diffs__change__direction">
                                        <?php echo esc_html(Helper::calculatePercentageChange($prevVisitors, $visitors, 1, true)) ?>%
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="wps-postbox-chart--item">
                        <span><span class="wps-postbox-chart--item--color"></span><?php esc_html_e('Views', 'wp-statistics'); ?></span>
                        <div>
                            <div class="current-data">
                                <span><?php echo esc_html(Helper::formatNumberWithUnit($views, 1)) ?></span>
                                <span class="current-data-percent diffs__change <?php echo $views > $prevViews ? 'plus' : ''; ?> <?php echo $views < $prevViews ? 'minus' : ''; ?>">
                                    <span class="diffs__change__direction">
                                        <?php echo esc_html(Helper::calculatePercentageChange($prevViews, $views, 1, true)) ?>%
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="wps-postbox-chart--container">
            <p class="screen-reader-text">
                <?php echo esc_html__('Traffic overview chart', 'wp-statistics') ?>
            </p>
            <canvas id="wp-statistics-quickstats-widget-chart" aria-labelledby="Traffic overview chart" role="img" height="166"></canvas>
        </div>
    </div>

    <div class="o-table-wrapper">
        <table width="100%" class="o-table o-table--wps-summary-stats">
            <thead>
                <tr>
                    <th width="50%" scope="col">
                        <span class="screen-reader-text"><?php esc_html_e('Date range', 'wp-statistics'); ?></span>
                    </th>
                    <th scope="col"><?php esc_html_e('Visitors', 'wp-statistics'); ?></th>
                    <th scope="col"><?php esc_html_e('Views', 'wp-statistics'); ?></th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($data['summary'] as $key => $item) :
                    $currentVisitors = $item['data']['current']['visitors'];
                    $prevVisitors    = $item['data']['prev']['visitors'] ?? null;
                    $currentHits     = $item['data']['current']['hits'];
                    $prevHits        = $item['data']['prev']['hits'] ?? null;
                ?>
                    <tr>
                        <td>
                            <?php echo esc_html($item['label']); ?>

                            <?php if (isset($item['tooltip'])) : ?>
                                <span class="wps-tooltip" title="<?php echo esc_html($item['tooltip']); ?>"><i class="wps-tooltip-icon info"></i></span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <div>
                                <a href="<?php echo Menus::admin_url('visitors', array_merge(['tab' => 'visitors'], DateRange::get($key, !empty($item['today_excluded'])))) ?>"><span class="quickstats-values" title="<?php echo esc_attr($currentVisitors); ?>"><?php echo esc_html(Helper::formatNumberWithUnit($currentVisitors, 1)) ?></span></a>

                                <?php if (isset($currentVisitors) && isset($prevVisitors)) : ?>
                                    <div class="diffs__change <?php echo $currentVisitors > $prevVisitors ? 'plus' : ''; ?> <?php echo $currentVisitors < $prevVisitors ? 'minus' : ''; ?>">
                                        <span class="diffs__change__direction"><?php echo esc_html(Helper::calculatePercentageChange($prevVisitors, $currentVisitors, 1, true)) ?>%</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </td>

                        <td>
                            <div>
                                <a href="<?php echo Menus::admin_url('visitors', array_merge(['tab' => 'views'], DateRange::get($key, !empty($item['today_excluded'])))) ?>"><span class="quickstats-values" title="<?php echo esc_attr($currentHits); ?>"><?php echo esc_html(Helper::formatNumberWithUnit($currentHits, 1)) ?></span></a>

                                <?php if (isset($currentHits) && isset($prevHits)) : ?>
                                    <div class="diffs__change <?php echo $currentHits > $prevHits ? 'plus' : ''; ?> <?php echo $currentHits < $prevHits ? 'minus' : ''; ?>">
                                         <span class="diffs__change__direction"><?php echo esc_html(Helper::calculatePercentageChange($prevHits, $currentHits, 1, true)) ?>%</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php
    if (!Option::get('time_report') && !in_array('enable_email_metabox_notice', get_option('wp_statistics_dismissed_notices', []))) {
        View::load("components/meta-box/enable-mail");
    }
    ?>
</div>