<?php
use WP_STATISTICS\Helper;
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
                        /** @var stdClass[] */
                        $viewingAuthors = $data['authors']['top_viewing'];
                        $counter        = 1; 

                        if ($viewingAuthors) {
                            foreach ($viewingAuthors as $author) : ?>
                                <a class="wps-author-tabs__item" href="<?php echo esc_url(Menus::admin_url('author-analytics', ['type' => 'single-author', 'author_id' => $author->id])); ?>">
                                    <div class="wps-author-tabs__item--image">
                                        <span># <?php echo esc_html($counter); ?></span>
                                        <img src="<?php echo esc_url(get_avatar_url($author->id)); ?>" alt="<?php echo esc_html($author->name); ?>"/>
                                    </div>
                                    <div class="wps-author-tabs__item--content">
                                        <h3><?php echo esc_html($author->name); ?></h3>
                                        <span><?php echo esc_html(Helper::formatNumberWithUnit($author->total_views)); ?> <?php esc_html_e('page views', 'wp-statistics') ?></span>
                                    </div>
                                </a>
                                <?php $counter++;
                            endforeach; 
                        } else {
                            ?>
                                <div class="o-wrap o-wrap--no-data">
                                    <p><?php esc_html_e('No recent data available.', 'wp-statistics') ?></p>
                                </div>
                            <?php
                        }
                    ?>
                </div>
                <input type="radio" name="tabs" id="author-publishing">
                <label for="author-publishing"><?php esc_html_e('Publishing', 'wp-statistics') ?></label>
                <div class="wps-author-tabs__content">
                    <?php
                        
                        /** @var stdClass[] */
                        $publishingAuthors  = $data['authors']['top_publishing'];
                        $counter            = 1; 

                        if ($publishingAuthors) {
                            foreach ($publishingAuthors as $author) : ?>
                                <a class="wps-author-tabs__item" href="<?php echo Menus::admin_url('author-analytics', ['type' => 'single-author', 'author_id' => $author->id]) ?>">
                                    <div class="wps-author-tabs__item--image">
                                        <span># <?php echo esc_html($counter); ?></span>
                                        <img src="<?php echo esc_url(get_avatar_url($author->id)); ?>" alt="<?php echo esc_html($author->name); ?>"/>
                                    </div>
                                    <div class="wps-author-tabs__item--content">
                                        <h3><?php echo esc_html($author->name); ?></h3>
                                        <span><?php echo esc_html(Helper::formatNumberWithUnit($author->post_count)); ?> <?php esc_html_e('page publish', 'wp-statistics') ?></span>
                                    </div>
                                </a>
                                <?php $counter++;
                            endforeach; 
                        } else {
                            ?>
                                <div class="o-wrap o-wrap--no-data">
                                    <p><?php esc_html_e('No recent data available.', 'wp-statistics') ?></p>
                                </div>
                            <?php
                        }
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
                        /** @var stdClass[] */
                        $topByCommentsPerPost   = $data['authors']['top_by_comments'];
                        $counter                = 1;

                        if ($topByCommentsPerPost) {
                            foreach ($topByCommentsPerPost as $author) : ?>
                                <a class="wps-author-tabs__item" href="<?php echo Menus::admin_url('author-analytics', ['type' => 'single-author', 'author_id' => $author->id]) ?>">
                                    <div class="wps-author-tabs__item--image">
                                        <span># <?php echo esc_html($counter); ?></span>
                                        <img src="<?php echo esc_url(get_avatar_url($author->id)); ?>" alt="<?php echo esc_html($author->name); ?>"/>
                                    </div>
                                    <div class="wps-author-tabs__item--content">
                                        <h3><?php echo esc_html($author->name); ?></h3>
                                        <span><?php echo esc_html(Helper::formatNumberWithUnit($author->average_comments)) . esc_html__(' comments/post', 'wp-statistics'); ?> </span>
                                    </div>
                                </a>
                                <?php $counter++;
                            endforeach; 
                        } else {
                            ?>
                                <div class="o-wrap o-wrap--no-data">
                                    <p><?php esc_html_e('No recent data available.', 'wp-statistics') ?></p>
                                </div>
                            <?php
                        }
                    ?>
                </div>

                <input type="radio" name="side-tabs" id="views-post">
                <label for="views-post"><?php esc_html_e('Views/Post', 'wp-statistics') ?></label>
                <div class="wps-author-tabs__content">
                    <?php
                        /** @var stdClass[] */
                        $topByViewsPerPost  = $data['authors']['top_by_views'];
                        $counter            = 1; 

                        if ($topByViewsPerPost) {
                            foreach ($topByViewsPerPost as $author) : ?>
                                <a class="wps-author-tabs__item" href="<?php echo Menus::admin_url('author-analytics', ['type' => 'single-author', 'author_id' => $author->id]) ?>">
                                    <div class="wps-author-tabs__item--image">
                                        <span># <?php echo esc_html($counter); ?></span>
                                        <img src="<?php echo esc_url(get_avatar_url($author->id)); ?>" alt="<?php echo esc_html($author->name); ?>"/>
                                    </div>
                                    <div class="wps-author-tabs__item--content">
                                        <h3><?php echo esc_html($author->name); ?></h3>
                                        <span><?php echo esc_html(Helper::formatNumberWithUnit($author->average_views)); ?> <?php esc_html_e('views/post', 'wp-statistics') ?></span>
                                    </div>
                                </a>
                                <?php $counter++;
                            endforeach; 
                        } else {
                            ?>
                                <div class="o-wrap o-wrap--no-data">
                                    <p><?php esc_html_e('No recent data available.', 'wp-statistics') ?></p>
                                </div>
                            <?php
                        }

                    ?>
                </div>

                <input type="radio" name="side-tabs" id="words-post">
                <label for="words-post"><?php esc_html_e('Words/Post', 'wp-statistics') ?></label>
                <div class="wps-author-tabs__content">
                    <?php
                        /** @var stdClass[] */
                        $topByWordsPerPost  = $data['authors']['top_by_words'];
                        $counter            = 1; 

                        if ($topByWordsPerPost) {
                            foreach ($topByWordsPerPost as $author) : ?>
                                <a class="wps-author-tabs__item" href="<?php echo Menus::admin_url('author-analytics', ['type' => 'single-author', 'author_id' => $author->id]) ?>">
                                    <div class="wps-author-tabs__item--image">
                                        <span># <?php echo esc_html($counter); ?></span>
                                        <img src="<?php echo esc_url(get_avatar_url($author->id)); ?>" alt="<?php echo esc_html($author->name); ?>"/>
                                    </div>
                                    <div class="wps-author-tabs__item--content">
                                        <h3><?php echo esc_html($author->name); ?></h3>
                                        <span><?php echo esc_html(Helper::formatNumberWithUnit($author->average_words)); ?> <?php esc_html_e('words/post', 'wp-statistics') ?></span>
                                    </div>
                                </a>
                                <?php $counter++;
                            endforeach; 
                        } else {
                            ?>
                                <div class="o-wrap o-wrap--no-data">
                                    <p><?php esc_html_e('No recent data available.', 'wp-statistics') ?></p>
                                </div>
                            <?php
                        }
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