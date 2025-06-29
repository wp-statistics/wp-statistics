<?php
use WP_STATISTICS\Admin_Template;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Visitor;
use WP_Statistics\Components\View;
?>

<div class="inside">
    <?php if (!empty($pages)) : ?>
        <div class="o-table-wrapper">
            <table width="100%" class="o-table wps-new-table">
                <thead>
                    <tr>
                        <th scope="col" class="wps-pd-l">
                            <?php esc_html_e('Entry Page', 'wp-statistics') ?>
                        </th>
                        <th scope="col" class="wps-pd-l">
                            <span class="wps-order"><?php esc_html_e('Unique Entrances', 'wp-statistics') ?></span>
                        </th>
                        <th scope="col" class="wps-pd-l">
                            <?php esc_html_e('Publish Date', 'wp-statistics') ?>
                        </th>
                        <th scope="col">
                            <span class="screen-reader-text"><?php esc_html_e('View page detail', 'wp-statistics'); ?></span>
                        </th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($pages as $page) :
                        $pageInfo = Visitor::get_page_by_id($page->page_id);
                    ?>
                        <tr>
                            <td class="wps-pd-l">
                                <?php View::load("components/objects/internal-link", [
                                    'url'   => $pageInfo['report'],
                                    'title' => $pageInfo['title']
                                ]); ?>
                            </td>

                            <td class="wps-pd-l">
                                <span><?php echo esc_html(number_format_i18n($page->visitors)); ?></span>
                            </td>

                            <td class="wps-pd-l">
                                <?php if (!empty($page->post_date)) : ?>
                                    <?php echo esc_html(date_i18n(get_option('date_format', 'Y-m-d'), strtotime($page->post_date))) . ' ' . esc_html__('at', 'wp-statistics') . ' ' . esc_html(date_i18n(get_option('time_format', 'g:i a'), strtotime($page->post_date))); ?>
                                <?php else : ?>
                                    <?php echo Admin_Template::UnknownColumn() ?>
                                <?php endif; ?>
                            </td>

                            <td class="wps-pd-l view-more view-more__arrow">
                                <a target="_blank" href="<?php echo esc_url($pageInfo['link']) ?>"><?php esc_html_e('View Page', 'wp-statistics') ?></a>
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