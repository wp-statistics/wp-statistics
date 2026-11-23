<?php
/**
 * Compact activity list ("feed"): one row per visitor or event, with a round flag or icon,
 * two lines of text and a count with a time on the side. Under ~380px wide (a sidebar)
 * the rows switch to a short form by CSS, so the same markup works in both places.
 *
 * Used by Data Plus "Statistics - Latest Visitors" (meta box and block editor panel).
 *
 * @var array  $items Rows. Keys (all optional except `title`):
 *                    - `image`      Flag or icon URL.
 *                    - `title`      Bold first line, e.g. "Poland".
 *                    - `meta`       Rest of the first line, e.g. "Safari on macOS" (hidden when narrow).
 *                    - `subtitle`   Second line, e.g. "from google.com".
 *                    - `detail`     Added to the second line, e.g. "Organic Search" (hidden when narrow).
 *                    - `count`      Number on the side, e.g. 5.
 *                    - `count_label` Count with its unit, e.g. "5 views" (wide layout).
 *                    - `time`       Full time, e.g. "Nov 21, 4:38 pm" (wide layout, under the count).
 *                    - `time_short` Short time for the narrow layout, e.g. "4:38 pm".
 *                    - `url`        Row link.
 *                    - `tooltip`    Row title attribute.
 * @var string $footer_text      Left side of the footer, e.g. "53 visitors this week".
 * @var string $footer_url       "View all" link.
 * @var string $footer_link_text Defaults to "View all".
 * @var string $target           Link target, e.g. "_blank".
 * @var string $empty_message    Shown when there are no items.
 * @var string $class            Extra classes.
 */

if (!defined('ABSPATH')) exit;

use WP_Statistics\Components\View;

$items       = !empty($items) && is_array($items) ? $items : [];
$target      = !empty($target) ? $target : '';
$rel         = $target === '_blank' ? 'noopener' : '';
$arrow       = is_rtl() ? '&larr;' : '&rarr;';
$sep         = '<span class="wps-activity-feed__sep" aria-hidden="true"> · </span>';
$linkText    = !empty($footer_link_text) ? $footer_link_text : __('View all', 'wp-statistics');
$extraClass  = !empty($class) ? ' ' . $class : '';
?>
<div class="wps-activity-feed<?php echo esc_attr($extraClass); ?>">
    <?php if (empty($items)) : ?>
        <?php View::load('components/objects/empty-state', [
            'message' => !empty($empty_message) ? $empty_message : __('No activity yet.', 'wp-statistics'),
            'range'   => false,
            'icon'    => 'activity',
        ]); ?>
    <?php else : ?>
        <ul class="wps-activity-feed__list">
            <?php foreach ($items as $item) : ?>
                <?php
                $tag   = !empty($item['url']) ? 'a' : 'div';
                $attrs = '';
                if ($tag === 'a') {
                    $attrs .= ' href="' . esc_url($item['url']) . '"';
                    $attrs .= $target ? ' target="' . esc_attr($target) . '"' : '';
                    $attrs .= $rel ? ' rel="' . esc_attr($rel) . '"' : '';
                }
                $attrs .= !empty($item['tooltip']) ? ' title="' . esc_attr($item['tooltip']) . '"' : '';
                ?>
                <li class="wps-activity-feed__item">
                    <<?php echo $tag . $attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> class="wps-activity-feed__row">
                        <span class="wps-activity-feed__avatar" aria-hidden="true">
                            <?php if (!empty($item['image'])) : ?>
                                <img src="<?php echo esc_url($item['image']); ?>" alt="" loading="lazy">
                            <?php endif; ?>
                        </span>
                        <span class="wps-activity-feed__text">
                            <span class="wps-activity-feed__line">
                                <b class="wps-activity-feed__title"><?php echo esc_html($item['title'] ?? ''); ?></b><?php
                                if (!empty($item['meta'])) :
                                    ?><span class="wps-activity-feed__wide"><?php echo $sep; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo esc_html($item['meta']); ?></span><?php
                                endif;
                                ?>
                            </span>
                            <?php if (!empty($item['subtitle']) || !empty($item['time_short'])) : ?>
                                <span class="wps-activity-feed__line wps-activity-feed__sub"><?php
                                    echo esc_html($item['subtitle'] ?? '');
                                    if (!empty($item['detail'])) :
                                        ?><span class="wps-activity-feed__wide"><?php echo $sep; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo esc_html($item['detail']); ?></span><?php
                                    endif;
                                    if (!empty($item['time_short'])) :
                                        ?><span class="wps-activity-feed__narrow"><?php echo !empty($item['subtitle']) ? $sep : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo esc_html($item['time_short']); ?></span><?php
                                    endif;
                                ?></span>
                            <?php endif; ?>
                        </span>
                        <span class="wps-activity-feed__aside">
                            <?php if (isset($item['count'])) : ?>
                                <b class="wps-activity-feed__wide"><?php echo esc_html($item['count_label'] ?? number_format_i18n(intval($item['count']))); ?></b>
                                <b class="wps-activity-feed__narrow"><?php echo esc_html(number_format_i18n(intval($item['count']))); ?></b>
                            <?php endif; ?>
                            <?php if (!empty($item['time'])) : ?>
                                <span class="wps-activity-feed__wide wps-activity-feed__time"><?php echo esc_html($item['time']); ?></span>
                            <?php endif; ?>
                        </span>
                    </<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php if (!empty($footer_text) || !empty($footer_url)) : ?>
        <div class="wps-activity-feed__footer">
            <span><?php echo esc_html($footer_text ?? ''); ?></span>
            <?php if (!empty($footer_url)) : ?>
                <a href="<?php echo esc_url($footer_url); ?>"<?php echo $target ? ' target="' . esc_attr($target) . '"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
                    <?php echo esc_html($linkText); ?> <span aria-hidden="true"><?php echo $arrow; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
