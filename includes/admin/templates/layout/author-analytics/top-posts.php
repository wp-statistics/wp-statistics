<div class="wps-card">
    <div class="wps-card__title">
        <h2>
            <?php echo $title_text ?>
            <?php if ($tooltip_text): ?>
                <span class="wps-tooltip" title="<?php echo esc_attr($tooltip_text); ?>"><i class="wps-tooltip-icon info"></i></span>
            <?php endif ?>
        </h2>
    </div>

    <div class="wps-flex-container">
        <div class="wps-flex-half">
            <div class="wps-author-tabs">
                <input type="radio" name="tabs" id="author-views" checked="checked">
                <label for="author-views"><?php echo esc_html__('Views', 'wp-statistics') ?></label>
                <div class="wps-author-tabs__content">
                    <?php
                    $users = array("Post Name" => "15.1K", "Post Name1" => "12.5K", "Post Name2" => "8.3K", "Post Name3" => "5.6K", "Post Name4" => "4.7K");
                    $counter = 1; // Initialize counter variable
                    foreach ($users as $name => $pageviews) : ?>
                        <a class="wps-author-tabs__item" href="">
                            <div class="wps-author-tabs__item--image">
                                <?php $user = wp_get_current_user();
                                    if ($user) : ?>
                                    <span>#<?php echo $counter; // Output the counter value ?></span>
                                    <img src="<?php echo esc_url(get_avatar_url($user->ID)); ?>" alt="<?php echo $name; ?>"/>
                                <?php endif ?>
                            </div>
                            <div class="wps-author-tabs__item--content">
                                <h3><?php echo esc_html__($name); ?></h3>
                                <span><?php echo $pageviews; ?> <?php echo esc_html__('Views', 'wp-statistics') ?></span>
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
                <label for="comments-post"><?php echo esc_html__('Comments', 'wp-statistics') ?></label>
                <div class="wps-author-tabs__content">
                    <?php
                    $users = array("Post Name" => "15.1Avg", "Post Name1" => "12.5Avg", "Post Name2" => "8.3Avg", "Post Name3" => "5.6Avg", "Post Name4" => "4.7Avg");
                    $counter = 1; // Initialize counter variable
                    foreach ($users as $name => $pageviews) : ?>
                        <a class="wps-author-tabs__item" href="">
                            <div class="wps-author-tabs__item--image">
                                <?php $user = wp_get_current_user();
                                if ($user) : ?>
                                    <span>#<?php echo $counter; // Output the counter value ?></span>
                                    <img src="<?php echo esc_url(get_avatar_url($user->ID)); ?>" alt="<?php echo $name; ?>"/>
                                <?php endif ?>
                            </div>
                            <div class="wps-author-tabs__item--content">
                                <h3><?php echo $name; ?></h3>
                                <span><?php echo $pageviews; ?>  <?php echo esc_html__('Views', 'wp-statistics') ?></span>
                            </div>
                        </a>
                        <?php $counter++;?>
                    <?php endforeach; ?>
                </div>

                <input type="radio" name="side-tabs" id="views-post">
                <label for="views-post"><?php echo esc_html__('Words', 'wp-statistics') ?></label>
                <div class="wps-author-tabs__content">
                    <?php
                    $users = array("Post Name" => "15.1Avg", "Post Name1" => "12.5Avg", "Post Name2" => "8.3Avg", "Post Name3" => "5.6Avg", "Post Name4" => "4.7Avg");
                    $counter = 1; // Initialize counter variable
                    foreach ($users as $name => $pageviews) : ?>
                        <a class="wps-author-tabs__item" href="">
                            <div class="wps-author-tabs__item--image">
                                <?php $user = wp_get_current_user();
                                if ($user) : ?>
                                    <span>#<?php echo $counter; // Output the counter value ?></span>
                                    <img src="<?php echo esc_url(get_avatar_url($user->ID)); ?>" alt="<?php echo $name; ?>"/>
                                <?php endif ?>
                            </div>
                            <div class="wps-author-tabs__item--content">
                                <h3><?php echo $name; ?></h3>
                                <span><?php echo $pageviews; ?>  <?php echo esc_html__('Views', 'wp-statistics') ?></span>
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
            <a href="<?php echo esc_url(admin_url('admin.php?page=wps_author-analytics_page&author_id=1&report=posts')); ?>" class="c-footer__more__link" title="<?php echo esc_html__('See all posts', 'wp-statistics') ?>"><?php echo esc_html__('See all posts', 'wp-statistics') ?></a>
        </div>
    </div>
</div>