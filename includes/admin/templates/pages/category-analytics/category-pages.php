<?php
use WP_STATISTICS\Menus;
use WP_Statistics\Utils\Request;
?>

<div class="postbox-container wps-postbox-full">
    <div class="metabox-holder">
        <div class="meta-box-sortables">
            <div class="postbox">
                <div class="inside">
                         <div class="o-table-wrapper">
                            <table width="100%" class="o-table wps-authors-table">
                                <thead>
                                <tr>
                                    <th class="wps-pd-l">
                                        <a href="" class="sort">
                                            <?php esc_html_e('Author', 'wp-statistics') ?>
                                        </a>
                                    </th>
                                    <th class="wps-pd-l">
                                        <a href="" class="sort">
                                            <?php esc_html_e('Author\'s Page Views', 'wp-statistics') ?>
                                        </a>
                                    </th>
                                    <th class="wps-pd-l">
                                        <a href="" class="sort">
                                            <?php esc_html_e('Published Contents', 'wp-statistics') ?>
                                        </a>
                                    </th>
                                    <th></th>
                                </tr>
                                </thead>

                                <tbody>
                                     <tr>
                                        <td class="wps-pd-l">
                                            <div class="wps-author-name">
                                                <img src="" alt=""/>
                                                <a href="">
                                                    <span title="author name">author name</span>
                                                </a>
                                            </div>
                                        </td>
                                        <td class="wps-pd-l">12</td>
                                        <td class="wps-pd-l">13</td>
                                        <td class="view-more">
                                            <a target="_blank" href="" title="<?php esc_html_e('View Author Page', 'wp-statistics') ?>">
                                                <?php esc_html_e('View Author Page', 'wp-statistics') ?>
                                            </a>
                                        </td>
                                    </tr>
                                 </tbody>
                            </table>
                        </div>
                         <div class="o-wrap o-wrap--no-data wps-center">
                            <?php esc_html_e('No recent data available.', 'wp-statistics')   ?>
                        </div>
                 </div>
             </div>
        </div>
    </div>
</div>