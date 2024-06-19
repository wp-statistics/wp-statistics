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
        <div class="wps-flex-half">
            <div class="wps-content-tabs">
                <input type="radio" name="tabs" id="content-most-popular" checked="checked">
                <label for="content-most-popular"><?php esc_html_e('Most Popular', 'wp-statistics') ?></label>
                <div class="wps-content-tabs__content">
                    <?php
                    $popular_data = ["The Evolution of SEO: From Keyword Stuffing to User " => "15.1K", "Demystifying Google's Algorithm Updates: ADemystifying Google's Algorithm Updates: A" => "12.5K", "test data" => "8.3K", "test data2" => "5.6K", "test data3" => "4.7K"];
                    $popular_counter = 1;
                    foreach ($popular_data as $title => $views) : ?>
                        <a class="wps-content-tabs__item" href="">
                            <div class="wps-content-tabs__item--image">
                                <?php $user = wp_get_current_user();
                                if ($user) : ?>
                                    <span>#<?php echo esc_html($popular_counter); ?></span>
                                    <img src="<?php echo esc_url(get_avatar_url($user->ID)); ?>" alt="<?php echo esc_html($title); ?>"/>
                                <?php endif ?>
                            </div>
                            <div class="wps-content-tabs__item--content">
                                <h3 class="wps-ellipsis-parent"><span class="wps-ellipsis-text"><?php echo esc_html($title); ?></span></h3>
                                <span><?php echo esc_html($views); ?> <?php esc_html_e('views', 'wp-statistics') ?></span>
                            </div>
                        </a>
                        <?php $popular_counter++;?>
                    <?php endforeach; ?>
                </div>
                <input type="radio" name="tabs" id="content-most-commented">
                <label for="content-most-commented"><?php esc_html_e('Most Commented', 'wp-statistics') ?></label>
                <div class="wps-content-tabs__content">
                    <?php
                    $commented_data = ["The Evolution of SEO: From Keyword Stuffing to User " => "15.1K", "Demystifying Google's Algorithm Updates: ADemystifying Google's Algorithm Updates: A" => "12.5K", "test data" => "8.3K"];
                    $commented_counter = 1;
                    foreach ($commented_data as $title => $views) : ?>
                        <a class="wps-content-tabs__item" href="">
                            <div class="wps-content-tabs__item--image">
                                <?php $user = wp_get_current_user();
                                if ($user) : ?>
                                    <span>#<?php echo esc_html($commented_counter); ?></span>
                                    <img src="<?php echo esc_url(get_avatar_url($user->ID)); ?>" alt="<?php echo esc_html($title); ?>"/>
                                <?php endif ?>
                            </div>
                            <div class="wps-content-tabs__item--content">
                                <h3 class="wps-ellipsis-parent"><span class="wps-ellipsis-text"><?php echo esc_html($title); ?></span></h3>
                                <span><?php echo esc_html($views); ?> <?php esc_html_e('views', 'wp-statistics') ?></span>
                            </div>
                        </a>
                        <?php $commented_counter++;?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="wps-flex-half">
            <div class="wps-content-tabs">
                <input type="radio" name="side-tabs" id="content-recent" checked="checked">
                <label for="content-recent"><?php esc_html_e('Recent', 'wp-statistics') ?></label>
                <div class="wps-content-tabs__content">
                    <?php
                    $recent_data = ["The Evolution of SEO: From Keyword Stuffing to User " => "15.1K", "Demystifying Google's Algorithm Updates: ADemystifying Google's Algorithm Updates: A" => "12.5K", "test data" => "8.3K"];
                    $recent_counter = 1;
                    foreach ($recent_data as $title => $views) : ?>
                        <a class="wps-content-tabs__item" href="">
                            <div class="wps-content-tabs__item--image">
                                <?php $user = wp_get_current_user();
                                if ($user) : ?>
                                    <span>#<?php echo esc_html($recent_counter); ?></span>
                                    <img src="<?php echo esc_url(get_avatar_url($user->ID)); ?>" alt="<?php echo esc_html($title); ?>"/>
                                <?php endif ?>
                            </div>
                            <div class="wps-content-tabs__item--content">
                                <h3 class="wps-ellipsis-parent"><span class="wps-ellipsis-text"><?php echo esc_html($title); ?></span></h3>
                                <span><?php echo esc_html($views) . ' ' . esc_html__('views', 'wp-statistics'); ?> </span>
                            </div>
                        </a>
                        <?php $recent_counter++;?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="c-footer">
        <div class="c-footer__more">
            <a href="" class="c-footer__more__link" title="<?php esc_html_e('See all', 'wp-statistics'); echo $type ?>s"><?php esc_html_e('See all ', 'wp-statistics') ; echo $type ?>s</a>
        </div>
    </div>
</div>