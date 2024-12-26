<?php
use WP_Statistics\Components\DateRange;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Option;
use WP_Statistics\Components\View;
?>

<div class="wps-meta-traffic-summary">
    <div class="c-live">
        <div>
            <span class="c-live__status"></span>
            <span class="c-live__title"><?php esc_html_e('Online Visitors', 'wp-statistics'); ?></span>
        </div>
        <div class="c-live__online">
            <span class="c-live__online--value"><?php echo esc_html($data['online']) ?></span>
            <a class="c-live__value" href="<?php echo Menus::admin_url('visitors', ['tab' => 'online']) ?>"><span class="c-live__online--arrow"></span></a>
        </div>
    </div>
    <div class="o-table-wrapper">
        <table width="100%" class="o-table o-table--wps-summary-stats">
            <thead>
                <tr>
                    <th width="50%"><?php esc_html_e('Time', 'wp-statistics'); ?></th>
                    <th><?php esc_html_e('Visitors', 'wp-statistics'); ?></th>
                    <th><?php esc_html_e('Views', 'wp-statistics'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php for ($i = 0; $i < 12; $i++) :
                    $key        = $data['keys'][$i];
                    $label      = $data['labels'][$i];
                    $visitors   = $data['visitors'][$i];
                    $views      = $data['views'][$i];
                ?>
                    <tr>
                        <td><b><?php echo esc_html($label); ?></b></td>
                        <td><a href="<?php echo Menus::admin_url('visitors', array_merge(['tab' => 'visitors'], DateRange::get($key))); ?>"><span class="quickstats-values"><?php echo esc_html(number_format_i18n($visitors)) ?></span></a></td>
                        <td><a href="<?php echo Menus::admin_url('visitors', array_merge(['tab' => 'views'], DateRange::get($key))); ?>"><span class="quickstats-values"><?php echo esc_html(number_format_i18n($views)) ?></span></a></td>
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table>
    </div>

    <?php if (!Option::get('time_report'))  {
        View::load("components/meta-box/enable-mail", ['url' => Menus::admin_url('settings')]);
    } ?>
</div>