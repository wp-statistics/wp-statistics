<?php 
$queryKey         = 'pt';
$baseUrl          = remove_query_arg($queryKey);
$postTypes        = array_values(array_diff(get_post_types(['public' => true]), ['attachment']));
$selectedPostType = isset($_GET[$queryKey]) ? sanitize_text_field($_GET[$queryKey]) : false;
?>

<div class="wps-filter-post-type wps-head-filters__item loading">
    <div class="wps-dropdown">
        <label class="selectedItemLabel"><?php esc_html_e('Post Type:', 'wp-statistics'); ?> </label>
        <button type="button" class="dropbtn"><?php echo $selectedPostType ? esc_html(ucfirst($selectedPostType)) : esc_html__('All', 'wp-statistics'); ?></button>
        <div class="dropdown-content">
            <a href="<?php echo esc_url($baseUrl) ?>" data-index="0" class="<?php echo !$selectedPostType ? 'selected' : '' ?>"><?php esc_html_e('All', 'wp-statistics'); ?></a>

            <?php foreach ($postTypes as $key => $postType) : ?>
                <?php $url = add_query_arg([$queryKey => $postType]); ?>

                <a href="<?php echo esc_url($url) ?>" data-index="<?php echo esc_attr($key + 1) ?>" title="<?php echo esc_attr(ucfirst($postType)) ?>" class="<?php echo $selectedPostType == $postType ? 'selected' : '' ?>">
                    <?php echo esc_html(ucfirst($postType)) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>