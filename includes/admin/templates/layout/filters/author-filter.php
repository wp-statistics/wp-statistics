<?php 
$queryKey       = 'author_id';
$authorId       = isset($_GET[$queryKey]) ? intval($_GET[$queryKey]) : false;
$authors        = get_users(['has_published_posts' => true]);
$authorInfo     = get_userdata($authorId);
$selectedOption = $authorInfo ? get_userdata($authorId)->display_name : __('All', 'wp-statistics');
$baseUrl        = remove_query_arg([$queryKey, 'pid']); // remove post id and author id from query
?>

<div class="wps-filter-author wps-head-filters__item loading">
    <div class="wps-dropdown">
        <label class="selectedItemLabel"><?php esc_html_e('Authors:', 'wp-statistics'); ?> </label>
        <button type="button" class="dropbtn"><span><?php echo esc_html($selectedOption); ?></span></button>
        <div class="dropdown-content">
            <a href="<?php echo esc_url($baseUrl) ?>" data-index="0" class="<?php echo !$authorId ? 'selected' : '' ?>"><?php  esc_html_e('All', 'wp-statistics'); ?></a>

            <?php foreach ($authors as $key => $author) : ?>
                <?php $url = add_query_arg([$queryKey => $author->ID], $baseUrl); ?>

                <a href="<?php echo esc_url($url) ?>" data-index="<?php echo esc_attr($key + 1) ?>" title="<?php echo esc_attr($author->display_name) ?>" class="<?php echo $authorId == $author->ID ? 'selected' : '' ?>">
                    <?php echo esc_html($author->display_name) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>