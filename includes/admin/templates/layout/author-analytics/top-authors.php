<?php
use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus; 
use WP_Statistics\Utils\Request;

$postType               = Request::get('pt', 'post');
$postTypeNameSingular   = Helper::getPostTypeName($postType, true);
$postTypeNamePlural     = Helper::getPostTypeName($postType);
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
                                        <span><?php echo esc_html(Helper::formatNumberWithUnit($author->total_views)); ?> <?php echo sprintf(esc_html__('%s views', 'wp-statistics'), strtolower($postTypeNameSingular)) ?></span>
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
                                        <span><?php echo esc_html(Helper::formatNumberWithUnit($author->post_count)); ?> <?php echo sprintf(esc_html__('%s published', 'wp-statistics'), strtolower($postTypeNamePlural)) ?></span>
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

                <input type="radio" name="side-tabs" id="views-post" checked>
                <label for="views-post"><?php echo sprintf(esc_html__('Views/%s', 'wp-statistics'), $postTypeNameSingular) ?></label>
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
                                        <span><?php echo esc_html(Helper::formatNumberWithUnit($author->average_views)); ?> <?php echo sprintf(esc_html__('views/%s', 'wp-statistics'), strtolower($postTypeNameSingular)) ?></span>
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

                <?php if (post_type_supports($postType, 'comments')) : ?>
                    <input type="radio" name="side-tabs" id="comments-post">
                    <label for="comments-post"><?php echo sprintf(esc_html__('Comments/%s', 'wp-statistics'), $postTypeNameSingular) ?></label>
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
                                            <span><?php echo esc_html(Helper::formatNumberWithUnit($author->average_comments)) . sprintf(esc_html__(' comments/%s', 'wp-statistics'), strtolower($postTypeNameSingular)); ?> </span>
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
                <?php endif; ?>

                <input type="radio" name="side-tabs" id="words-post">
                <label for="words-post"><?php echo sprintf(esc_html__('Words/%s', 'wp-statistics'), $postTypeNameSingular) ?></label>
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
                                        <span><?php echo esc_html(Helper::formatNumberWithUnit($author->average_words)); ?> <?php echo sprintf(esc_html__('words/%s', 'wp-statistics'), strtolower($postTypeNameSingular)) ?></span>
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
            <a href="<?php echo esc_url(Menus::admin_url('author-analytics', ['type' => 'authors', 'pt' => $postType])); ?>" class="c-footer__more__link" title="<?php esc_html_e('See all authors', 'wp-statistics') ?>"><?php esc_html_e('See all authors', 'wp-statistics') ?></a>
        </div>
    </div>
</div>