<?php
/**
 * Network Admin > Statistics > Overview: one card per site with its key numbers
 * (last 7 days) and links to its reports.
 *
 * Args:
 *  - sites (array[]) From WP_STATISTICS\Network::getSitesOverview().
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

$sites = isset($sites) && is_array($sites) ? $sites : [];
?>
<div class="wrap wps-wrap wps-network-overview">
    <h1 class="wps-network-overview__title"><?php esc_html_e('Statistics', 'wp-statistics'); ?></h1>
    <p class="wps-network-overview__intro"><?php esc_html_e('Visitors and views of each site in the last 7 days. Open a site to see its full reports.', 'wp-statistics'); ?></p>

    <?php if (empty($sites)) : ?>
        <div class="wps-card wps-network-overview__empty">
            <p><?php esc_html_e('No sites found.', 'wp-statistics'); ?></p>
        </div>
    <?php else : ?>
        <ul class="wps-network-overview__list">
            <?php foreach ($sites as $site) : ?>
                <?php $headingId = 'wps-network-site-' . (int)$site['id']; ?>
                <li class="wps-card wps-network-site<?php echo empty($site['active']) ? ' wps-network-site--inactive' : ''; ?>" aria-labelledby="<?php echo esc_attr($headingId); ?>">
                    <div class="wps-network-site__head">
                        <h2 class="wps-network-site__name" id="<?php echo esc_attr($headingId); ?>"><bdi><?php echo esc_html($site['name'] !== '' ? $site['name'] : $site['url']); ?></bdi></h2>
                        <a class="wps-network-site__url" href="<?php echo esc_url($site['url']); ?>" target="_blank" rel="noopener"><bdi dir="ltr"><?php echo esc_html(untrailingslashit(preg_replace('#^https?://#', '', (string)$site['url']))); ?></bdi><span class="screen-reader-text"><?php esc_html_e('(opens in a new tab)', 'wp-statistics'); ?></span></a>
                    </div>

                    <?php if (empty($site['active'])) : ?>
                        <p class="wps-network-site__note"><?php esc_html_e('WP Statistics is not active on this site.', 'wp-statistics'); ?></p>
                    <?php else : ?>
                        <dl class="wps-network-site__numbers">
                            <div class="wps-network-site__number">
                                <dt><?php esc_html_e('Visitors', 'wp-statistics'); ?></dt>
                                <dd><?php echo $site['visitors'] === null ? '&mdash;' : esc_html(number_format_i18n($site['visitors'])); ?></dd>
                            </div>
                            <div class="wps-network-site__number">
                                <dt><?php esc_html_e('Views', 'wp-statistics'); ?></dt>
                                <dd><?php echo $site['views'] === null ? '&mdash;' : esc_html(number_format_i18n($site['views'])); ?></dd>
                            </div>
                        </dl>

                        <nav class="wps-network-site__links" aria-label="<?php echo esc_attr(sprintf(/* translators: %s: Site name. */ __('Reports for %s', 'wp-statistics'), $site['name'])); ?>">
                            <ul>
                                <?php foreach ($site['links'] as $index => $link) : ?>
                                    <li>
                                        <a href="<?php echo esc_url($link['url']); ?>"<?php echo $index === 0 ? ' class="button button-primary"' : ' class="button"'; ?>><?php echo esc_html($link['label']); ?></a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
