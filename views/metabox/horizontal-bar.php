<?php

use WP_Statistics\Components\View;

?>
<?php if (!empty($data) && !empty($data['data'])) : ?>
    <div class="o-wrap">
        <div class="c-chart c-chart--limited-height">
            <canvas id="<?php echo esc_attr($data['tag_id']); ?>" height="0"></canvas>
        </div>
    </div>
<?php else : ?>
    <?php
    View::load("components/objects/no-data", [
        'url'   => $data['url'],
        'title' => __('Data coming soon!', 'wp-statistics')
    ]);
    ?>
<?php endif; ?>


