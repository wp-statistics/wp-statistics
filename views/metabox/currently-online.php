<?php

use WP_STATISTICS\Admin_Template;
use WP_Statistics\Components\View;
use WP_Statistics\Decorators\VisitorDecorator;
use WP_STATISTICS\Menus;

?>
<?php if (!empty($data['total'])) : ?>
    <span class="wps-currently-online"><?php echo esc_html($data['total']) ?></span>
<?php endif; ?>
<?php if (!empty($data['visitors'])) : ?>
    <div class="o-table-wrapper">
        <table width="100%" class="o-table wps-new-table">
            <thead>
            <tr>
                <th class="wps-pd-l">
                    <span class="wps-order"><?php esc_html_e('Last View', 'wp-statistics'); ?></span>
                </th>
                <th class="wps-pd-l">
                    <?php esc_html_e('Visitor Info', 'wp-statistics'); ?>
                </th>
                <th class="wps-pd-l">
                    <?php esc_html_e('Location', 'wp-statistics'); ?>
                </th>
                <th class="wps-pd-l">
                    <?php esc_html_e('Referrer', 'wp-statistics'); ?>
                </th>
                <th class="wps-pd-l">
                    <?php esc_html_e('Latest Page', 'wp-statistics'); ?>
                </th>
                <th class="wps-pd-l">
                    <?php esc_html_e('Online For', 'wp-statistics'); ?>
                </th>
                <th class="wps-pd-l">
                    <?php esc_html_e('Views', 'wp-statistics'); ?>
                </th>
            </tr>
            </thead>

            <tbody>
            <?php foreach ($data['visitors'] as $visitor) : ?>
                <?php /** @var VisitorDecorator $visitor */ ?>
                <tr>
                    <td class="wps-pd-l"><?php echo esc_html($visitor->getLastView()) ?></td>

                    <td class="wps-pd-l">
                        <?php View::load("components/visitor-information", ['visitor' => $visitor]); ?>
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
                        <?php
                        View::load("components/objects/referrer-link", [
                            'label' => $visitor->getReferral()->getSourceChannel(),
                            'url'   => $visitor->getReferral()->getReferrer(),
                            'title' => $visitor->getReferral()->getRawReferrer()
                        ]);
                        ?>
                    </td>

                    <td class="wps-pd-l">
                        <?php $page = $visitor->getLastPage(); ?>
                        <?php if (!empty($page)) :
                            View::load("components/objects/external-link", [
                                'url'       => $page['link'],
                                'title'     => $page['title'],
                                'tooltip'   => $page['query'] ? "?{$page['query']}" : ''
                            ]);
                        else : ?>
                            <?php echo Admin_Template::UnknownColumn() ?>
                        <?php endif; ?>
                    </td>

                    <td class="wps-pd-l"><?php echo esc_html($visitor->getOnlineTime()) ?></td>

                    <td class="wps-pd-l">
                        <a href="<?php echo esc_url(Menus::admin_url('visitors', ['type' => 'single-visitor', 'visitor_id' => $visitor->getId()])) ?>"><?php echo esc_html($visitor->getHits()) ?></a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else : ?>
    <?php
    View::load("components/objects/no-data", [
        'url'   => WP_STATISTICS_URL . 'assets/images/no-data/vector-4.svg',
        'title' => __('No activity right now.', 'wp-statistics')
    ]);
    ?>
<?php endif; ?>