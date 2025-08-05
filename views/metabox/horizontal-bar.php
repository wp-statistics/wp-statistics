<?php

use WP_Statistics\Components\View;

?>
<?php if (!empty($data) && !empty($data['data'])) : ?>
    <div class="o-wrap">
        <div class="c-chart c-chart--limited-height">
            <canvas id="<?php echo esc_attr($data['tag_id']); ?>" aria-label="<?php echo esc_attr($data['tag_id']); ?> chart" role="img" height="0"></canvas>
        </div>
    </div>
<?php else : ?>
    <?php
    $title = __('No data found for this date range.', 'wp-statistics');
    if ($isTodayOrFutureDate) {
        $title = __('Data coming soon!', 'wp-statistics');
    }
    View::load("components/objects/no-data", [
        'url'   => $data['url'],
        'title' => $title
    ]);
    ?>
<?php endif; ?>


