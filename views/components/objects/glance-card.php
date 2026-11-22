<?php
/**
 * At a Glance card.
 *
 * Each metric: label, value, optional change, plus:
 *  - polarity: 'positive' (default) | 'negative' (lower is better) | 'neutral' (grey chip)
 *  - ring:     numeric percent; draws an 18px ring before the value (rate tiles)
 *  - hint:     true prints "Lower is better" under a negative-polarity metric
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Components\View;
?>
<div class="wps-card">
    <div class="wps-card__title">
        <h2><?php esc_html_e('At a Glance', 'wp-statistics'); ?></h2>
    </div>
    <div class="inside">
        <div class="wps-at-a-glance <?php echo isset($two_column) && $two_column ? 'wps-at-a-glance__two-col' : ''; ?>">
            <?php if (!empty($metrics) && is_array($metrics)): ?>
                <?php foreach ($metrics as $metric): ?>
                    <div class="wps-at-a-glance-item">
                        <!-- Metric Label -->
                        <span class="wps-at-a-glance-label" title="<?php echo esc_html($metric['label'] ?? ''); ?>">
                               <?php if (!empty($metric['icon'])): ?>
                                   <i class="wps-at-a-glance-icon <?php echo esc_attr($metric['icon']); ?>" aria-hidden="true" aria-label="<?php echo esc_attr($metric['label']); ?>"></i>
                               <?php endif; ?>
                            <?php if (!empty($metric['link-href'])): ?>
                                <a href="<?php echo esc_url($metric['link-href']); ?>"
                                   title="<?php echo esc_attr($metric['label']); ?>">
                                    <?php echo esc_html($metric['label'] ?? ''); ?>
                                </a>
                            <?php else: ?>
                                <?php echo esc_html($metric['label'] ?? ''); ?>
                            <?php endif; ?>
                            <?php if (!empty($metric['tooltip'])): ?>
                                <span class="wps-tooltip" title="<?php echo esc_html($metric['tooltip']); ?>">
                                    <i class="wps-tooltip-icon info"></i>
                                </span>
                            <?php endif; ?>
                        </span>
                        <span class="wps-at-a-glance-value<?php echo !empty($metric['link-href']) && !empty($metric['link-title']) ? ' wps-at-a-glance-link' : ''; ?>">
                            <?php if (!empty($metric['link-href']) && !empty($metric['link-title'])): ?>
                                <a href="<?php echo esc_url($metric['link-href']); ?>" title="<?php echo esc_html($metric['link-title'] ?? 'View Details'); ?>" target="_blank" class="wps-external-link">
                                    <?php echo esc_html($metric['link-title'] ?? 'View Details'); ?>
                                </a>


                            <?php elseif (isset($metric['score'])): ?>
                                <span title="<?php echo esc_html($metric['score']); ?>" class="wps-at-a-glance-score">
                                    <?php echo esc_html($metric['score']); ?><span> /100</span>
                                </span>

                            <?php elseif (isset($metric['value'])): ?>
                                <span title="<?php echo esc_html($metric['value'] ?? 'No data'); ?>"<?php echo isset($metric['ring']) ? ' class="wps-at-a-glance-ring"' : ''; ?>>
                                    <?php if (isset($metric['ring']) && is_numeric($metric['ring'])): ?>
                                        <?php View::load('components/objects/percent-bar', ['value' => (float) $metric['ring'], 'variant' => 'ring', 'show_value' => false]); ?>
                                    <?php endif; ?>
                                    <?php if ($metric['value']): ?>
                                        <?php echo esc_html($metric['value']); ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </span>
                            <?php else: ?>
                                <span>-</span>
                            <?php endif; ?>
                            <?php if (isset($metric['change']) && $metric['change'] !== null && (!empty($metric['link-title']) || !empty($metric['value']))): ?>
                                <?php
                                // Legacy classes kept as aliases for add-ons and custom CSS.
                                $arrow_class = $metric['change'] > 0 ? 'wps-glance-up' : ($metric['change'] < 0 ? 'wps-glance-down' : '');
                                $polarity    = $metric['polarity'] ?? 'positive';
                                $color_class = '';
                                if ($metric['change'] != 0 && $polarity !== 'neutral') {
                                    $is_good_change = ($polarity === 'negative') ? $metric['change'] < 0 : $metric['change'] > 0;
                                    $color_class    = $is_good_change ? 'wps-glance-positive' : 'wps-glance-negative';
                                }
                                View::load('components/objects/change-chip', [
                                    'change'   => (float) $metric['change'],
                                    'polarity' => $polarity,
                                    'class'    => trim('wps-at-a-glance-change ' . $arrow_class . ' ' . $color_class),
                                ]);
                                ?>
                            <?php endif; ?>
                        </span>
                        <?php if (!empty($metric['hint']) && ($metric['polarity'] ?? '') === 'negative'): ?>
                            <span class="wps-change-hint"><?php esc_html_e('Lower is better', 'wp-statistics'); ?></span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p><?php esc_html_e('No metrics available.', 'wp-statistics'); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>