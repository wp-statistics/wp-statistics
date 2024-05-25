<?php 
use WP_STATISTICS\Menus; 
?>

<div class="wps-card">
    <div class="wps-card__title">
        <h2>
            <?php echo esc_html($title) ?>
            <?php if ($tooltip) : ?>
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
                        $users = ["Navid" => "15.1K", "Mostafa" => "12.5K", "Byimrez" => "8.3K", "James" => "5.6K", "Emily" => "4.7K"];
                        $counter = 1; 
                        foreach ($users as $name => $views) : ?>
                            <a class="wps-author-tabs__item" href="<?php echo esc_url(admin_url('admin.php?page=wps_author-analytics_page&author_id=1')); ?>">
                                <div class="wps-author-tabs__item--image">
                                    <?php $user = wp_get_current_user();
                                        if ($user) : ?>
                                        <span># <?php echo esc_html($counter); ?></span>
                                        <img src="<?php echo esc_url(get_avatar_url($user->ID)); ?>" alt="<?php echo esc_html($name); ?>"/>
                                    <?php endif ?>
                                </div>
                                <div class="wps-author-tabs__item--content">
                                    <h3><?php echo esc_html($name); ?></h3>
                                    <span><?php echo esc_html($views); ?> <?php esc_html_e('page views', 'wp-statistics') ?></span>
                                </div>
                            </a>
                            <?php $counter++;
                        endforeach; 
                    ?>
                </div>
                <input type="radio" name="tabs" id="author-publishing">
                <label for="author-publishing"><?php esc_html_e('Publishing', 'wp-statistics') ?></label>
                <div class="wps-author-tabs__content">
                    <?php
                        $users = ["Navid" => "15.1K", "Mostafa" => "12.5K", "Byimrez" => "8.3K", "James" => "5.6K"];
                        $counter = 1; 
                        foreach ($users as $name => $publishes) : ?>
                            <a class="wps-author-tabs__item" href="">
                                <div class="wps-author-tabs__item--image">
                                    <?php $user = wp_get_current_user();
                                        if ($user) : ?>
                                        <span># <?php echo esc_html($counter); ?></span>
                                        <img src="<?php echo esc_url(get_avatar_url($user->ID)); ?>" alt="<?php echo esc_html($name); ?>"/>
                                    <?php endif ?>
                                </div>
                                <div class="wps-author-tabs__item--content">
                                    <h3><?php echo esc_html($name); ?></h3>
                                    <span><?php echo esc_html($publishes); ?> <?php esc_html_e('page publish', 'wp-statistics') ?></span>
                                </div>
                            </a>
                            <?php $counter++;
                        endforeach; 
                    ?>
                </div>
            </div>
        </div>
        <div class="wps-flex-half">
            <div class="wps-author-tabs">
                <input type="radio" name="side-tabs" id="comments-post" checked="checked">
                <label for="comments-post"><?php esc_html_e('Comments/Post', 'wp-statistics') ?></label>
                <div class="wps-author-tabs__content">
                    <?php
                        $users = ["Navid" => "15.1Avg", "Mostafa" => "12.5Avg", "Byimrez" => "8.3Avg", "James" => "5.6Avg", "Emily" => "4.7Avg"];
                        $counter = 1; 
                        foreach ($users as $name => $avgComments) : ?>
                            <a class="wps-author-tabs__item" href="">
                                <div class="wps-author-tabs__item--image">
                                    <?php $user = wp_get_current_user();
                                    if ($user) : ?>
                                        <span># <?php echo esc_html($counter); ?></span>
                                        <img src="<?php echo esc_url(get_avatar_url($user->ID)); ?>" alt="<?php echo esc_html($name); ?>"/>
                                    <?php endif ?>
                                </div>
                                <div class="wps-author-tabs__item--content">
                                    <h3><?php echo esc_html($name); ?></h3>
                                    <span><?php echo esc_html($avgComments) . ' ' . esc_html__('comments/post', 'wp-statistics'); ?> </span>
                                </div>
                            </a>
                            <?php $counter++;
                        endforeach; 
                    ?>
                </div>

                <input type="radio" name="side-tabs" id="views-post">
                <label for="views-post"><?php esc_html_e('Views/Post', 'wp-statistics') ?></label>
                <div class="wps-author-tabs__content">
                    <?php
                        $users = ["Navid" => "15.1Avg", "Mostafa" => "12.5Avg", "Byimrez" => "8.3Avg", "James" => "5.6Avg"];
                        $counter = 1; 
                        foreach ($users as $name => $avgPosts) : ?>
                            <a class="wps-author-tabs__item" href="">
                                <div class="wps-author-tabs__item--image">
                                    <?php $user = wp_get_current_user();
                                    if ($user) : ?>
                                        <span># <?php echo esc_html($counter); ?></span>
                                        <img src="<?php echo esc_url(get_avatar_url($user->ID)); ?>" alt="<?php echo esc_html($name); ?>"/>
                                    <?php endif ?>
                                </div>
                                <div class="wps-author-tabs__item--content">
                                    <h3><?php echo esc_html($name); ?></h3>
                                    <span><?php echo esc_html($avgPosts); ?> <?php esc_html_e('views/post', 'wp-statistics') ?></span>
                                </div>
                            </a>
                            <?php $counter++;
                        endforeach; 
                    ?>
                </div>

                <input type="radio" name="side-tabs" id="words-post">
                <label for="words-post"><?php esc_html_e('Words/Post', 'wp-statistics') ?></label>
                <div class="wps-author-tabs__content">
                    <?php
                        $users = ["Navid" => "15.1Avg", "Mostafa" => "12.5Avg", "Byimrez" => "8.3Avg"];
                        $counter = 1; 
                        foreach ($users as $name => $avgWords) : ?>
                            <a class="wps-author-tabs__item" href="">
                                <div class="wps-author-tabs__item--image">
                                    <?php $user = wp_get_current_user();
                                    if ($user) : ?>
                                        <span># <?php echo esc_html($counter); ?></span>
                                        <img src="<?php echo esc_url(get_avatar_url($user->ID)); ?>" alt="<?php echo esc_html($name); ?>"/>
                                    <?php endif ?>
                                </div>
                                <div class="wps-author-tabs__item--content">
                                    <h3><?php echo esc_html($name); ?></h3>
                                    <span><?php echo esc_html($avgWords); ?> <?php esc_html_e('words/post', 'wp-statistics') ?></span>
                                </div>
                            </a>
                            <?php $counter++;
                        endforeach; 
                    ?>
                </div>
            </div>
        </div>
    </div>
    <div class="c-footer">
        <div class="c-footer__more">
            <a href="<?php echo esc_url(Menus::admin_url('author-analytics', ['type' => 'authors'])); ?>" class="c-footer__more__link" title="<?php esc_html_e('See all authors', 'wp-statistics') ?>"><?php esc_html_e('See all authors', 'wp-statistics') ?></a>
        </div>
    </div>
</div>