<?php
use WP_STATISTICS\Menus;
use WP_Statistics\Components\View;
use WP_STATISTICS\Helper;
?>

<div class="wps-postbox-two-col">
    <div class="postbox">
        <?php
            View::load("components/objects/card-header", [
                'title' => esc_html__('Top Browsers', 'wp-statistics'),
            ]);
        ?>
        <div class="inside">
            <?php if (!empty($data['browsers']['data'])) : ?>
                <div class="o-table-wrapper">
                    <table width="100%" class="o-table wps-new-table">
                        <thead>
                            <tr>
                                <th class="wps-pd-l">
                                    <?php esc_html_e('Browser', 'wp-statistics'); ?>
                                </th>
                                <th class="wps-pd-l">
                                    <span class="wps-order"><?php esc_html_e('Visitors', 'wp-statistics'); ?></span>
                                </th>
                                <th class="wps-pd-l">
                                    %
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for ($i = 0; $i < count($data['browsers']['data']); $i++) :
                                $icon       = $data['browsers']['icons'][$i];
                                $visitors   = $data['browsers']['data'][$i];
                                $browser    = $data['browsers']['labels'][$i];

                                if ($browser == esc_html__('Other', 'wp-statistics')) continue;
                            ?>
                                <tr>
                                    <td class="wps-pd-l">
                                        <span title="<?php echo esc_attr($browser) ?>" class="wps-browser-name">
                                            <?php if (!empty($icon)) : ?>
                                                <img alt="<?php echo esc_attr($browser) ?>" src="<?php echo esc_url($icon) ?>" title="<?php echo esc_attr($browser) ?>" class="log-tools wps-flag">
                                            <?php endif; ?>
                                            <a href="<?php echo esc_url(Menus::admin_url('devices', ['type' => 'single-browser', 'browser' => $browser])) ?>"><?php echo esc_html($browser) ?></a>
                                        </span>
                                    </td>
                                    <td class="wps-pd-l">
                                        <?php echo esc_html(number_format_i18n($visitors)); ?>
                                    </td>
                                    <td class="wps-pd-l">
                                        <?php echo esc_html(Helper::calculatePercentage($visitors, array_sum($data['browsers']['data'])) . '%') ?>
                                    </td>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
            <?php else : ?>
                <div class="o-wrap o-wrap--no-data wps-center">
                    <?php echo esc_html(Helper::getNoDataMessage()); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
            View::load("components/objects/card-footer", [
                'href'  => add_query_arg(['tab' => 'browsers'], Menus::admin_url('devices')),
                'title' => __('View Browsers', 'wp-statistics'),
            ]);
        ?>
    </div>


    <!-- Top OS -->
    <div class="postbox">
        <?php
            View::load("components/objects/card-header", [
                'title' => __('Top OS', 'wp-statistics'),
            ]);
        ?>
        <div class="inside">
            <?php if (!empty($data['os']['data'])) : ?>
                <div class="o-table-wrapper">
                    <table width="100%" class="o-table wps-new-table">
                        <thead>
                            <tr>
                                <th class="wps-pd-l">
                                    <?php esc_html_e('OS', 'wp-statistics'); ?>
                                </th>
                                <th class="wps-pd-l">
                                    <span class="wps-order"><?php esc_html_e('Visitors', 'wp-statistics'); ?></span>
                                </th>
                                <th class="wps-pd-l">
                                    %
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for ($i = 0; $i < count($data['os']['data']); $i++) :
                                $icon       = $data['os']['icons'][$i];
                                $visitors   = $data['os']['data'][$i];
                                $os         = $data['os']['labels'][$i];

                                if ($os == esc_html__('Other', 'wp-statistics')) continue;
                            ?>
                                <tr>
                                    <td class="wps-pd-l">
                                        <span title="<?php echo esc_attr($os) ?>" class="wps-platform-name">
                                            <img alt="<?php echo esc_attr($os) ?>" src="<?php echo esc_url($icon) ?>" title="<?php echo esc_attr($os) ?>" class="log-tools wps-flag">
                                            <?php echo esc_html($os); ?>
                                        </span>
                                    </td>
                                    <td class="wps-pd-l">
                                        <?php echo esc_html(number_format_i18n($visitors)); ?>
                                    </td>
                                    <td class="wps-pd-l">
                                        <?php echo esc_html(Helper::calculatePercentage($visitors, array_sum($data['os']['data'])) . '%') ?>
                                    </td>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
            <?php else : ?>
                <div class="o-wrap o-wrap--no-data wps-center">
                    <?php echo esc_html(Helper::getNoDataMessage()); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
            View::load("components/objects/card-footer", [
                'href'  => add_query_arg(['tab' => 'platforms'], Menus::admin_url('devices')),
                'title' => __('View OS', 'wp-statistics'),
            ]);
        ?>
    </div>

    <!-- Device Models-->
    <div class="postbox">
        <?php
            View::load("components/objects/card-header", [
                'title' => __('Device Models', 'wp-statistics'),
            ]);
        ?>
        <div class="inside">
            <?php if (!empty($data['models']['data'])) : ?>
                <div class="o-table-wrapper">
                    <table width="100%" class="o-table wps-new-table">
                        <thead>
                            <tr>
                                <th class="wps-pd-l">
                                    <?php esc_html_e('Model', 'wp-statistics'); ?>
                                </th>
                                <th class="wps-pd-l">
                                    <span class="wps-order"><?php esc_html_e('Visitors', 'wp-statistics'); ?></span>
                                </th>
                                <th class="wps-pd-l">
                                    %
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for ($i = 0; $i < count($data['models']['data']); $i++) :
                                $visitors   = $data['models']['data'][$i];
                                $model      = $data['models']['labels'][$i];

                                if ($model == esc_html__('Other', 'wp-statistics')) continue;
                            ?>
                                <tr>
                                    <td class="wps-pd-l">
                                        <span title="<?php echo esc_attr($model); ?>" class="wps-model-name">
                                            <?php echo esc_html($model); ?>
                                        </span>
                                    </td>
                                    <td class="wps-pd-l">
                                        <?php echo esc_html(number_format_i18n($visitors)); ?>
                                    </td>
                                    <td class="wps-pd-l">
                                        <?php echo esc_html(Helper::calculatePercentage($visitors, array_sum($data['models']['data'])) . '%') ?>
                                    </td>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="o-wrap o-wrap--no-data wps-center">
                    <?php echo esc_html(Helper::getNoDataMessage()); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
            View::load("components/objects/card-footer", [
                'href'  => add_query_arg(['tab' => 'models'], Menus::admin_url('devices')),
                'title' => __('View Device Models', 'wp-statistics'),
            ]);
        ?>
    </div>


    <!-- Device Categories -->
    <div class="postbox">
        <?php
            View::load("components/objects/card-header", [
                'title' => __('Device Categories', 'wp-statistics'),
            ]);
        ?>
        <div class="inside">
            <?php if (!empty($data['devices']['data'])) : ?>
                <div class="o-table-wrapper">
                    <table width="100%" class="o-table wps-new-table">
                        <thead>
                            <tr>
                                <th class="wps-pd-l">
                                    <?php esc_html_e('Category', 'wp-statistics'); ?>
                                </th>
                                <th class="wps-pd-l">
                                    <span class="wps-order"><?php esc_html_e('Visitors', 'wp-statistics'); ?></span>
                                </th>
                                <th class="wps-pd-l">
                                    %
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for ($i = 0; $i < count($data['devices']['data']); $i++) :
                                $visitors   = $data['devices']['data'][$i];
                                $device     = $data['devices']['labels'][$i];

                                if ($device == esc_html__('Other', 'wp-statistics')) continue;
                            ?>
                                <tr>
                                    <td class="wps-pd-l">
                                        <span title="<?php echo esc_attr($device); ?>" class="wps-model-name">
                                            <?php echo esc_html($device); ?>
                                        </span>
                                    </td>
                                    <td class="wps-pd-l">
                                        <?php echo esc_html(number_format_i18n($visitors)); ?>
                                    </td>
                                    <td class="wps-pd-l">
                                        <?php echo esc_html(Helper::calculatePercentage($visitors, array_sum($data['devices']['data'])) . '%') ?>
                                    </td>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
            <?php else : ?>
                <div class="o-wrap o-wrap--no-data wps-center">
                    <?php echo esc_html(Helper::getNoDataMessage()); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
            View::load("components/objects/card-footer", [
                'href'  => add_query_arg(['tab' => 'categories'], Menus::admin_url('devices')),
                'title' => __('View Device Categories', 'wp-statistics'),
            ]);
        ?>
    </div>
</div>