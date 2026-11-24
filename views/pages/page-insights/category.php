<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Utils\Request;
use WP_Statistics\Components\View;

$taxonomy = Request::get('tx', 'category');

if ($showLockedPage) :
    // Custom taxonomy without Data Plus: the standard lock page (Upgrade to Premium / Learn more, new tab).
    View::load("pages/lock-page", [
        'page_title' => __('See traffic for every custom taxonomy', 'wp-statistics'),
        'benefits'   => [
            __('Views and visitors for custom taxonomies', 'wp-statistics'),
            __('Custom post types in every report', 'wp-statistics'),
            __('Link clicks and file downloads', 'wp-statistics'),
        ],
        'addon_name' => esc_html__('Data Plus', 'wp-statistics'),
        'addon_slug' => 'wp-statistics-data-plus',
        'images'     => ['category-pages.jpg', 'data-plus-category.png'],
    ]);
else: ?>
    <div class="postbox-container wps-postbox-full">
        <div class="metabox-holder">
            <div class="meta-box-sortables">
                <div class="postbox">
                    <?php
                        View::load("components/tables/category-pages", [
                            'data'       => $data['categories'][$taxonomy] ?? null,
                            'pagination' => $pagination
                        ]);
                    ?>
                </div>
            </div>
        </div>
    </div>
<?php endif ?>

