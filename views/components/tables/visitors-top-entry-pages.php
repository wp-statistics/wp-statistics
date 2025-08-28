<?php
use WP_Statistics\Components\View;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Visitor;
?>

<div class="inside">
    <?php if (!empty($data)) : ?>
        <div class="o-table-wrapper">
            <table width="100%" class="o-table wps-new-table wps-table-inspect">
                <thead>
                    <tr>
                        <th class="wps-pd-l">
                            <?php esc_html_e('Entry Page', 'wp-statistics') ?>
                        </th>
                        <th class="wps-pd-l">
                            <?php esc_html_e('Unique Entrances', 'wp-statistics') ?>
                        </th>
                        <th class="wps-pd-l"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $page) :
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
            <?php echo esc_html(Helper::getNoDataMessage()); ?>
        </div>
    <?php endif; ?>
</div>