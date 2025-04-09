<?php

use WP_STATISTICS\Country;
use WP_STATISTICS\Menus;
use WP_Statistics\Components\View;

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
    <?php
    $title = __('No data found for this date range.', 'wp-statistics');
    if ($isTodayOrFutureDate) {
        $title = __('Data coming soon!', 'wp-statistics');
    }
    View::load("components/objects/no-data", [
        'url'   => WP_STATISTICS_URL . 'assets/images/no-data/vector-3.svg',
        'title' => $title
    ]);
    ?>
<?php endif; ?>