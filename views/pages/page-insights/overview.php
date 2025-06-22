<?php
use WP_STATISTICS\Menus;
use WP_Statistics\Components\View;
use WP_STATISTICS\Helper;
?>

<div class="wps-postbox-two-col">
    <!-- Top Pages-->
    <div class="postbox">
        <?php
            View::load("components/objects/card-header", [
                'title' => esc_html__('Top Pages', 'wp-statistics'),
            ]);
        ?>
        <div class="inside">
            <?php if (!empty($data['top'])) : ?>
                <div class="o-table-wrapper">
                    <table width="100%" class="o-table wps-new-table wps-table-inspect">
                        <thead>
                            <tr>
                                <th class="wps-pd-l">
                                    <?php esc_html_e('Page', 'wp-statistics'); ?>
                                </th>
                                <th class="wps-pd-l">
                                    <span class="wps-order"><?php esc_html_e('Views', 'wp-statistics'); ?></span>
                                </th>
                                <th class="wps-pd-l"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['top'] as $item) : ?>
                                <tr>
                                    <td class="wps-pd-l">
                                        <a class="wps-table-ellipsis--name" href="<?php echo esc_url(Menus::admin_url('content-analytics', ['type' => 'single', 'post_id' => $item->post_id])) ?>">
                                            <span title="<?php  echo esc_attr($item->title); ?>"><?php echo esc_html($item->title); ?></span>
                                        </a>
                                    </td>
                                    <td class="wps-pd-l">
                                        <?php echo esc_html(number_format_i18n($item->views)) ?>
                                    </td>
                                    <td class="wps-pd-l view-more view-more__arrow">
                                        <a target="_blank" href="<?php the_permalink($item->post_id) ?>">
                                            <?php esc_html_e('View Content', 'wp-statistics'); ?>
                                        </a>
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
        <?php
            View::load("components/objects/card-footer", [
                'href'  => Menus::admin_url('pages', ['tab' => 'top']),
                'title' => esc_html__('View Top Pages', 'wp-statistics'),
            ]);
        ?>
    </div>


    <!-- Recent Pages-->
    <div class="postbox">
        <?php
            View::load("components/objects/card-header", [
                'title' => esc_html__('Recent Pages', 'wp-statistics'),
            ]);
        ?>
        <div class="inside">
            <?php if ($data['recent']) : ?>
                <div class="o-table-wrapper">
                    <table width="100%" class="o-table wps-new-table wps-table-inspect">
                        <thead>
                            <tr>
                                <th class="wps-pd-l">
                                    <?php esc_html_e('Page', 'wp-statistics'); ?>
                                </th>
                                <th class="wps-pd-l">
                                    <span class="wps-order"><?php esc_html_e('Views', 'wp-statistics'); ?></span>
                                </th>
                                <th class="wps-pd-l"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['recent'] as $item) : ?>
                                <tr>
                                    <td class="wps-pd-l">
                                        <a class="wps-table-ellipsis--name" href="<?php echo esc_url(Menus::admin_url('content-analytics', ['type' => 'single', 'post_id' => $item->post_id])) ?>">
                                            <span title="<?php  echo esc_attr($item->title); ?>"><?php echo esc_html($item->title); ?></span>
                                        </a>
                                    </td>
                                    <td class="wps-pd-l">
                                        <?php echo esc_html(number_format_i18n($item->views)) ?>
                                    </td>
                                    <td class="wps-pd-l view-more view-more__arrow">
                                        <a target="_blank" href="<?php the_permalink($item->post_id) ?>">
                                            <?php esc_html_e('View Content', 'wp-statistics'); ?>
                                        </a>
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
        <?php
            View::load("components/objects/card-footer", [
                'href'  => add_query_arg(['tab' => 'top', 'order_by' => 'date', 'order' => 'desc'], Menus::admin_url('pages')),
                'title' => esc_html__('View Recent Pages', 'wp-statistics'),
            ]);
        ?>
    </div>

    <!-- Top Entry Pages-->
    <?php do_action('wp_statistics_pages_overview_entry_pages_widget'); ?>

    <!-- Top Exit Pages -->
    <?php do_action('wp_statistics_pages_overview_exit_pages_widget'); ?>

    <!--  Top 404 Pages  -->
    <div class="postbox">
        <?php
            View::load("components/objects/card-header", [
                'title' => esc_html__('Top 404 Pages', 'wp-statistics'),
            ]);
        ?>
        <div class="inside">
            <?php if (!empty($data['404'])) : ?>
                <div class="o-table-wrapper">
                    <table width="100%" class="o-table wps-new-table wps-table-inspect">
                        <thead>
                            <tr>
                                <th class="wps-pd-l">
                                    <?php esc_html_e('URL ', 'wp-statistics'); ?>
                                </th>
                                <th class="wps-pd-l">
                                    <span class="wps-order"><?php esc_html_e('Views', 'wp-statistics'); ?></span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['404'] as $item) : ?>
                                <tr>
                                    <td class="wps-pd-l">
                                        <span class="wps-table-ellipsis--name">
                                            <span title="<?php echo esc_html($item->uri) ?>"><?php echo esc_html($item->uri) ?></span>
                                        </span>
                                    </td>

                                    <td class="wps-pd-l"><?php echo esc_html($item->views) ?></td>
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
        <?php
            View::load("components/objects/card-footer", [
                'href'  => add_query_arg(['tab' => '404'], Menus::admin_url('pages')),
                'title' => esc_html__('View 404 Pages', 'wp-statistics'),
            ]);
        ?>
    </div>


    <!-- Top Author Pages -->
    <div class="postbox">
        <?php
            View::load("components/objects/card-header", [
                'title' => esc_html__('Top Author Pages ', 'wp-statistics'),
            ]);
        ?>
        <div class="inside">
            <?php if (!empty($data['author'])) : ?>
                <div class="o-table-wrapper">
                    <table width="100%" class="o-table wps-new-table wps-table-inspect">
                        <thead>
                            <tr>
                                <th class="wps-pd-l">
                                    <?php esc_html_e('Author ', 'wp-statistics'); ?>
                                </th>
                                <th class="wps-pd-l">
                                    <span class="wps-order"><?php esc_html_e('Author\'s Page Views', 'wp-statistics'); ?></span>
                                </th>
                                <th class="wps-pd-l"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['author'] as $author) : ?>
                                <tr>
                                    <td class="wps-pd-l">
                                        <a class="wps-table-ellipsis--name" href="<?php echo esc_url(Menus::admin_url('author-analytics', ['type' => 'single-author', 'author_id' => $author->id])) ?>">
                                            <span title="<?php echo esc_attr($author->name) ?>"><?php echo esc_html($author->name) ?></span>
                                        </a>
                                    </td>
                                    <td class="wps-pd-l"><?php echo esc_html(number_format_i18n($author->page_views)); ?></td>
                                    <td class="wps-pd-l view-more view-more__arrow">
                                        <a target="_blank" href="<?php echo esc_url(get_author_posts_url($author->id)); ?>" title="<?php esc_html_e('View Author Page', 'wp-statistics') ?>">
                                            <?php esc_html_e('View Author Page', 'wp-statistics') ?>
                                        </a>
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
        <?php
            View::load("components/objects/card-footer", [
                'href'  => add_query_arg(['tab' => 'author'], Menus::admin_url('pages')),
                'title' => esc_html__('View Author Pages', 'wp-statistics'),
            ]);
        ?>
    </div>
</div>