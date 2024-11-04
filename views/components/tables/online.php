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
                        <th class="wps-pd-l">
                            <span class="wps-order"><?php esc_html_e('Last View', 'wp-statistics'); ?></span>
                        </th>
                        <th class="wps-pd-l">
                            <span><?php esc_html_e('Online For', 'wp-statistics'); ?></span>
                        </th>
                        <th class="wps-pd-l">
                            <?php esc_html_e('Visitor Information', 'wp-statistics'); ?>
                        </th>
                        <th class="wps-pd-l">
                            <?php esc_html_e('Location', 'wp-statistics'); ?>
                        </th>
                        <th class="wps-pd-l">
                            <?php esc_html_e('Referrer', 'wp-statistics'); ?>
                        </th>
                        <th class="wps-pd-l">
                            <?php echo esc_html__('Latest Page', 'wp-statistics'); ?>
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
                                <div class="wps-country-flag wps-ellipsis-parent">
                                    <a href="<?php echo esc_url(Menus::admin_url('geographic', ['type' => 'single-country', 'country' => $visitor->getLocation()->getCountryCode()])) ?>" class="wps-tooltip" title="<?php echo esc_attr($visitor->getLocation()->getCountryName()) ?>">
                                        <img src="<?php echo esc_url($visitor->getLocation()->getCountryFlag()) ?>" alt="<?php echo esc_attr($visitor->getLocation()->getCountryName()) ?>" width="15" height="15">
                                    </a>
                                    <?php $location = Admin_Template::locationColumn($visitor->getLocation()->getCountryCode(), $visitor->getLocation()->getRegion(), $visitor->getLocation()->getCity()); ?>
                                    <span class="wps-ellipsis-text" title="<?php echo esc_attr($location) ?>"><?php echo esc_html($location) ?></span>
                                </div>
                            </td>

                            <td class="wps-pd-l">
                                <?php if ($visitor->getReferral()->getReferrer()) :
                                    View::load("components/objects/external-link", ['url' => $visitor->getReferral()->getReferrer() , 'title' => $visitor->getReferral()->getRawReferrer() ]);
                                else : ?>
                                    <?php echo Admin_Template::UnknownColumn(); ?>
                                <?php endif; ?>
                            </td>

                            <td class="wps-pd-l">
                                <?php $page = $visitor->getLastPage(); ?>
                                <?php if (!empty($page)) :
                                    View::load("components/objects/external-link", ['url' => $page['link'] , 'title' => $page['title'] ]);
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