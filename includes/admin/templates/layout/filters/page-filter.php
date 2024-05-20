<?php 
use WP_STATISTICS\Helper;

$queryKey       = 'pid';
$postId         = isset($_GET[$queryKey]) ? intval($_GET[$queryKey]) : '';
$authorId       = isset($_GET['author_id']) ? intval($_GET['author_id']) : '';
$postType       = isset($_GET['pt']) ? sanitize_text_field($_GET['pt']) : array_values(Helper::get_list_post_type());
$selectedOption = $postId ? get_the_title($postId) : __('All', 'wp-statistics');
$baseUrl        = remove_query_arg($queryKey); // remove post id from query

$query = new WP_Query([
    'post_status'    => 'publish', 
    'posts_per_page' => -1,
    'post_type'      => $postType,
    'author'         => $authorId
]);
?>

<div class="wps-filter-page wps-head-filters__item loading">
    <div class="wps-dropdown">
        <label for="wps-page-filter" class="selectedItemLabel"><?php esc_html_e('Page:', 'wp-statistics'); ?></label>
        <select id="wps-page-filter" class="wps-select2" data-type-show="select2">
            <option value="<?php echo esc_url($baseUrl) ?>" <?php selected(!$postId) ?> >
                <?php esc_html_e('All', 'wp-statistics'); ?>
            </option>
            <?php while ($query->have_posts()) : $query->the_post(); ?>
                <?php $url = add_query_arg([$queryKey => get_the_ID()], $baseUrl); ?>
                <option value="<?php echo esc_url($url) ?>" <?php selected($postId, get_the_ID()) ?>>
                    <?php the_title() ?>
                </option>
            <?php endwhile; ?>
            <?php wp_reset_query(); ?>
        </select>
    </div>
</div>

