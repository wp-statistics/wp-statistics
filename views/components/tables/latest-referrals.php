<?php

use WP_STATISTICS\Admin_Template;
use WP_Statistics\Components\View;
use WP_Statistics\Decorators\VisitorDecorator;
use WP_STATISTICS\Menus;

?>

    <div class="inside">
        <div class="o-table-wrapper">
            <table width="100%" class="o-table wps-new-table">
                <thead>
                <tr>
                    <th class="wps-pd-l">
                        <span class="wps-order"><?php esc_html_e('Last View', 'wp-statistics') ?></span>
                    </th>
                    <th class="wps-pd-l">
                        <span><?php esc_html_e('Referrer', 'wp-statistics') ?></span>
                    </th>
                    <th class="wps-pd-l">
                        <?php esc_html_e('Visitor Information', 'wp-statistics') ?>
                    </th>
                    <th class="wps-pd-l">
                        <?php echo esc_html__('Entry Page', 'wp-statistics'); ?>
                    </th>
                    <th class="wps-pd-l">
                        <?php echo esc_html__('Exit Page', 'wp-statistics'); ?>
                    </th>
                    <th class="wps-pd-l">
                        <?php echo esc_html__('Total Views', 'wp-statistics'); ?>
                    </th>
                </tr>
                </thead>

                <tbody>
                     <tr>
                        <td class="wps-pd-l">
                            <!----><?php //echo esc_html($visitor->getLastView()) ?>
                            August 16, 4:15 pm
                        </td>

                        <td class="wps-pd-l">
<!--                            --><?php
//                            View::load("components/objects/referrer-link", [
//                                'label' => $visitor->getReferral()->getSourceChannel(),
//                                'url'   => $visitor->getReferral()->getReferrer(),
//                                'title' => $visitor->getReferral()->getRawReferrer()
//                            ]);
//                            ?>
                        </td>

                        <td class="wps-pd-l">
<!--                            --><?php
//                            View::load("components/visitor-information", ['visitor' => $visitor]);
//                            ?>
                        </td>

                        <td class="wps-pd-l">
<!--                            --><?php //$page = $visitor->getFirstPage(); ?>
<!---->
<!--                            --><?php //if (!empty($page)) :
//                                View::load("components/objects/external-link", [
//                                    'url'     => $page['link'],
//                                    'title'   => $page['title'],
//                                    'tooltip' => ''
//                                ]);
//                            else : ?>
<!--                                --><?php //echo Admin_Template::UnknownColumn() ?>
<!--                            --><?php //endif; ?>
                        </td>

                         <td class="wps-pd-l">
<!--                             --><?php //$page = $visitor->getFirstPage(); ?>
<!---->
<!--                             --><?php //if (!empty($page)) :
//                                 View::load("components/objects/external-link", [
//                                     'url'     => $page['link'],
//                                     'title'   => $page['title'],
//                                  ]);
//                             else : ?>
<!--                                 --><?php //echo Admin_Template::UnknownColumn() ?>
<!--                             --><?php //endif; ?>
                         </td>

                        <td class="wps-pd-l">
<!--                            <a href="--><?php //echo esc_url(Menus::admin_url('visitors', ['type' => 'single-visitor', 'visitor_id' => $visitor->getId()])) ?><!--">-->
<!--                                --><?php //echo esc_html($visitor->getHits()) ?>
<!--                            </a>-->
                        </td>
                    </tr>
                 </tbody>
            </table>
        </div>
        <div class="o-wrap o-wrap--no-data wps-center">
            <?php esc_html_e('No recent data available.', 'wp-statistics') ?>
        </div>
    </div>