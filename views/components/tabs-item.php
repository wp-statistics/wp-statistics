<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly ?>
<a class="wps-tabs-item" href="<?php echo esc_url($href) ?>">
    <div class="wps-content-tabs__item--image">
        <span>#<?php echo esc_html($counter); ?></span>
        <?php \WP_Statistics\Components\View::load('components/objects/post-thumb', [
            'post_id' => $post->ID,
            'title'   => $post->post_title,
            'alt'     => $post->post_title,
            'size'    => 39,
        ]); ?>
    </div>
    <div class="wps-content-tabs__item--content">
        <h3 class="wps-ellipsis-parent"><span class="wps-ellipsis-text"><?php echo esc_html($post->post_title); ?></span></h3>
        <span><span class="wps-content-tabs__item--count"><?php echo esc_html($count);?></span><?php echo esc_html($count_title); ?></span>
    </div>
</a>