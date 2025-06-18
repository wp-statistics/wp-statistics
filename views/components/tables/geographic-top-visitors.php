<?php

use WP_Statistics\Components\View;

?>

<div class="wps-card">
    <div class="wps-card__title">
        <h2>
            <?php echo esc_html($title); ?>
        </h2>
    </div>
    <div class="inside">
        <div class="o-table-wrapper">
            <table width="100%" class="o-table wps-new-table">
                <thead>
                <tr>
                    <th class="wps-pd-l">
                        <?php echo esc_html($top_title); ?>
                    </th>
                    <th class="wps-pd-l">
                        <span class="wps-order">
                            <?php esc_html_e('Visitors', 'wp-statistics') ?>
                        </span>
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td class="wps-pd-l">
                        <?php
                        View::load("components/objects/internal-link", [
                            'url'     => '',
                            'title'   => 'Paris',
                        ]);

                        ?>
                     </td>
                    <td class="wps-pd-l">
                        <a href="">45,452</a>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="o-wrap o-wrap--no-data wps-center">
            <?php esc_html_e('No recent data available.', 'wp-statistics') ?>
        </div>
    </div>
    <?php if (isset($footer_link)) : ?>
        <div class="wps-card__footer">
            <div class="wps-card__footer__more">
                <a class="wps-card__footer__more__link" href="<?php echo esc_url($footer_link); ?>">
                    <?php echo esc_attr($footer_title); ?>
                </a>
            </div>
        </div>
    <?php endif ?>
</div>