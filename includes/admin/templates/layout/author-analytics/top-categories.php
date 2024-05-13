<div class="wps-card">
    <div class="wps-card__title">
        <h2>
            <?php echo esc_html($title_text) ?>
            <?php if ($tooltip_text): ?>
                <span class="wps-tooltip" title="<?php echo esc_attr($tooltip_text); ?>"><i class="wps-tooltip-icon info"></i></span>
            <?php endif ?>
        </h2>
    </div>
    <div class="wps-top-categories__content">
        <div class="wps-author-tabs">
            <input type="radio" name="categories-tabs" id="top-categories" checked>
            <label for="top-categories"><?php esc_html_e('Category', 'wp-statistics') ?></label>
            <div class="wps-author-tabs__content">
                <?php
                $cats    = ["title" => "13", "title1" => "14", "title2" => "15", "title3" => "16", "title4" => "18"];
                $counter = 1; 
                foreach ($cats as $name => $published) : ?>
                    <a class="wps-author-tabs__item" href="">
                        <div class="wps-author-tabs__item--content">
                            <h3><?php echo esc_html($name); ?></h3>
                            <span><?php echo esc_html($published); ?><?php esc_html_e('Published Content(s)', 'wp-statistics') ?></span>
                        </div>
                    </a>
                    <?php $counter++; ?>
                <?php endforeach; ?>
            </div>
            <input type="radio" name="categories-tabs" id="top-tag">
            <label for="top-tag"><?php esc_html_e('Tag', 'wp-statistics') ?></label>
            <div class="wps-author-tabs__content">
                <?php
                $tags    = ["Tag name" => "20", "Tag name2" => "10", "Tag name3" => "5", "Tag name4" => "2"];
                $counter = 1; 
                foreach ($tags as $name => $published) : ?>
                    <a class="wps-author-tabs__item" href="">
                        <div class="wps-author-tabs__item--content">
                            <h3><?php echo esc_html($name); ?></h3>
                            <span><?php echo esc_html($published); ?><?php esc_html_e('Published Content(s)', 'wp-statistics') ?></span>
                        </div>
                    </a>
                    <?php $counter++; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>