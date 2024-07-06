<div class="wps-card">
    <div class="wps-card__title">
        <h2>
            <?php echo esc_html($title) ?>
            <?php if ($tooltip): ?>
                <span class="wps-tooltip" title="<?php echo esc_attr($tooltip); ?>"><i class="wps-tooltip-icon info"></i></span>
            <?php endif ?>
        </h2>
    </div>
    <div class="wps-flex-container">
        <div class="wps-content-tabs wps-content-category">
             <input type="radio" name="content-category" id="content-category" checked="checked">
            <label for="content-category"><?php esc_html_e('Category', 'wp-statistics') ?></label>
            <div class="wps-content-tabs__content">
                <a class="wps-content-tabs__item" href="">
                    <div class="wps-content-tabs__item--content">
                        <h3 class="wps-ellipsis-parent"><span class="wps-ellipsis-text">Data Analytics</span></h3>
                        <span><span class="wps-count">15</span><?php esc_html_e('Published Content(s)', 'wp-statistics') ?></span>
                    </div>
                </a>
                <a class="wps-content-tabs__item" href="">
                    <div class="wps-content-tabs__item--content">
                        <h3 class="wps-ellipsis-parent"><span class="wps-ellipsis-text">Keyword Research</span></h3>
                        <span><span class="wps-count">15</span><?php esc_html_e('Published Content(s)', 'wp-statistics') ?></span>
                    </div>
                </a>
                <a class="wps-content-tabs__item" href="">
                    <div class="wps-content-tabs__item--content">
                        <h3 class="wps-ellipsis-parent"><span class="wps-ellipsis-text">Content Marketing</span></h3>
                        <span><span class="wps-count">15</span><?php esc_html_e('Published Content(s)', 'wp-statistics') ?></span>
                    </div>
                </a>
            </div>
             <input type="radio" name="content-category" id="content-tag">
            <label for="content-tag"><?php esc_html_e('Tag', 'wp-statistics') ?></label>
            <div class="wps-content-tabs__content">
                <div class="o-wrap o-wrap--no-data"><p><?php esc_html_e('No recent data available.', 'wp-statistics') ?></p></div>
            </div>
       </div>
    </div>
</div>