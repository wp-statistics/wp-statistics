<?php
use WP_Statistics\Utils\Request;

$postId     = Request::get('post_id');
$postAuthor = get_post_field('post_author', $postId);
?>

<div class="wps-content-analytics-header">
    <div>
        <?php if (has_post_thumbnail($postId)) : ?>
            <img src="<?php echo get_the_post_thumbnail_url($postId) ?>">
        <?php endif; ?>
    </div>
    <div>
        <div class="wps-content-analytics-header__title">
            <h2 class="wps_title"><?php echo get_the_title($postId) ?></h2>
            <a href="<?php echo get_the_permalink($postId) ?>" target="_blank" title="<?php echo get_the_title($postId) ?>">
                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4.66667 2V3.33333H1.33333V10.6667H8.66667V7.33333H10V11.3333C10 11.7015 9.70153 12 9.33333 12H0.666667C0.29848 12 0 11.7015 0 11.3333V2.66667C0 2.29848 0.29848 2 0.666667 2H4.66667ZM12 0V5.33333H10.6667L10.6666 2.27533L5.4714 7.4714L4.52859 6.5286L9.72327 1.33333H6.66667V0H12Z" fill="#404BF2"/>
                </svg>
            </a>
        </div>
        <div class="wps-content-analytics-header__info">
            <span class="wps-content-analytics-header__type"><?php echo ucfirst(get_post_type($postId)) ?></span>
            <span class="wps-content-analytics-header__date"><?php echo get_the_date('F j, Y g:i a', $postId) ?></span>
            <span class="wps-content-analytics-header__author">
                <a href="<?php echo get_author_posts_url($postAuthor) ?>"><?php echo get_the_author_meta('display_name', $postAuthor) ?></a>
            </span>
        </div>
        <div class="wps-content-analytics-header__tags">
            <?php
                $taxonomies = get_post_taxonomies($postId);

                foreach ($taxonomies as $taxonomy) {
                    $terms = get_the_terms($postId, $taxonomy);
                    if ($terms) {
                        foreach ($terms as $term) {
                            echo '<a href="' . get_term_link($term) . '">' . $term->name . '</a>';
                        }
                    }
                }
            ?>
        </div>
    </div>
</div>