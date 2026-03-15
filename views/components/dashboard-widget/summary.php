<?php
/**
 * Dashboard Widget Summary Template.
 *
 * @var array  $metrics       Formatted metric values and change percentages.
 * @var array  $top_pages     Top pages array [{title, views, url}].
 * @var array  $top_referrers Top referrers array [{domain, visitors}].
 * @var string $overview_url  URL to the full overview page.
 * @var bool   $has_data      Whether any data exists.
 */

defined('ABSPATH') || exit;
?>
<div class="wps-dw">

    <div class="wps-dw-metrics">
        <div class="wps-dw-metric">
            <div class="wps-dw-metric-value"><?php echo esc_html($metrics['visitors_today']); ?></div>
            <div class="wps-dw-metric-label"><?php esc_html_e('Visitors today', 'wp-statistics'); ?></div>
            <?php if (!empty($metrics['visitors_change'])) : ?>
                <span class="wps-dw-metric-change <?php echo $metrics['visitors_change'] > 0 ? 'wps-dw-metric-change--up' : 'wps-dw-metric-change--down'; ?>">
                    <?php echo esc_html(($metrics['visitors_change'] > 0 ? '+' : '') . $metrics['visitors_change'] . '%'); ?>
                </span>
            <?php endif; ?>
        </div>

        <div class="wps-dw-metric">
            <div class="wps-dw-metric-value"><?php echo esc_html($metrics['views_today']); ?></div>
            <div class="wps-dw-metric-label"><?php esc_html_e('Views today', 'wp-statistics'); ?></div>
            <?php if (!empty($metrics['views_change'])) : ?>
                <span class="wps-dw-metric-change <?php echo $metrics['views_change'] > 0 ? 'wps-dw-metric-change--up' : 'wps-dw-metric-change--down'; ?>">
                    <?php echo esc_html(($metrics['views_change'] > 0 ? '+' : '') . $metrics['views_change'] . '%'); ?>
                </span>
            <?php endif; ?>
        </div>

        <div class="wps-dw-metric">
            <div class="wps-dw-metric-value"><?php echo esc_html($metrics['visitors_month']); ?></div>
            <div class="wps-dw-metric-label"><?php esc_html_e('Visitors this month', 'wp-statistics'); ?></div>
        </div>
    </div>

    <?php if ($has_data && !empty($top_pages)) : ?>
        <div class="wps-dw-section">
            <div class="wps-dw-section-header">
                <h4 class="wps-dw-section-title"><?php esc_html_e('Top pages', 'wp-statistics'); ?></h4>
                <span class="wps-dw-section-period"><?php esc_html_e('Last 28 days', 'wp-statistics'); ?></span>
            </div>
            <ul class="wps-dw-list">
                <?php foreach ($top_pages as $index => $page) : ?>
                    <li>
                        <span class="wps-dw-list-index"><?php echo esc_html($index + 1); ?></span>
                        <?php if (!empty($page['url'])) : ?>
                            <a class="wps-dw-list-title" href="<?php echo esc_url($page['url']); ?>"><?php echo esc_html($page['title']); ?></a>
                        <?php else : ?>
                            <span class="wps-dw-list-title"><?php echo esc_html($page['title']); ?></span>
                        <?php endif; ?>
                        <span class="wps-dw-list-count"><?php echo esc_html(number_format_i18n($page['views'])); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($has_data && !empty($top_referrers)) : ?>
        <div class="wps-dw-section">
            <div class="wps-dw-section-header">
                <h4 class="wps-dw-section-title"><?php esc_html_e('Top referrers', 'wp-statistics'); ?></h4>
                <span class="wps-dw-section-period"><?php esc_html_e('Last 28 days', 'wp-statistics'); ?></span>
            </div>
            <ul class="wps-dw-list">
                <?php foreach ($top_referrers as $index => $referrer) : ?>
                    <li>
                        <span class="wps-dw-list-index"><?php echo esc_html($index + 1); ?></span>
                        <span class="wps-dw-list-title"><?php echo esc_html($referrer['domain']); ?></span>
                        <span class="wps-dw-list-count"><?php echo esc_html(number_format_i18n($referrer['visitors'])); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!$has_data) : ?>
        <div class="wps-dw-empty">
            <?php esc_html_e('No data recorded yet. Stats will appear once visitors start browsing your site.', 'wp-statistics'); ?>
        </div>
    <?php endif; ?>

    <div class="wps-dw-footer">
        <a href="<?php echo esc_url($overview_url); ?>">
            <?php esc_html_e('View full report', 'wp-statistics'); ?> &rarr;
        </a>
    </div>

</div>
