<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;
use WP_Statistics\Utils\Request;

$postId           = Request::get('post_id', 0, 'number');
$postAuthor       = get_post_field('post_author', $postId);
$postType         = get_post_type($postId);
$postTypeSingular = Helper::getPostTypeName($postType, true);
$datePublished    = get_the_date(Helper::getDefaultDateFormat(true), $postId);
$dateUpdated      = get_the_modified_date(Helper::getDefaultDateFormat(true), $postId);
?>

<div class="wps-content-analytics-header">
    <div>
        <?php \WP_Statistics\Components\View::load('components/objects/post-thumb', [
            'post_id' => $postId,
            'title'   => get_the_title($postId),
            /* translators: %s: post title */
            'alt'     => sprintf(__('%s thumbnail', 'wp-statistics'), get_the_title($postId)),
            'size'    => 54,
            'class'   => 'wps-content-analytics-header__thumb',
        ]); ?>
    </div>
    <div>
        <div class="wps-content-analytics-header__title">
            <h2 class="wps_title"><?php echo esc_html(get_the_title($postId)); ?></h2>
            <a href="<?php echo esc_url(get_the_permalink($postId)); ?>" target="_blank" title="<?php echo esc_attr(get_the_title($postId)); ?>"></a>
        </div>
        <div class="wps-content-analytics-header__info">
            <?php
            $className = in_array($postType, ['post', 'page'], true) ? $postType : 'custom';

            printf(
                '<a class="wps-content-analytics-header__type wps-content-analytics-header__type--%1$s" href="%3$s">%2$s</a>',
                esc_attr($className),
                esc_html($postTypeSingular),
                esc_url(admin_url('admin.php?page=wps_pages_page&pt=' . urlencode($postType)))
            )
            ?>
            <span class="wps-content-analytics-header__date_published"><?php echo esc_html($datePublished); ?></span>
            <?php if($datePublished !== $dateUpdated): ?>
                <span class="wps-content-analytics-header__date_updated"><span><?php echo esc_html__('Updated on', 'wp-statistics'); ?></span> <?php echo esc_html($dateUpdated); ?></span>
            <?php endif; ?>
            <?php if ((int)$postAuthor > 0) : ?>
            <span class="wps-content-analytics-header__author">
                <span><?php echo esc_html__('Author:', 'wp-statistics') ?></span> <a href="<?php echo esc_url(Menus::admin_url('author-analytics', ['type' => 'single-author', 'author_id' => $postAuthor])) ?>"><?php echo esc_html(get_the_author_meta('display_name', $postAuthor)); ?></a>
            </span>
            <?php endif; ?>
        </div>
        <div class="wps-content-analytics-header__tags">
            <?php
            $taxonomies = get_post_taxonomies($postId);
            $termCount  = 0;

            foreach ($taxonomies as $taxonomy) {
                $terms = get_the_terms($postId, $taxonomy);
                if ($terms) {
                    foreach ($terms as $term) {
                        ++$termCount;

                        $termClass = '';

                        if ($termCount > 8) {
                            $termClass = 'extra-item';
                        }

                        printf(
                            '<a href="%1$s" class="%2$s">%3$s</a>',
                            esc_url(Menus::admin_url('category-analytics', ['type' => 'single', 'term_id' => $term->term_id])),
                            esc_attr($termClass),
                            esc_html($term->name)
                        );
                    }
                }
            }
            ?>

            <?php
            if ($termCount > 8) {
                printf(
                    '<a class="wps-content-analytics-header__tags--more js-toggle-content-tags">%s</a>',
                    esc_html__('Show more', 'wp-statistics')
                );
            }
            ?>
        </div>
    </div>
</div>