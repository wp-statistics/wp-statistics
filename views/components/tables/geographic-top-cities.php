<?php
use WP_STATISTICS\Country;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;
?>

<div class="wps-card">
    <div class="wps-card__title">
        <h2>
            <?php echo esc_html__('Top Cities', 'wp-statistics') ?>
        </h2>
    </div>
    <div class="inside">
        <?php if (!empty($data)) : ?>
            <div class="o-table-wrapper">
                <table width="100%" class="o-table wps-new-table">
                    <thead>
                        <tr>
                            <th class="wps-pd-l">
                                <?php esc_html_e('City', 'wp-statistics') ?>
                            </th>
                            <th class="wps-pd-l">
                                <?php esc_html_e('Region', 'wp-statistics') ?>
                            </th>
                            <th class="wps-pd-l">
                                <?php esc_html_e('Country', 'wp-statistics') ?>
                            </th>
                            <th class="wps-pd-l">
                                <span class="wps-order">
                                    <?php esc_html_e('Visitors', 'wp-statistics') ?>
                                </span>
                            </th>
                            <th class="wps-pd-l">
                                <?php esc_html_e('Views', 'wp-statistics') ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $item) : ?>
                            <tr>
                                <td class="wps-pd-l">
                                    <span class="truncate" title="<?php echo esc_attr($item->city); ?>"><?php echo esc_html($item->city); ?></span>
                                </td>
                                <td class="wps-pd-l">
                                    <span class="truncate" title="<?php echo esc_attr($item->region); ?>"><?php echo esc_html($item->region); ?></span>
                                </td>
                                <td class="wps-pd-l">
                                    <div class="wps-country-name">
                                        <img class="wps-flag" src="<?php echo esc_url(Country::flag($item->country)); ?>" alt="<?php echo esc_attr(Country::getName($item->country)); ?>">
                                        <span class="truncate" title="<?php echo esc_attr(Country::getName($item->country)); ?>"><?php echo esc_html(Country::getName($item->country)); ?></span>
                                    </div>
                                </td>
                                <td class="wps-pd-l">
                                    <span><?php echo esc_html(number_format_i18n($item->visitors)); ?></span>
                                </td>
                                <td class="wps-pd-l">
                                    <span><?php echo esc_html(number_format_i18n($item->views)); ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else : ?>
            <div class="o-wrap o-wrap--no-data wps-center">
                <?php echo esc_html(Helper::getNoDataMessage()); ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="wps-card__footer">
        <div class="wps-card__footer__more">
            <a class="wps-card__footer__more__link" href="<?php echo esc_url(Menus::admin_url('geographic', ['tab' => 'cities'])); ?>">
                <?php esc_html_e('View Cities', 'wp-statistics') ?>
            </a>
        </div>
    </div>
</div>