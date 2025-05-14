<?php

use WP_STATISTICS\Admin_Template;
use WP_Statistics\Components\View;
use WP_Statistics\Decorators\VisitorDecorator;
use WP_STATISTICS\Menus;

$linksTarget    = !empty($open_links_in_new_tab) ? '_blank' : '';
$viewTitle      = !empty($single_post) ? esc_html__('Page View', 'wp-statistics') : esc_html__('Last View', 'wp-statistics')
?>

<div class="inside">
    <?php if (!empty($data)) : ?>
        <div class="o-table-wrapper">
            <table width="100%" class="o-table wps-new-table">
                <thead>
                    <tr>
                        <th class="wps-pd-l">
                            <span class="wps-order"><?php echo esc_html($viewTitle); ?></span>
                        </th>

                        <th class="wps-pd-l">
                            <?php esc_html_e('Visitor Information', 'wp-statistics'); ?>
                        </th>

                        <th class="wps-pd-l">
                            <?php esc_html_e('Referrer', 'wp-statistics'); ?>
                        </th>

                        <?php if (empty($hide_entry_page_column)) : ?>
                            <th class="wps-pd-l">
                                <?php echo esc_html__('Entry Page', 'wp-statistics'); ?>
                            </th>
                        <?php endif; ?>

                        <?php if (empty($hide_latest_page_column)) : ?>
                            <th class="wps-pd-l">
                                <?php echo isset($page_column_title) ? esc_html($page_column_title) : esc_html__('Exit Page', 'wp-statistics'); ?>
                            </th>
                        <?php endif; ?>
                        <th class="wps-pd-l">
                            <?php esc_html_e('Total Views', 'wp-statistics'); ?>
                            <span class="wps-tooltip" title="<?php echo esc_html__('Total views for a single day. Privacy rules assign users a new ID daily, so visits on different days are counted separately.', 'wp-statistics') ?>"><i class="wps-tooltip-icon"></i></span>
                        </th>

                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($data as $visitor) : ?>
                        <?php /** @var VisitorDecorator $visitor */ ?>
                        <tr>
                            <td class="wps-pd-l">
                                <?php if (!empty($single_post)) : ?>
                                    <?php echo esc_html($visitor->getPageView()); ?>
                                <?php else : ?>
                                    <?php echo esc_html($visitor->getLastView()); ?>
                                <?php endif; ?>
                            </td>

                            <td class="wps-pd-l">
                                <?php View::load("components/visitor-information", ['visitor' => $visitor]); ?>
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

                            <?php if (empty($hide_entry_page_column)) : ?>
                                <td class="wps-pd-l">
                                    <?php
                                    $page = $visitor->getFirstPage();

                                    if (!empty($page)) :
                                        View::load("components/objects/internal-link", [
                                            'url'       => $page['report'],
                                            'title'     => $page['title'],
                                            'tooltip'   => $page['query'] ? "?{$page['query']}" : ''
                                        ]);
                                    else :
                                        echo Admin_Template::UnknownColumn();
                                    endif;
                                    ?>
                                </td>
                            <?php endif; ?>

                            <?php if (empty($hide_latest_page_column)) : ?>
                                <td class="wps-pd-l">
                                    <?php
                                    $page = $visitor->getLastPage();

                                    if (!empty($page)) :
                                        View::load("components/objects/internal-link", [
                                            'url'       => $page['report'],
                                            'title'     => $page['title'],
                                        ]);
                                    else :
                                        echo Admin_Template::UnknownColumn();
                                    endif;
                                    ?>
                                </td>
                            <?php endif; ?>

                            <td class="wps-pd-l">
                                <a target="<?php echo esc_attr($linksTarget); ?>" href="<?php echo esc_url(Menus::admin_url('visitors', ['type' => 'single-visitor', 'visitor_id' => $visitor->getId()])) ?>"><?php echo esc_html($visitor->getHits()) ?></a>
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