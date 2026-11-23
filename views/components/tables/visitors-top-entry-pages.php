<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Components\View;
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
                    <th class="wps-pd-l wps-col-num">
                        <?php esc_html_e('Unique Entrances', 'wp-statistics') ?>
                    </th>
                    <th class="wps-pd-l wps-col-share">
                        <?php esc_html_e('Bounce Rate', 'wp-statistics') ?>
                    </th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($data as $page) :
                    $pageInfo = Visitor::get_page_by_id($page->page_id);
                    ?>
                    <tr>
                        <td class="wps-pd-l">
                            <span class="wps-title-cell">
                                <?php View::load("components/objects/internal-link", [
                                    'url'   => $pageInfo['report'],
                                    'title' => $pageInfo['title']
                                ]); ?>
                                <?php View::load('components/objects/new-tab-link', ['url' => $pageInfo['link']]); ?>
                            </span>
                        </td>

                        <td class="wps-pd-l wps-col-num">
                            <span><?php echo esc_html(number_format_i18n($page->visitors)); ?></span>
                        </td>

                        <td class="wps-pd-l wps-col-share">
                            <?php View::load('components/objects/percent-bar', ['value' => (float) ($page->bounce_rate ?? 0), 'variant' => 'rate']); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else : ?>
        <?php View::load('components/objects/empty-state'); ?>
    <?php endif; ?>
</div>