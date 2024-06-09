<?php
use WP_STATISTICS\Helper;

$queryKey         = 'pt';
$selectedOption   = isset($_GET[$queryKey]) ? sanitize_text_field($_GET[$queryKey]) : 'post';
$postTypes        = array_values(Helper::get_list_post_type());
$baseUrl          = remove_query_arg([$queryKey, 'pid']); // remove post type and post id from query
?>

<div class="wps-filter-post-type wps-head-filters__item loading">
    <div class="wps-dropdown">
        <label class="selectedItemLabel"><?php esc_html_e('Post Type:', 'wp-statistics'); ?> </label>
        <button type="button" class="dropbtn"><span><?php echo $selectedOption ? esc_html(Helper::getPostTypeName($selectedOption)) : esc_html__('All', 'wp-statistics'); ?></span></button>
        <div class="dropdown-content">
            <?php foreach ($postTypes as $key => $postType) : ?>
                <?php 
                    $url    = add_query_arg([$queryKey => $postType], $baseUrl); 
                    $name   = Helper::getPostTypeName($postType);
                ?>

                <a href="<?php echo esc_url($url) ?>" data-index="<?php echo esc_attr($key) ?>" title="<?php echo esc_attr($name) ?>" class="<?php echo $selectedOption == $postType ? 'selected' : '' ?>">
                    <?php echo esc_html($name) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>