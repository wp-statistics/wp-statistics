<?php

use WP_Statistics\Utils\Request;
use WP_Statistics\Components\View;

$taxonomy = Request::get('tx', 'category');

if ($showLockedPage) :
    $locked_args = [
        'campaign' => 'pages',
        'src'      => 'assets/images/locked/category-pages.jpg',
    ];
    View::load("components/locked-page", $locked_args);
else: ?>
    <div class="postbox-container wps-postbox-full">
        <div class="metabox-holder">
            <div class="meta-box-sortables">
                <div class="postbox">
                    <div class="inside">
                        <?php if (!empty($data['categories'][$taxonomy])) : ?>
                            <div class="o-table-wrapper">
                                <?php
                                $args = [
                                    'data' => $data['categories'][$taxonomy]
                                ];
                                View::load("components/tables/pages-category", $args);
                                ?>
                            </div>
                        <?php else : ?>
                            <div class="o-wrap o-wrap--no-data wps-center">
                                <?php esc_html_e('No recent data available.', 'wp-statistics') ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php echo isset($pagination) ? $pagination : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </div>
            </div>
        </div>
    </div>
<?php endif ?>

