<div class="wps-card">
    <div class="wps-card__title">
        <h2>
            <?php echo esc_html($title); ?>
            <?php if ($tooltip) : ?>
                <span class="wps-tooltip" title="<?php echo esc_attr($tooltip); ?>"><i class="wps-tooltip-icon info"></i></span>
            <?php endif ?>
        </h2>
        <?php if ($description) : ?>
            <p><?php echo esc_html($description); ?></p>
        <?php endif ?>
    </div>
    <div class="wps-card__chart-matrix">
        <div class="chart-container">
            <canvas id="overviewPublishChart">
        </div>
        <div class="wps-card__chart-guide">
            <div class="wps-card__chart-guide--items">
                <span><?php esc_html_e('Less', 'wp-statistics') ?></span>
                <ul>
                    <li class="wps-card__chart-guide--item"></li>
                    <li class="wps-card__chart-guide--item"></li>
                    <li class="wps-card__chart-guide--item"></li>
                    <li class="wps-card__chart-guide--item"></li>
                    <li class="wps-card__chart-guide--item"></li>
                </ul>
                <span><?php esc_html_e('More', 'wp-statistics') ?></span>
            </div>
        </div>
    </div>
</div>