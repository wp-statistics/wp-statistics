<?php 
use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;
use WP_Statistics\Utils\Request;

$postType = Request::get('tab', 'post');
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
            <div class="wps-content-tabs">
                <input type="radio" name="tabs" id="content-most-popular" checked="checked">
                <label for="content-most-popular"><?php esc_html_e('Most Popular', 'wp-statistics') ?></label>
                <div class="wps-content-tabs__content">
                    <?php
                        if (!empty($data['top_viewing'])) {
                            $counter = 1;
                            
                            foreach ($data['top_viewing'] as $post) : ?>
                                <a class="wps-content-tabs__item" href="<?php echo esc_url(add_query_arg(['type' => 'single', 'post_id' => $post->ID])) ?>">
                                    <div class="wps-content-tabs__item--image">
                                        <span>#<?php echo esc_html($counter); ?></span>
                                        <?php if (has_post_thumbnail($post->ID)) : ?>
                                            <img src="<?php echo esc_url(get_the_post_thumbnail_url($post->ID)) ?>" alt="<?php echo esc_attr($post->post_title) ?>">
                                        <?php else : ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="140" height="140" viewBox="0 0 140 140" fill="none"><g clip-path="url(#clip0_9208_25189)"><path d="M0 0H140V140H0V0Z" fill="#E0E0E0"/><path d="M92 88H48C46.9 88 46 87.1 46 86V54C46 52.9 46.9 52 48 52H92C93.1 52 94 52.9 94 54V86C94 87.1 93.1 88 92 88ZM68.28 73.573L64.865 70.052L55.23 80.999H85.35L74.565 64.644L68.281 73.572L68.28 73.573ZM62.919 64.523C62.9189 64.0251 62.8208 63.5321 62.6302 63.0721C62.4396 62.6121 62.1603 62.1942 61.8081 61.8422C61.456 61.4901 61.038 61.2109 60.578 61.0204C60.118 60.8299 59.6249 60.7319 59.127 60.732C58.6291 60.7321 58.1361 60.8302 57.6761 61.0208C57.2161 61.2114 56.7982 61.4907 56.4462 61.8429C56.0941 62.195 55.8149 62.613 55.6244 63.073C55.4339 63.533 55.3359 64.0261 55.336 64.524C55.336 65.5296 55.7355 66.4939 56.4465 67.205C57.1576 67.916 58.1219 68.3155 59.1275 68.3155C60.1331 68.3155 61.0975 67.916 61.8085 67.205C62.5195 66.4939 62.919 65.5286 62.919 64.523Z" fill="#C2C2C2"/></g><defs><clipPath id="clip0_9208_25189"><rect width="140" height="140" fill="white"/></clipPath></defs></svg>
                                        <?php endif; ?>
                                    </div>
                                    <div class="wps-content-tabs__item--content">
                                        <h3 class="wps-ellipsis-parent"><span class="wps-ellipsis-text"><?php echo esc_html($post->post_title); ?></span></h3>
                                        <span><?php echo esc_html(Helper::formatNumberWithUnit($post->views)); ?> <?php esc_html_e('views', 'wp-statistics') ?></span>
                                    </div>
                                </a>
                                <?php $counter++;
                            endforeach;
                        } else {
                            ?><div class="o-wrap o-wrap--no-data"><p><?php esc_html_e('No recent data available.', 'wp-statistics') ?></p></div><?php
                        } 
                    ?>
                </div>
                

                <?php if (post_type_supports($postType, 'comments')) : ?>
                    <input type="radio" name="tabs" id="content-most-commented">
                    <label for="content-most-commented"><?php esc_html_e('Most Commented', 'wp-statistics') ?></label>
                    <div class="wps-content-tabs__content">
                        <?php 
                            if (!empty($data['top_commented'])) {
                                $counter = 1;
                                
                                foreach ($data['top_commented'] as $post) : ?>
                                    <a class="wps-content-tabs__item" href="<?php echo esc_url(add_query_arg(['type' => 'single', 'post_id' => $post->ID])) ?>">
                                        <div class="wps-content-tabs__item--image">
                                            <span>#<?php echo esc_html($counter); ?></span>
                                            <?php if (has_post_thumbnail($post->ID)) : ?>
                                                <img src="<?php echo esc_url(get_the_post_thumbnail_url($post->ID)) ?>" alt="<?php echo esc_attr($post->post_title) ?>">
                                            <?php else : ?>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="140" height="140" viewBox="0 0 140 140" fill="none"><g clip-path="url(#clip0_9208_25189)"><path d="M0 0H140V140H0V0Z" fill="#E0E0E0"/><path d="M92 88H48C46.9 88 46 87.1 46 86V54C46 52.9 46.9 52 48 52H92C93.1 52 94 52.9 94 54V86C94 87.1 93.1 88 92 88ZM68.28 73.573L64.865 70.052L55.23 80.999H85.35L74.565 64.644L68.281 73.572L68.28 73.573ZM62.919 64.523C62.9189 64.0251 62.8208 63.5321 62.6302 63.0721C62.4396 62.6121 62.1603 62.1942 61.8081 61.8422C61.456 61.4901 61.038 61.2109 60.578 61.0204C60.118 60.8299 59.6249 60.7319 59.127 60.732C58.6291 60.7321 58.1361 60.8302 57.6761 61.0208C57.2161 61.2114 56.7982 61.4907 56.4462 61.8429C56.0941 62.195 55.8149 62.613 55.6244 63.073C55.4339 63.533 55.3359 64.0261 55.336 64.524C55.336 65.5296 55.7355 66.4939 56.4465 67.205C57.1576 67.916 58.1219 68.3155 59.1275 68.3155C60.1331 68.3155 61.0975 67.916 61.8085 67.205C62.5195 66.4939 62.919 65.5286 62.919 64.523Z" fill="#C2C2C2"/></g><defs><clipPath id="clip0_9208_25189"><rect width="140" height="140" fill="white"/></clipPath></defs></svg>
                                            <?php endif; ?>
                                        </div>
                                        <div class="wps-content-tabs__item--content">
                                            <h3 class="wps-ellipsis-parent"><span class="wps-ellipsis-text"><?php echo esc_html($post->post_title); ?></span></h3>
                                            <span><?php echo esc_html(Helper::formatNumberWithUnit($post->comments)); ?> <?php esc_html_e('comments', 'wp-statistics') ?></span>
                                        </div>
                                    </a>
                                    <?php $counter++;
                                endforeach;
                            } else {
                                ?><div class="o-wrap o-wrap--no-data"><p><?php esc_html_e('No recent data available.', 'wp-statistics') ?></p></div><?php
                            } 
                        ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="wps-flex-half">
            <div class="wps-content-tabs">
                <input type="radio" name="side-tabs" id="content-recent" checked="checked">
                <label for="content-recent"><?php esc_html_e('Recent', 'wp-statistics') ?></label>
                <div class="wps-content-tabs__content">
                    <?php 
                        if (!empty($data['recent'])) {
                            $counter = 1;
                            
                            foreach ($data['recent'] as $post) : ?>
                                <a class="wps-content-tabs__item" href="<?php echo esc_url(add_query_arg(['type' => 'single', 'post_id' => $post->ID])) ?>">
                                    <div class="wps-content-tabs__item--image">
                                        <span>#<?php echo esc_html($counter); ?></span>
                                        <?php if (has_post_thumbnail($post->ID)) : ?>
                                            <img src="<?php echo esc_url(get_the_post_thumbnail_url($post->ID)) ?>" alt="<?php echo esc_attr($post->post_title) ?>">
                                        <?php else : ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="140" height="140" viewBox="0 0 140 140" fill="none"><g clip-path="url(#clip0_9208_25189)"><path d="M0 0H140V140H0V0Z" fill="#E0E0E0"/><path d="M92 88H48C46.9 88 46 87.1 46 86V54C46 52.9 46.9 52 48 52H92C93.1 52 94 52.9 94 54V86C94 87.1 93.1 88 92 88ZM68.28 73.573L64.865 70.052L55.23 80.999H85.35L74.565 64.644L68.281 73.572L68.28 73.573ZM62.919 64.523C62.9189 64.0251 62.8208 63.5321 62.6302 63.0721C62.4396 62.6121 62.1603 62.1942 61.8081 61.8422C61.456 61.4901 61.038 61.2109 60.578 61.0204C60.118 60.8299 59.6249 60.7319 59.127 60.732C58.6291 60.7321 58.1361 60.8302 57.6761 61.0208C57.2161 61.2114 56.7982 61.4907 56.4462 61.8429C56.0941 62.195 55.8149 62.613 55.6244 63.073C55.4339 63.533 55.3359 64.0261 55.336 64.524C55.336 65.5296 55.7355 66.4939 56.4465 67.205C57.1576 67.916 58.1219 68.3155 59.1275 68.3155C60.1331 68.3155 61.0975 67.916 61.8085 67.205C62.5195 66.4939 62.919 65.5286 62.919 64.523Z" fill="#C2C2C2"/></g><defs><clipPath id="clip0_9208_25189"><rect width="140" height="140" fill="white"/></clipPath></defs></svg>
                                        <?php endif; ?>
                                    </div>
                                    <div class="wps-content-tabs__item--content">
                                        <h3 class="wps-ellipsis-parent"><span class="wps-ellipsis-text"><?php echo esc_html($post->post_title); ?></span></h3>
                                        <span><?php echo esc_html(Helper::formatNumberWithUnit($post->views)); ?> <?php esc_html_e('views', 'wp-statistics') ?></span>
                                    </div>
                                </a>
                                <?php $counter++;
                            endforeach;
                        } else {
                            ?><div class="o-wrap o-wrap--no-data"><p><?php esc_html_e('No recent data available.', 'wp-statistics') ?></p></div><?php
                        } 
                    ?>
                </div>
            </div>
        </div>
    </div>
    <div class="c-footer">
        <div class="c-footer__more">
            <a href="<?php echo esc_url(add_query_arg(['tab' => 'contents', 'pt' => $postType], Menus::admin_url('pages'))); ?>" class="c-footer__more__link">
                <?php echo sprintf(esc_html__('See all %s', 'wp-statistics'), strtolower(Helper::getPostTypeName($postType))) ?>
            </a>
        </div>
    </div>
</div>