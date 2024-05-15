<?php 
use WP_STATISTICS\Menus; 
?>

<div class="wps-card">
    <div class="wps-card__title">
        <h2>
            <?php echo esc_html($title); ?>
            <?php if ($tooltip): ?>
                <span class="wps-tooltip" title="<?php echo esc_attr($tooltip); ?>"><i class="wps-tooltip-icon info"></i></span>
            <?php endif ?>
        </h2>
    </div>

    <div class="wps-flex-container">
        <div class="wps-flex-half">
            <div class="wps-author-tabs">
                <input type="radio" name="tabs" id="author-views" checked="checked">
                <label for="author-views"><?php esc_html_e('Views', 'wp-statistics') ?></label>
                <div class="wps-author-tabs__content">
                    <?php
                    $users = ["Post Name" => "15.1K", "Post Name1" => "12.5K", "Post Name2" => "8.3K", "Post Name3" => "5.6K", "Post Name4" => "4.7K"];
                    $counter = 1; 
                    foreach ($users as $name => $pageviews) : ?>
                        <a class="wps-author-tabs__item" href="">
                            <div class="wps-author-tabs__item--image">
                                <?php $user = wp_get_current_user();
                                    if ($user) : ?>
                                    <span>#<?php echo esc_html($counter); ?></span>
                                    <img src="<?php echo esc_url(get_avatar_url($user->ID)); ?>" alt="<?php echo esc_html($name); ?>"/>
                                <?php endif ?>
                            </div>
                            <div class="wps-author-tabs__item--content">
                                <h3><?php echo esc_html($name); ?></h3>
                                <span><?php echo esc_html($pageviews); ?> <?php esc_html_e('Views', 'wp-statistics') ?></span>
                            </div>
                        </a>
                        <?php $counter++;?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="wps-flex-half">
            <div class="wps-author-tabs">
                <input type="radio" name="side-tabs" id="comments-post" checked="checked">
                <label for="comments-post"><?php esc_html_e('Comments', 'wp-statistics') ?></label>
                <div class="wps-author-tabs__content">
                    <?php
                    $users = ["Post Name" => "15.1Avg", "Post Name1" => "12.5Avg", "Post Name2" => "8.3Avg", "Post Name3" => "5.6Avg", "Post Name4" => "4.7Avg"];
                    $counter = 1; 
                    foreach ($users as $name => $pageviews) : ?>
                        <a class="wps-author-tabs__item" href="">
                            <div class="wps-author-tabs__item--image">
                                <?php $user = wp_get_current_user();
                                if ($user) : ?>
                                    <span>#<?php echo esc_html($counter); ?></span>
                                    <img src="<?php echo esc_url(get_avatar_url($user->ID)); ?>" alt="<?php echo esc_html($name); ?>"/>
                                <?php endif ?>
                            </div>
                            <div class="wps-author-tabs__item--content">
                                <h3><?php echo esc_html($name); ?></h3>
                                <span><?php echo esc_html($pageviews); ?>  <?php esc_html_e('Views', 'wp-statistics') ?></span>
                            </div>
                        </a>
                        <?php $counter++;?>
                    <?php endforeach; ?>
                </div>

                <input type="radio" name="side-tabs" id="views-post">
                <label for="views-post"><?php esc_html_e('Words', 'wp-statistics') ?></label>
                <div class="wps-author-tabs__content">
                    <?php
                    $users = ["Post Name" => "15.1Avg", "Post Name1" => "12.5Avg", "Post Name2" => "8.3Avg", "Post Name3" => "5.6Avg", "Post Name4" => "4.7Avg"];
                    $counter = 1; 
                    foreach ($users as $name => $pageviews) : ?>
                        <a class="wps-author-tabs__item" href="">
                            <div class="wps-author-tabs__item--image">
                                <?php $user = wp_get_current_user();
                                if ($user) : ?>
                                    <span>#<?php echo esc_html($counter); ?></span>
                                    <img src="<?php echo esc_url(get_avatar_url($user->ID)); ?>" alt="<?php echo esc_html($name); ?>"/>
                                <?php endif ?>
                            </div>
                            <div class="wps-author-tabs__item--content">
                                <h3><?php echo esc_html($name); ?></h3>
                                <span><?php echo esc_html($pageviews); ?>  <?php esc_html_e('Views', 'wp-statistics') ?></span>
                            </div>
                        </a>
                        <?php $counter++;?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="c-footer">
        <div class="c-footer__more">
            <a href="<?php echo esc_url(Menus::admin_url('author-analytics', ['type' => 'posts'])); ?>" class="c-footer__more__link" title="<?php esc_html_e('See all posts', 'wp-statistics') ?>"><?php esc_html_e('See all posts', 'wp-statistics') ?></a>
        </div>
    </div>
</div>