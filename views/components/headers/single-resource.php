<?php

use WP_Statistics\Utils\Request;

$postId           = Request::get('post_id');
 ?>

<div class="wps-content-analytics-header wps-content-analytics-header__single-resource">
    <div>
        <div class="wps-content-analytics-header__title">
            <h2 class="wps_title"><?php echo esc_html(get_the_title($postId)); ?></h2>
            <a href="<?php echo esc_url(get_the_permalink($postId)); ?>" target="_blank" title="<?php echo esc_attr(get_the_title($postId)); ?>"></a>
        </div>
    </div>
</div>