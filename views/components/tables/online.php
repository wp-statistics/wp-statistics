<?php

use WP_STATISTICS\Admin_Template;
use WP_Statistics\Components\View;
use WP_Statistics\Decorators\VisitorDecorator;
use WP_STATISTICS\Menus;

?>

<div class="inside">
    <?php if (!empty($data)) : ?>
        <div class="o-table-wrapper">
            <table width="100%" class="o-table wps-new-table">
                <thead>
                    <tr>
                        <th scope="col" class="wps-pd-l">
                            <span class="wps-order"><?php esc_html_e('Last View', 'wp-statistics'); ?></span>
                        </th>
                        <th scope="col" class="wps-pd-l">
                            <span><?php esc_html_e('Online For', 'wp-statistics'); ?></span>
                        </th>
                        <th scope="col" class="wps-pd-l">
                            <?php esc_html_e('Visitor Information', 'wp-statistics'); ?>
                        </th>
                        <th scope="col" class="wps-pd-l">
                            <?php esc_html_e('Referrer', 'wp-statistics'); ?>
                        </th>
                        <th scope="col" class="wps-pd-l">
                            <?php echo esc_html__('Current Page', 'wp-statistics'); ?>
                        </th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($data as $visitor) : ?>
                    <?php /** @var VisitorDecorator $visitor */ ?>
                        <tr>
                            <td class="wps-pd-l"><?php echo esc_html($visitor->getLastView()); ?></td>

                            <td class="wps-pd-l"><?php echo esc_html($visitor->getOnlineTime()); ?></td>

                            <td class="wps-pd-l">
                                <?php
                                    View::load("components/visitor-information", ['visitor' => $visitor]);
                                ?>
                            </td>

                            <td class="wps-pd-l">
                                <?php
                                    View::load("components/objects/referrer-link", [
                                        'label' => $visitor->getReferral()->getSourceChannel(),
                                        'url'   => $visitor->getReferral()->getReferrer() ,
                                        'title' => $visitor->getReferral()->getRawReferrer()
                                    ]);
                                ?>
                            </td>

                            <td class="wps-pd-l">
                                <?php $page = $visitor->getLastPage(); ?>
                                <?php if (!empty($page)) :
                                    View::load("components/objects/internal-link", [
                                        'url'       => $page['report'],
                                        'title'     => $page['title'],
                                    ]);
                                 else : ?>
                                    <?php echo Admin_Template::UnknownColumn() ?>
                                <?php endif; ?>
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
<?php echo isset($pagination) ? $pagination : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
?>