<?php
use WP_STATISTICS\Menus;
use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Request;

$order          = Request::get('order', 'desc');
$postType       = Request::get('pt', 'post');
$postTypeNameSingular  = Helper::getPostTypeName($postType, true);
?>

<div class="postbox-container wps-postbox-full">
    <div class="metabox-holder">
        <div class="meta-box-sortables">
            <div class="postbox">
                <div class="inside">
                    <?php if (!empty($data)) : ?>
                        <div class="o-table-wrapper">
                            <table width="100%" class="o-table wps-authors-table">
                                <thead>
                                    <tr>
                                        <th class="wps-pd-l">
                                            <a href="<?php echo esc_url(Helper::getTableColumnSortUrl('name')) ?>" class="sort <?php echo Request::compare('order_by', 'name') ? esc_attr($order) : ''; ?>"><?php esc_html_e('Author', 'wp-statistics') ?></a>
                                        </th>
                                        <th class="wps-pd-l">
                                            <a href="<?php echo esc_url(Helper::getTableColumnSortUrl('total_views')) ?>" class="sort <?php echo !Request::has('order_by') || Request::compare('order_by', 'total_views') ? esc_attr($order) : ''; ?>">
                                                <?php echo sprintf(esc_html__('%s Views', 'wp-statistics'), $postTypeNameSingular) ?>
                                            </a>
                                        </th>
                                        <th class="wps-pd-l">
                                            <a href="<?php echo esc_url(Helper::getTableColumnSortUrl('total_posts')) ?>" class="sort <?php echo Request::compare('order_by', 'total_posts') ? esc_attr($order) : ''; ?>">
                                                <?php esc_html_e('Published', 'wp-statistics') ?>
                                            </a>
                                        </th>
                                        <th class="wps-pd-l">
                                            <a href="<?php echo esc_url(Helper::getTableColumnSortUrl('total_words')) ?>" class="sort <?php echo Request::compare('order_by', 'total_words') ? esc_attr($order) : ''; ?>">
                                                <?php esc_html_e('Words', 'wp-statistics') ?>
                                            </a>
                                        </th>
                                        <th class="wps-pd-l">
                                            <a href="<?php echo esc_url(Helper::getTableColumnSortUrl('total_comments')) ?>" class="sort <?php echo Request::compare('order_by', 'total_comments') ? esc_attr($order) : ''; ?>">
                                                <?php esc_html_e('Comments', 'wp-statistics') ?>
                                            </a>
                                        </th>
                                        <th class="wps-pd-l">
                                            <a href="<?php echo esc_url(Helper::getTableColumnSortUrl('average_comments')) ?>" class="sort <?php echo Request::compare('order_by', 'average_comments') ? esc_attr($order) : ''; ?>">
                                                <?php echo sprintf(esc_html__('Comments/%s', 'wp-statistics'), $postTypeNameSingular) ?>
                                            </a>
                                        </th>
                                        <th class="wps-pd-l">
                                            <a href="<?php echo esc_url(Helper::getTableColumnSortUrl('average_views')) ?>" class="sort <?php echo Request::compare('order_by', 'average_views') ? esc_attr($order) : ''; ?>">
                                                <?php echo sprintf(esc_html__('Views/%s', 'wp-statistics'), $postTypeNameSingular) ?>
                                            </a>
                                        </th>
                                        <th class="wps-pd-l">
                                            <a href="<?php echo esc_url(Helper::getTableColumnSortUrl('average_words')) ?>" class="sort <?php echo Request::compare('order_by', 'average_words') ? esc_attr($order) : ''; ?>">
                                                <?php echo sprintf(esc_html__('Words/%s', 'wp-statistics'), $postTypeNameSingular) ?>
                                            </a>
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php foreach ($data as $author) : ?>
                                        <?php /** @var stdClass $author */ ?>
                                        <tr>
                                            <td class="wps-pd-l">
                                                <div class="wps-author-name">
                                                    <img src="<?php echo esc_url(get_avatar_url($author->id)); ?>" alt="<?php echo esc_attr($author->name) ?>"/>
                                                    <a href="<?php echo esc_url(Menus::admin_url('author-analytics', ['type' => 'single-author', 'author_id' => $author->id])) ?>">
                                                        <span title="<?php echo esc_attr($author->name) ?>"><?php echo esc_html($author->name) ?></span>
                                                    </a>
                                                </div>
                                            </td>
                                            <td class="wps-pd-l">
                                                <?php echo esc_html(number_format_i18n(intval($author->total_views))); ?>
                                            </td>
                                            <td class="wps-pd-l">
                                                <?php echo esc_html(number_format_i18n(intval($author->total_posts))) ?>
                                            </td>
                                            <td class="wps-pd-l">
                                                <?php echo esc_html(number_format_i18n(intval($author->total_words))) ?>
                                            </td>
                                            <td class="wps-pd-l">
                                                <?php echo esc_html(number_format_i18n(intval($author->total_comments)))?>
                                            </td>
                                            <td class="wps-pd-l">
                                                <?php echo esc_html(number_format_i18n(intval($author->average_comments))) ?>
                                            </td>
                                            <td class="wps-pd-l">
                                                <?php echo esc_html(number_format_i18n(intval($author->average_views))) ?>
                                            </td>
                                            <td class="wps-pd-l">
                                                <?php echo esc_html(number_format_i18n(intval($author->average_words))) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else : ?>
                        <div class="o-wrap o-wrap--no-data wps-center">
                            <?php esc_html_e('No recent data available.', 'wp-statistics')   ?> 
                        </div>
                    <?php endif; ?>
                </div>

                <?php echo isset($pagination) ? $pagination : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>
        </div>
    </div>
</div>