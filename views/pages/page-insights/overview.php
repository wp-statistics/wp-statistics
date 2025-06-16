<?php

use WP_STATISTICS\Menus;
use WP_Statistics\Components\View;

?>
<div class="wps-postbox-two-col">


    <!--    Top Pages-->
    <div class="postbox">
        <?php
        View::load("components/objects/card-header", [
            'title' => __('Top Pages', 'wp-statistics'),
        ]);
        ?>
        <div class="inside">
            <div class="o-table-wrapper">
                <table width="100%" class="o-table wps-new-table wps-table-inspect">
                    <thead>
                    <tr>
                        <th class="wps-pd-l">
                            <?php esc_html_e('Page', 'wp-statistics'); ?>
                        </th>
                        <th class="wps-pd-l">
                            <span class="wps-order"><?php esc_html_e('Visitors', 'wp-statistics'); ?></span>
                        </th>
                        <th class="wps-pd-l"></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td class="wps-pd-l">
                            <a class="wps-table-ellipsis--name" target="_blank" href="">
                                <span title="Data Plus">Data Plus</span>
                            </a>
                        </td>
                        <td class="wps-pd-l">
                            725
                        </td>
                        <td class="wps-pd-l view-more view-more__arrow">
                            <a target="_blank" href="">
                                <?php esc_html_e('View Content', 'wp-statistics'); ?>
                            </a>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="o-wrap o-wrap--no-data wps-center">
                <?php esc_html_e('No recent data available.', 'wp-statistics'); ?>
            </div>
        </div>
        <?php
        View::load("components/objects/card-footer", [
            'href'  => add_query_arg(['tab' => 'top'], Menus::admin_url('pages')),
            'title' => __('View Top Pages', 'wp-statistics'),
        ]); ?>
    </div>


    <!-- Recent Pages-->
    <div class="postbox">
        <?php
        View::load("components/objects/card-header", [
            'title' => __('Recent Pages', 'wp-statistics'),
        ]);
        ?>
        <div class="inside">
            <div class="o-table-wrapper">
                <table width="100%" class="o-table wps-new-table wps-table-inspect">
                    <thead>
                    <tr>
                        <th class="wps-pd-l">
                            <?php esc_html_e('Page', 'wp-statistics'); ?>
                        </th>
                        <th class="wps-pd-l">
                            <span class="wps-order"><?php esc_html_e('Visitors', 'wp-statistics'); ?></span>
                        </th>
                        <th class="wps-pd-l"></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td class="wps-pd-l">
                            <a class="wps-table-ellipsis--name" target="_blank" href="">
                                <span title="Data Plus">Data Plus</span>
                            </a>
                        </td>
                        <td class="wps-pd-l">
                            725
                        </td>
                        <td class="wps-pd-l view-more view-more__arrow">
                            <a target="_blank" href="">
                                <?php esc_html_e('View Content', 'wp-statistics'); ?>
                            </a>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="o-wrap o-wrap--no-data wps-center">
                <?php esc_html_e('No recent data available.', 'wp-statistics'); ?>
            </div>
        </div>
        <?php
        View::load("components/objects/card-footer", [
            'href'  => add_query_arg(['tab' => 'top'], Menus::admin_url('pages')),
            'title' => __('View Recent Pages', 'wp-statistics'),
        ]); ?>
    </div>


    <!--    Top Entry Pages-->
    <div class="postbox">
        <?php
        View::load("components/objects/card-header", [
            'title' => __('Top Entry Pages', 'wp-statistics'),
        ]);
        ?>
        <div class="inside">
            <div class="o-table-wrapper">
                <table width="100%" class="o-table wps-new-table wps-table-inspect">
                    <thead>
                    <tr>
                        <th class="wps-pd-l">
                            <?php esc_html_e('Entry Page', 'wp-statistics'); ?>
                        </th>
                        <th class="wps-pd-l">
                            <span class="wps-order"><?php esc_html_e('Unique Entrances', 'wp-statistics'); ?></span>
                        </th>
                        <th class="wps-pd-l"></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td class="wps-pd-l">
                            <a class="wps-table-ellipsis--name" target="_blank" href="">
                                <span title="Data Plus">Data Plus</span>
                            </a>
                        </td>
                        <td class="wps-pd-l">
                            725
                        </td>
                        <td class="wps-pd-l view-more view-more__arrow">
                            <a target="_blank" href="">
                                <?php esc_html_e('View Page', 'wp-statistics'); ?>
                            </a>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="o-wrap o-wrap--no-data wps-center">
                <?php esc_html_e('No recent data available.', 'wp-statistics'); ?>
            </div>
        </div>
        <?php
        View::load("components/objects/card-footer", [
            'href'  => add_query_arg(['tab' => 'entry-pages'], Menus::admin_url('pages')),
            'title' => __('View Entry Pages', 'wp-statistics'),
        ]); ?>
    </div>


    <!--    Top Exit Pages -->
    <div class="postbox">
        <?php
        View::load("components/objects/card-header", [
            'title' => __('Top Exit Pages ', 'wp-statistics'),
        ]);
        ?>
        <div class="inside">
            <div class="o-table-wrapper">
                <table width="100%" class="o-table wps-new-table wps-table-inspect">
                    <thead>
                    <tr>
                        <th class="wps-pd-l">
                            <?php esc_html_e('Exit page ', 'wp-statistics'); ?>
                        </th>
                        <th class="wps-pd-l">
                            <span class="wps-order"><?php esc_html_e('Unique Exits', 'wp-statistics'); ?></span>
                        </th>
                        <th class="wps-pd-l"></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td class="wps-pd-l">
                            <a class="wps-table-ellipsis--name" target="_blank" href="">
                                <span title="Data Plus">Data Plus</span>
                            </a>
                        </td>
                        <td class="wps-pd-l">
                            725
                        </td>
                        <td class="wps-pd-l view-more view-more__arrow">
                            <a target="_blank" href="">
                                <?php esc_html_e('View Page', 'wp-statistics'); ?>
                            </a>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="o-wrap o-wrap--no-data wps-center">
                <?php esc_html_e('No recent data available.', 'wp-statistics'); ?>
            </div>
        </div>
        <?php
        View::load("components/objects/card-footer", [
            'href'  => add_query_arg(['tab' => 'exit-pages'], Menus::admin_url('pages')),
            'title' => __('View Exit Pages', 'wp-statistics'),
        ]); ?>
    </div>

    <!--    Top 404 Pages  -->
    <div class="postbox">
        <?php
        View::load("components/objects/card-header", [
            'title' => __('Top 404 Pages', 'wp-statistics'),
        ]);
        ?>
        <div class="inside">
            <div class="o-table-wrapper">
                <table width="100%" class="o-table wps-new-table wps-table-inspect">
                    <thead>
                    <tr>
                        <th class="wps-pd-l">
                            <?php esc_html_e('URL ', 'wp-statistics'); ?>
                        </th>
                        <th class="wps-pd-l">
                            <span class="wps-order"><?php esc_html_e('Views', 'wp-statistics'); ?></span>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td class="wps-pd-l">
                            <span class="wps-table-ellipsis--name">
                                <span title="/help-center/">/help-center/</span>
                            </span>
                        </td>
                        <td class="wps-pd-l">
                            725
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="o-wrap o-wrap--no-data wps-center">
                <?php esc_html_e('No recent data available.', 'wp-statistics'); ?>
            </div>
        </div>
        <?php
        View::load("components/objects/card-footer", [
            'href'  => add_query_arg(['tab' => '404'], Menus::admin_url('pages')),
            'title' => __('View 404 Pages', 'wp-statistics'),
        ]); ?>
    </div>


    <!-- Top Author Pages -->
    <div class="postbox">
        <?php
        View::load("components/objects/card-header", [
            'title' => __('Top Author Pages ', 'wp-statistics'),
        ]);
        ?>
        <div class="inside">
            <div class="o-table-wrapper">
                <table width="100%" class="o-table wps-new-table wps-table-inspect">
                    <thead>
                    <tr>
                        <th class="wps-pd-l">
                            <?php esc_html_e('Author ', 'wp-statistics'); ?>
                        </th>
                        <th class="wps-pd-l">
                            <span class="wps-order"><?php esc_html_e('Author\'s Page Views', 'wp-statistics'); ?></span>
                        </th>
                        <th class="wps-pd-l"></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td class="wps-pd-l">
                            <a class="wps-table-ellipsis--name" target="_blank" href="">
                                <span title="Michael">Michael</span>
                            </a>
                        </td>
                        <td class="wps-pd-l">
                            725
                        </td>
                        <td class="wps-pd-l view-more view-more__arrow">
                            <a target="_blank" href="">
                                <?php esc_html_e('View Author Page', 'wp-statistics'); ?>
                            </a>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="o-wrap o-wrap--no-data wps-center">
                <?php esc_html_e('No recent data available.', 'wp-statistics'); ?>
            </div>
        </div>
        <?php
        View::load("components/objects/card-footer", [
            'href'  => add_query_arg(['tab' => 'author'], Menus::admin_url('pages')),
            'title' => __('View Author Pages', 'wp-statistics'),
        ]); ?>
    </div>
</div>