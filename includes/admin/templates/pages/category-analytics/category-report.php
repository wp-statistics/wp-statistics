<?php 
use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;
?>

<div class="postbox-container wps-postbox-full">
    <div class="metabox-holder">
        <div class="meta-box-sortables">
            <div class="postbox">
                <div class="inside">
                    <?php if (!empty($data)) : ?>
                        <div class="o-table-wrapper">
                            <table width="100%" class="o-table wps-new-table">
                                <thead>
                                    <tr>
                                        <th class="wps-pd-l">
                                            <a href="" class="sort">
                                                <?php esc_html_e('Term', 'wp-statistics'); ?>
                                            </a>
                                        </th>
                                        <th class="wps-pd-l">
                                            <a href="" class="sort desc">
                                                <?php esc_html_e('Views', 'wp-statistics'); ?>
                                            </a>
                                        </th>
                                        <th class="wps-pd-l">
                                            <a href="" class="sort">
                                                <?php esc_html_e('Published ', 'wp-statistics'); ?>
                                            </a>
                                        </th>
                                        <th class="wps-pd-l">
                                            <a href="" class="sort">
                                                <?php esc_html_e('Words ', 'wp-statistics'); ?>
                                            </a>
                                        </th>
                                        <th class="wps-pd-l">
                                            <a href="" class="sort">
                                                <?php esc_html_e('Views/Content ', 'wp-statistics'); ?>
                                            </a>
                                        </th>
                                        <th class="wps-pd-l">
                                            <a href="" class="sort">
                                                <?php esc_html_e('Words/Content ', 'wp-statistics'); ?>
                                            </a>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['terms'] as $term) : ?>
                                        <tr>
                                            <td class="wps-pd-l">
                                                <span class="wps-ellipsis-parent" title="<?php echo esc_attr($term->term_name) ?>">
                                                    <a href="<?php echo esc_url(Menus::admin_url('category-analytics', ['type' => 'single', 'term_id' => $term->term_id])) ?>"><span class="wps-ellipsis-text"><?php echo esc_html($term->term_name) ?></span></a>
                                                </span>
                                            </td>
                                            <td class="wps-pd-l"><?php echo esc_html(number_format_i18n($term->views)) ?></td>
                                            <td class="wps-pd-l"><?php echo esc_html(number_format_i18n($term->posts)) ?></td>
                                            <td class="wps-pd-l"><?php echo esc_html(number_format_i18n($term->words)) ?></td>
                                            <td class="wps-pd-l"><?php echo esc_html(number_format_i18n(Helper::divideNumbers($term->views, $term->posts, 0))) ?></td>
                                            <td class="wps-pd-l"><?php echo esc_html(number_format_i18n(Helper::divideNumbers($term->words, $term->posts, 0))) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else : ?>
                        <div class="o-wrap o-wrap--no-data wps-center">
                            <?php esc_html_e('No recent data available.', 'wp-statistics'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>