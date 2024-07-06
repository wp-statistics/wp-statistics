<?php 
use WP_STATISTICS\Country;
?>

<div class="wps-card">
    <div class="wps-card__title">
        <h2>
            <?php echo esc_html($title); ?>
            <?php if ($tooltip): ?>
                <span class="wps-tooltip" title="<?php echo esc_attr($tooltip); ?>"><i class="wps-tooltip-icon info"></i></span>
            <?php endif ?>
        </h2>
    </div>
    <div class="inside">
        <?php if (!empty($data)) : ?>
            <div class="o-table-wrapper">
                <table width="100%" class="o-table wps-authors-table wps-top-countries-table">
                    <thead>
                        <tr>
                            <th class="wps-pd-l">
                                <?php esc_html_e('Country', 'wp-statistics') ?>
                            </th>
                            <th class="wps-pd-l">
                                <?php esc_html_e('Visitors', 'wp-statistics') ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $item) : ?>
                            <tr>
                                <td class="wps-pd-l">
                                    <div >
                                        <img class="wps-flag" src="<?php echo esc_url(Country::flag($item->country)) ?>" alt="<?php echo esc_attr(Country::getName($item->country)) ?>">
                                        <?php echo esc_html(Country::getName($item->country)); ?>
                                    </div>
                                </td>
                                <td class="wps-pd-l">
                                    <span><?php echo esc_html(number_format($item->visitors)); ?></span>
                                </td>
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

    </div>
</div>