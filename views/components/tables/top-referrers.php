<?php

use WP_Statistics\Components\View;

?>
<div class="inside">
    <div class="o-table-wrapper">
        <table width="100%" class="o-table wps-new-table">
            <thead>
            <tr>
                <th class="wps-pd-l">
                    <?php esc_html_e('Domain Address', 'wp-statistics') ?>
                </th>
                <th class="wps-pd-l">
                    <span class="wps-order">
                        <?php esc_html_e('Referrals', 'wp-statistics') ?>
                    </span>
                </th>
            </tr>
            </thead>
            <tbody>
                 <tr>
                    <td class="wps-pd-l">
                        <?php
                        View::load("components/objects/external-link", [
                            'url'       => 'google.com',
                            'title'     => 'google.com',
                        ]);
                        ?>
                    </td>
                    <td class="wps-pd-l">
                        <a href="">25K</a>
                    </td>
                </tr>
                 <tr>
                     <td class="wps-pd-l">
                         <?php
                         View::load("components/objects/external-link", [
                             'url'       => 'google.com',
                             'title'     => 'google.com',
                         ]);
                         ?>
                     </td>
                     <td class="wps-pd-l">
                         <a href="">25K</a>
                     </td>
                 </tr>
                 <tr>
                     <td class="wps-pd-l">
                         <?php
                         View::load("components/objects/external-link", [
                             'url'       => 'google.com',
                             'title'     => 'google.com',
                         ]);
                         ?>
                     </td>
                     <td class="wps-pd-l">
                         <a href="">25K</a>
                     </td>
                 </tr>
             </tbody>
        </table>
    </div>
    <div class="o-wrap o-wrap--no-data wps-center">
        <?php esc_html_e('No recent data available.', 'wp-statistics') ?>
    </div>
</div>
