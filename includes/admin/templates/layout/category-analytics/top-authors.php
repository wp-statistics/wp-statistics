<?php 
use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;
?>

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
            <div class="wps-category-tabs">
                <input type="radio" name="views-tab" id="category-views" checked="checked">
                <label for="category-views"><?php esc_html_e('Views', 'wp-statistics') ?></label>
                <div class="wps-category-tabs__content">
                    <?php
                        /** @var stdClass[] */
                        $viewingAuthors = $data['viewing'];
                        $counter        = 1; 

                        if ($viewingAuthors) {
                            foreach ($viewingAuthors as $author) : ?>
                                <a class="wps-category-tabs__item" href="<?php echo esc_url(Menus::admin_url('author-analytics', ['type' => 'single-author', 'author_id' => $author->id])); ?>">
                                    <div class="wps-category-tabs__item--image">
                                        <span># <?php echo esc_html($counter); ?></span>
                                        <img src="<?php echo esc_url(get_avatar_url($author->id)); ?>" alt="<?php echo esc_html($author->name); ?>"/>
                                    </div>
                                    <div class="wps-category-tabs__item--content">
                                        <h3 class="wps-ellipsis-parent"><span class="wps-ellipsis-text"><?php echo esc_html($author->name); ?></span></h3>
                                        <span><?php echo esc_html(Helper::formatNumberWithUnit($author->total_views)); ?> <?php esc_html_e('content views', 'wp-statistics') ?></span>
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
            <div class="wps-category-tabs">
                <input type="radio" name="publishing-tabs" id="category-publishing" checked="checked">
                <label for="category-publishing"><?php esc_html_e('Publishing', 'wp-statistics') ?></label>
                <div class="wps-category-tabs__content">
                <?php
                        /** @var stdClass[] */
                        $publishingAuthors = $data['publishing'];
                        $counter        = 1; 

                        if ($publishingAuthors) {
                            foreach ($publishingAuthors as $author) : ?>
                                <a class="wps-category-tabs__item" href="<?php echo esc_url(Menus::admin_url('author-analytics', ['type' => 'single-author', 'author_id' => $author->id])); ?>">
                                    <div class="wps-category-tabs__item--image">
                                        <span># <?php echo esc_html($counter); ?></span>
                                        <img src="<?php echo esc_url(get_avatar_url($author->id)); ?>" alt="<?php echo esc_html($author->name); ?>"/>
                                    </div>
                                    <div class="wps-category-tabs__item--content">
                                        <h3 class="wps-ellipsis-parent"><span class="wps-ellipsis-text"><?php echo esc_html($author->name); ?></span></h3>
                                        <span><?php echo esc_html(Helper::formatNumberWithUnit($author->post_count)); ?> <?php esc_html_e('content published', 'wp-statistics'); ?></span>
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
</div>