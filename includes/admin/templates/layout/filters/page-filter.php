<?php 
$queryKey       = 'pid';
$postType       = isset($_GET['pt']) ? sanitize_text_field($_GET['pt']) : false;
$postId         = isset($_GET[$queryKey]) ? intval($_GET[$queryKey]) : false;
$selectedOption = $postId ? get_the_title($postId) : __('All', 'wp-statistics');
$baseUrl        = remove_query_arg($queryKey);

$pageQuery = ['post_status' => 'publish', 'posts_per_page' => -1];
if ($postType) {
    $pageQuery['post_type'] = $postType;
}
$posts = new WP_Query($pageQuery);
?>

<?php if ($posts->have_posts()) : ?>
    <div class="wps-filter-page wps-head-filters__item loading">
        <div class="wps-dropdown">
            <label class="selectedItemLabel"><?php esc_html_e('Page:', 'wp-statistics'); ?> </label>
            <button type="button" class="dropbtn"><?php echo esc_html($selectedOption); ?></button>
            <div class="dropdown-content">
                <a href="<?php echo esc_url($baseUrl) ?>" data-index="0" class="<?php echo !$postId ? 'selected' : '' ?>"><?php  esc_html_e('All', 'wp-statistics'); ?></a>

                <?php while ($posts->have_posts()) : $posts->the_post(); ?>
                    <?php $url = add_query_arg([$queryKey => get_the_ID()]); ?>

                    <a href="<?php echo esc_url($url) ?>" data-index="<?php echo esc_attr($key + 1) ?>" title="<?php echo esc_attr(get_the_title()) ?>" class="<?php echo get_the_ID() == $postId ? 'selected' : '' ?>">
                        <?php the_title() ?>
                    </a>
                <?php endwhile; ?>
                <?php wp_reset_query(); ?>
            </div>
        </div>
    </div>
<?php endif; ?>