<?php
use WP_STATISTICS\Menus;
?>

<div class="o-table-wrapper">
    <?php if (!empty($data)) : ?>
        <table width="100%" class="o-table wps-new-table wps-new-table__most-visited">
            <thead>
                <tr>
                    <th class="wps-pd-l"><?php esc_html_e('Page', 'wp-statistics'); ?></th>
                    <th class="wps-pd-l"><span class="wps-order"><?php esc_html_e('Views', 'wp-statistics'); ?></span></th>
                    <th class="wps-pd-l"></th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($data as $item) : ?>
                    <tr>
                        <td class="wps-pd-l">
                            <div class="wps-ellipsis-parent" title="<?php echo esc_attr($item->post_title) ?>"><span class="wps-ellipsis-text"><?php echo esc_html($item->post_title) ?></span></div>
                        </td>

                        <td class="wps-pd-l">
                            <a href="<?php echo Menus::admin_url('content-analytics', ['type' => 'single', 'post_id' => $item->ID, 'from' => $args['date']['from'], 'to' => $args['date']['to']]) ?>" target="_blank"><?php echo esc_html($item->views) ?></a>
                        </td>

                        <td class="wps-pd-l wps-middle-vertical">
                            <a target="_blank" class="wps-view-content" href="<?php the_permalink($item->ID) ?>"><?php esc_html_e('View', 'wp-statistics'); ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else : ?>
        <div class="o-wrap o-wrap--no-data wps-center">
            <?php esc_html_e('No recent data available.', 'wp-statistics') ?>
        </div>
    <?php endif; ?>
</div>