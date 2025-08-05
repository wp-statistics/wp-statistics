<?php

use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;
use WP_Statistics\Utils\Request;

$postId           = Request::get('post_id');
$postAuthor       = get_post_field('post_author', $postId);
$postType         = get_post_type($postId);
$postTypeSingular = Helper::getPostTypeName($postType, true);
$datePublished    = get_the_date(Helper::getDefaultDateFormat(true), $postId);
$dateUpdated      = get_the_modified_date(Helper::getDefaultDateFormat(true), $postId);
?>

<div class="wps-content-analytics-header">
    <div>
        <?php if (has_post_thumbnail($postId)) : ?>
            <img src="<?php echo esc_url(get_the_post_thumbnail_url($postId)); ?>" alt="<?php echo esc_attr(sprintf(__('%s thumbnail', 'wp-statistics'), get_the_title($postId))); ?>">
        <?php else : ?>
            <svg xmlns="http://www.w3.org/2000/svg" width="140" height="140" viewBox="0 0 140 140" fill="none">
                <g clip-path="url(#clip0_9208_25189)">
                    <path d="M0 0H140V140H0V0Z" fill="#E0E0E0" />
                    <path d="M92 88H48C46.9 88 46 87.1 46 86V54C46 52.9 46.9 52 48 52H92C93.1 52 94 52.9 94 54V86C94 87.1 93.1 88 92 88ZM68.28 73.573L64.865 70.052L55.23 80.999H85.35L74.565 64.644L68.281 73.572L68.28 73.573ZM62.919 64.523C62.9189 64.0251 62.8208 63.5321 62.6302 63.0721C62.4396 62.6121 62.1603 62.1942 61.8081 61.8422C61.456 61.4901 61.038 61.2109 60.578 61.0204C60.118 60.8299 59.6249 60.7319 59.127 60.732C58.6291 60.7321 58.1361 60.8302 57.6761 61.0208C57.2161 61.2114 56.7982 61.4907 56.4462 61.8429C56.0941 62.195 55.8149 62.613 55.6244 63.073C55.4339 63.533 55.3359 64.0261 55.336 64.524C55.336 65.5296 55.7355 66.4939 56.4465 67.205C57.1576 67.916 58.1219 68.3155 59.1275 68.3155C60.1331 68.3155 61.0975 67.916 61.8085 67.205C62.5195 66.4939 62.919 65.5286 62.919 64.523Z" fill="#C2C2C2" />
                </g>
                <defs>
                    <clipPath id="clip0_9208_25189">
                        <rect width="140" height="140" fill="white" />
                    </clipPath>
                </defs>
            </svg>
        <?php endif; ?>
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
            <span class="wps-content-analytics-header__author">
                <span><?php echo esc_html__('Author:', 'wp-statistics') ?></span> <a href="<?php echo Menus::admin_url('author-analytics', ['type' => 'single-author', 'author_id' => $postAuthor]) ?>"><?php echo esc_html(get_the_author_meta('display_name', $postAuthor)); ?></a>
            </span>
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
                            Menus::admin_url('category-analytics', ['type' => 'single', 'term_id' => $term->term_id]),
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