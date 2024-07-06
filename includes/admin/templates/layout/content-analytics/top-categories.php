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
            <?php 
                if (!empty($taxonomies)) :
                    $counter = 1; 
                    foreach ($taxonomies as $taxonomy => $terms) : ?>
                        <?php 
                            $taxName = get_taxonomy($taxonomy)->labels->menu_name;
                            if (empty($terms)) continue;
                        ?>
    
                        <input type="radio" name="content-category" id="<?php echo esc_attr('tax-' . $counter) ?>" <?php checked($counter, 1) ?>>
                        <label for="<?php echo esc_attr('tax-' . $counter) ?>"><?php echo esc_html($taxName) ?></label>
    
                        <div class="wps-content-tabs__content">
                            <?php foreach ($terms as $term) : ?>
                                <a class="wps-content-tabs__item" href="<?php echo get_term_link(intval($term['term_id'])) ?>">
                                    <div class="wps-content-tabs__item--content">
                                        <h3 class="wps-ellipsis-parent"><span class="wps-ellipsis-text"><?php echo esc_html($term['term_name']); ?></span></h3>
                                        <span><span class="wps-count"><?php echo esc_html($term['posts_count']); ?></span> <?php esc_html_e('published contents', 'wp-statistics') ?></span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                        <?php $counter++; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="o-wrap o-wrap--no-data"><?php esc_html_e('No recent data available.', 'wp-statistics') ?></div>
                <?php endif;?>

        </div>
    </div>
</div>