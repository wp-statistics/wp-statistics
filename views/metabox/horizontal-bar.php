<div class="o-wrap">
     <?php if (!empty($data) && !empty($data['data'])) : ?>
        <div class="c-chart c-chart--limited-height">
            <canvas id="<?php echo esc_attr($data['tag_id']); ?>" height="0"></canvas>
        </div>
    <?php else : ?>
    <div class="o-wrap o-wrap--no-data wps-center">
        <?php esc_html_e('No recent data available.', 'wp-statistics') ?>
    </div>
    <?php endif; ?>
</div>

