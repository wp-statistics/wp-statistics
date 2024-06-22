<div class="wps-card">
    <div class="wps-card__title">
        <h2>
            <?php echo $title ?>
            <?php if ($tooltip_text): ?>
                <span class="wps-tooltip" title="<?php echo esc_attr($tooltip_text); ?>"><i class="wps-tooltip-icon info"></i></span>
            <?php endif ?>
        </h2>
    </div>
    <div class="wps-flex-container">
        <div class="wps-flex-half">
            <div class="wps-category-tabs">
                <input type="radio" name="views-tab" id="category-views" checked="checked">
                <label for="category-views"><?php esc_html_e('Views', 'wp-statistics') ?></label>
                <div class="wps-category-tabs__content">
                    <?php
                    $views_data = ["Navid " => "15.1K", "Mostafa" => "12.5K", "Rez" => "8.3K"];
                    $views_counter = 1;
                    foreach ($views_data as $title => $views) : ?>
                        <a class="wps-category-tabs__item" href="">
                            <div class="wps-category-tabs__item--image">
                                <?php $user = wp_get_current_user();
                                if ($user) : ?>
                                    <span>#<?php echo esc_html($views_counter); ?></span>
                                    <img src="<?php echo esc_url(get_avatar_url($user->ID)); ?>" alt="<?php echo esc_html($title); ?>"/>
                                <?php endif ?>
                            </div>
                            <div class="wps-category-tabs__item--content">
                                <h3 class="wps-ellipsis-parent"><span class="wps-ellipsis-text"><?php echo esc_html($title); ?></span></h3>
                                <span><?php echo esc_html($views); ?> <?php esc_html_e('pageviews', 'wp-statistics') ?></span>
                            </div>
                        </a>
                        <?php $views_counter++;?>
                    <?php endforeach; ?>
                </div>
             </div>
        </div>
        <div class="wps-flex-half">
            <div class="wps-category-tabs">
                <input type="radio" name="publishing-tabs" id="category-publishing" checked="checked">
                <label for="category-publishing"><?php esc_html_e('Publishing', 'wp-statistics') ?></label>
                <div class="wps-category-tabs__content">
                    <?php
                    $publishing_data = ["user 1 " => "15.1K", "user2" => "12.5K", "user3" => "8.3K"];
                    $publishing_counter = 1;
                    foreach ($publishing_data as $title => $views) : ?>
                        <a class="wps-category-tabs__item" href="">
                            <div class="wps-category-tabs__item--image">
                                <?php $user = wp_get_current_user();
                                if ($user) : ?>
                                    <span>#<?php echo esc_html($publishing_counter); ?></span>
                                    <img src="<?php echo esc_url(get_avatar_url($user->ID)); ?>" alt="<?php echo esc_html($title); ?>"/>
                                <?php endif ?>
                            </div>
                            <div class="wps-category-tabs__item--content">
                                <h3 class="wps-ellipsis-parent"><span class="wps-ellipsis-text"><?php echo esc_html($title); ?></span></h3>
                                <span><?php echo esc_html($views) . ' ' . esc_html__('publish', 'wp-statistics'); ?> </span>
                            </div>
                        </a>
                        <?php $publishing_counter++;?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>