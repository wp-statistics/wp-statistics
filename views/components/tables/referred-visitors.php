<?php

use WP_STATISTICS\Admin_Template;
use WP_Statistics\Components\View;
use WP_Statistics\Decorators\VisitorDecorator;
use WP_STATISTICS\Menus;

?>

<div class="inside">
    <?php if (!empty($visitors)) : ?>
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
                            <?php esc_html_e('Source Category', 'wp-statistics') ?>
                        </th>
                        <th class="wps-pd-l">
                            <?php esc_html_e('Visitor Information', 'wp-statistics') ?>
                        </th>
                        <th class="wps-pd-l">
                            <?php esc_html_e('Location', 'wp-statistics') ?>
                        </th>
                        <th class="wps-pd-l">
                            <?php echo esc_html__('Landing Page', 'wp-statistics'); ?>
                        </th>
                        <th class="wps-pd-l">
                            <?php echo esc_html__('Number of Views', 'wp-statistics'); ?>
                        </th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($visitors as $visitor) : ?>
                        <?php /** @var VisitorDecorator $visitor */ ?>
                        <tr>
                            <td class="wps-pd-l">
                                <?php echo esc_html($visitor->getLastView()) ?>
                            </td>

                            <td class="wps-pd-l">
                                <a href="<?php echo esc_url($visitor->getReferral()->getReferrer()) ?>" title="<?php echo esc_attr($visitor->getReferral()->getReferrer()) ?>" target="_blank" class="wps-link-arrow">
                                    <span><?php echo esc_html($visitor->getReferral()->getRawReferrer()) ?></span>
                                </a>
                            </td>

                            <td class="wps-pd-l">
                                <div class="wps-ellipsis-parent">
                                    <?php
                                        echo $visitor->getReferral()->getSourceChannel() ? esc_html($visitor->getReferral()->getSourceChannel()) : Admin_Template::UnknownColumn();
                                    ?>
                                </div>
                            </td>

                            <td class="wps-pd-l">
                                <?php
                                    View::load("components/visitor-information", ['visitor' => $visitor]);
                                ?>
                             </td>

                            <td class="wps-pd-l">
                                <div class="wps-country-flag wps-ellipsis-parent">
                                    <div class="wps-country-flag wps-ellipsis-parent">
                                        <a href="<?php echo esc_url(Menus::admin_url('geographic', ['type' => 'single-country', 'country' => $visitor->getLocation()->getCountryCode()])) ?>" class="wps-tooltip" title="<?php echo esc_attr($visitor->getLocation()->getCountryName()) ?>">
                                            <img src="<?php echo esc_url($visitor->getLocation()->getCountryFlag()) ?>" alt="<?php echo esc_attr($visitor->getLocation()->getCountryName()) ?>" width="15" height="15">
                                        </a>
                                        <?php $location = Admin_Template::locationColumn($visitor->getLocation()->getCountryCode(), $visitor->getLocation()->getRegion(), $visitor->getLocation()->getCity()); ?>
                                        <span class="wps-ellipsis-text" title="<?php echo esc_attr($location) ?>"><?php echo esc_html($location) ?></span>
                                    </div>
                                </div>
                            </td>

                            <td class="wps-pd-l">
                                <?php $page = $visitor->getFirstPage(); ?>

                                <?php if (!empty($page)) : ?>
                                    <a target="_blank" href="<?php echo esc_url($page['link']) ?>" title="<?php echo esc_attr($page['title']) ?>" class="wps-link-arrow">
                                        <span><?php echo esc_html($page['title']) ?></span>
                                    </a>
                                <?php else : ?>
                                    <?php echo Admin_Template::UnknownColumn() ?>
                                <?php endif; ?>
                            </td>

                            <td class="wps-pd-l">
                                <a href="<?php echo esc_url(Menus::admin_url('visitors', ['type' => 'single-visitor', 'visitor_id' => $visitor->getId()])) ?>">
                                    <?php echo esc_html($visitor->getHits()) ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    <?php else : ?>
        <div class="o-wrap o-wrap--no-data wps-center">
            <?php esc_html_e('No recent data available.', 'wp-statistics') ?>
        </div>
    <?php endif; ?>
</div>

<?php
    echo $pagination ?? ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
?>