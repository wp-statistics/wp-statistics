<?php
use WP_STATISTICS\Country;
use WP_STATISTICS\Menus;
?>

<?php if (!empty($data)) : ?>
    <div class="o-table-wrapper">
        <table width="100%" class="o-table wps-new-table">
            <thead>
                <tr>
                    <th class="wps-pd-l"><?php esc_html_e('Country', 'wp-statistics'); ?></th>
                    <th class="wps-pd-l"><span class="wps-order"><?php esc_html_e('Visitors', 'wp-statistics'); ?></span></th>
                </tr>
            </thead>

            <tbody>
                <?php
                    foreach ($data as $item) :
                        if ($item->country == Country::$unknown_location) {
                            continue;
                        }
                ?>
                    <tr>
                        <td class="wps-pd-l">
                            <div class="wps-country-flag wps-ellipsis-parent">
                                <img src="<?php echo esc_url(Country::flag($item->country)) ?>" title="<?php echo esc_attr(Country::getName($item->country)) ?>" class="wps-flag wps-flag--first">
                                <span class="wps-ellipsis-text" title="<?php echo esc_attr(Country::getName($item->country)) ?>"><?php echo esc_html(Country::getName($item->country)) ?></span>
                            </div>
                        </td>

                        <td class="wps-pd-l wps-middle-vertical"><a href="<?php echo Menus::admin_url('visitors', ['location' => $item->country, 'from' => $filters['date']['from'], 'to' => $filters['date']['to']]) ?>" target="_blank"><?php echo esc_html($item->visitors) ?></a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else : ?>
    <div class="o-wrap o-wrap--no-data wps-center">
        <?php esc_html_e('No recent data available.', 'wp-statistics') ?>
    </div>
<?php endif; ?>