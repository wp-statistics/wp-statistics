<?php
use WP_STATISTICS\Menus;
use WP_STATISTICS\Option;
use WP_Statistics\Components\View;
use WP_STATISTICS\Helper;
?>

<div class="wps-meta-traffic-summary">
    <?php if (isset($data['online'])) : ?>
        <div class="c-live">
            <div>
                <span class="c-live__status"></span>
                <span class="c-live__title"><?php esc_html_e('Online Visitors', 'wp-statistics'); ?></span>
            </div>
            <div class="c-live__online">
                <span class="c-live__online--value"><?php echo esc_html($data['online']) ?></span>
                <a class="c-live__value" href="<?php echo Menus::admin_url('visitors', ['tab' => 'online']) ?>" aria-label="<?php esc_attr_e('View online visitors', 'wp-statistics'); ?>"><span class="c-live__online--arrow"></span></a>
            </div>
        </div>
    <?php endif ?>
    <div class="o-table-wrapper">
        <table width="100%" class="o-table o-table--wps-summary-stats">
            <thead>
            <tr>
                <th width="50%"><?php esc_html_e('Timeframe', 'wp-statistics'); ?></th>
                <th><?php esc_html_e('Visitors', 'wp-statistics'); ?></th>
                <th><?php esc_html_e('Views', 'wp-statistics'); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($data['summary'] as $key => $item) :
                    $itemData = $item['data'];
                ?>
                <tr>
                    <td>
                        <?php echo esc_html($item['label']); ?>

                        <?php if (isset($item['tooltip'])) : ?>
                            <span class="wps-tooltip" title="<?php echo esc_attr($item['tooltip']); ?>"><i class="wps-tooltip-icon info"></i></span>
                        <?php endif; ?>
                    </td>

                    <td>
                        <div>
                            <a href="<?php echo Menus::admin_url('visitors', array_merge(['tab' => 'visitors'], $item['date'])) ?>"><span class="quickstats-values" title="<?php echo esc_attr($itemData['current']['visitors']); ?>"><?php echo esc_html(Helper::formatNumberWithUnit($itemData['current']['visitors'], 1)) ?></span></a>
                            <?php if (!empty($item['comparison'])) : ?>
                                <div class="diffs__change <?php echo esc_attr($itemData['trend']['visitors']['direction']); ?>">
                                    <span class="diffs__change__direction"><?php echo esc_html($itemData['trend']['visitors']['percentage']) ?>%</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <div>
                            <a href="<?php echo Menus::admin_url('visitors', array_merge(['tab' => 'views'], $item['date'])) ?>"><span class="quickstats-values" title="<?php echo esc_attr($itemData['current']['views']); ?>"><?php echo esc_html(Helper::formatNumberWithUnit($itemData['current']['views'], 1)) ?></span></a>
                            <?php if (!empty($item['comparison'])) : ?>
                                <div class="diffs__change <?php echo esc_attr($itemData['trend']['views']['direction']); ?>">
                                    <span class="diffs__change__direction"><?php echo esc_html($itemData['trend']['views']['percentage']) ?>%</span>
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