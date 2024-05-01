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
                    $users = array("Navid" => "15.1K", "Mostafa" => "12.5K", "Byimrez" => "8.3K", "James" => "5.6K", "Emily" => "4.7K");
                    $counter = 1; // Initialize counter variable
                    foreach ($users as $name => $pageviews) : ?>
                        <a class="wps-author-tabs__item" href="<?php echo esc_url(admin_url('admin.php?page=wps_author-analytics_page&author_id=1')); ?>">
                            <div class="wps-author-tabs__item--image">
                                <?php $user = wp_get_current_user();
                                    if ($user) : ?>
                                    <span>#<?php echo $counter; // Output the counter value ?></span>
                                    <img src="<?php echo esc_url(get_avatar_url($user->ID)); ?>" alt="<?php echo $name; ?>"/>
                                <?php endif ?>
                            </div>
                            <div class="wps-author-tabs__item--content">
                                <h3><?php echo $name; ?></h3>
                                <span><?php echo $pageviews; ?> <?php echo esc_html__('page views', 'wp-statistics') ?></span>
                            </div>
                        </a>
                        <?php $counter++;?>
                    <?php endforeach; ?>
                </div>
                <input type="radio" name="tabs" id="author-publishing">
                <label for="author-publishing"><?php echo esc_html__('Publishing', 'wp-statistics') ?></label>
                <div class="wps-author-tabs__content">
                    <?php
                    $users = array("Navid" => "15.1K", "Mostafa" => "12.5K", "Byimrez" => "8.3K", "James" => "5.6K");
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
                                <span><?php echo $pageviews; ?> <?php echo esc_html__('page publish', 'wp-statistics') ?></span>
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
                <label for="comments-post"><?php echo esc_html__('Comments/Post', 'wp-statistics') ?></label>
                <div class="wps-author-tabs__content">
                    <?php
                    $users = array("Navid" => "15.1Avg", "Mostafa" => "12.5Avg", "Byimrez" => "8.3Avg", "James" => "5.6Avg", "Emily" => "4.7Avg");
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
                                <span><?php echo $pageviews; ?> comments/post</span>
                            </div>
                        </a>
                        <?php $counter++;?>
                    <?php endforeach; ?>
                </div>

                <input type="radio" name="side-tabs" id="views-post">
                <label for="views-post"><?php echo esc_html__('Views/Post', 'wp-statistics') ?></label>
                <div class="wps-author-tabs__content">
                    <?php
                    $users = array("Navid" => "15.1Avg", "Mostafa" => "12.5Avg", "Byimrez" => "8.3Avg", "James" => "5.6Avg");
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
                                <span><?php echo $pageviews; ?> <?php echo esc_html__('views/post', 'wp-statistics') ?></span>
                            </div>
                        </a>
                        <?php $counter++;?>
                    <?php endforeach; ?>
                </div>

                <input type="radio" name="side-tabs" id="words-post">
                <label for="words-post"><?php echo esc_html__('Words/Post', 'wp-statistics') ?></label>
                <div class="wps-author-tabs__content">
                    <?php
                    $users = array("Navid" => "15.1Avg", "Mostafa" => "12.5Avg", "Byimrez" => "8.3Avg");
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
                                <span><?php echo $pageviews; ?> <?php echo esc_html__('words/post', 'wp-statistics') ?></span>
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
            <a href="<?php echo esc_url(admin_url('admin.php?page=wps_author-analytics_page&report=authors')); ?>" class="c-footer__more__link" title="<?php echo esc_html__('See all authors', 'wp-statistics') ?>"><?php echo esc_html__('See all authors', 'wp-statistics') ?></a>
        </div>
    </div>
</div>