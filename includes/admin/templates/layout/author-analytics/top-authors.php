<?php

use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;
use WP_Statistics\Utils\Request;
use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHandler;

$pluginHandler        = new PluginHandler();
$showPreview          = !$pluginHandler->isPluginActive('wp-statistics-data-plus');
$postType             = Request::get('pt', 'post');
$postTypeNameSingular = Helper::getPostTypeName($postType, true);
$postTypeNamePlural   = Helper::getPostTypeName($postType);
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
            <div class="wps-tabs">
                <input type="radio" name="tabs" id="author-views" checked="checked">
                <label for="author-views"><?php esc_html_e('Views', 'wp-statistics') ?></label>
                <div class="wps-tabs__content">
                    <?php
                    /** @var stdClass[] */
                    $viewingAuthors = $data['authors']['top_viewing'];
                    $counter        = 1;

                    if ($viewingAuthors) {
                        foreach ($viewingAuthors as $author) :
                            View::load("components/author-box", [
                                'show_preview'  => $showPreview,
                                'author_id'     => $author->id,
                                'author_name'   => $author->name,
                                'count'         => $author->total_views,
                                'counter'       => $counter,
                                'count_label'   => sprintf(esc_html__('%s views', 'wp-statistics'), strtolower($postTypeNameSingular)),
                            ]);
                            $counter++;
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
                <div class="wps-tabs__content">
                    <?php

                    /** @var stdClass[] */
                    $publishingAuthors = $data['authors']['top_publishing'];
                    $counter           = 1;

                    if ($publishingAuthors) {
                        foreach ($publishingAuthors as $author) :
                            View::load("components/author-box", [
                                'show_preview'  => $showPreview,
                                'author_id'     => $author->id,
                                'author_name'   => $author->name,
                                'count'         => $author->post_count,
                                'counter'       => $counter,
                                'count_label'   => sprintf(esc_html__('%s published', 'wp-statistics'), strtolower($postTypeNamePlural)),
                            ]);
                            $counter++;
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
            <div class="wps-tabs">
                <input type="radio" name="side-tabs" id="views-post" checked>
                <label for="views-post"><?php echo sprintf(esc_html__('Views/Per %s', 'wp-statistics'), $postTypeNameSingular) ?></label>
                <div class="wps-tabs__content">
                    <?php
                    /** @var stdClass[] */
                    $topByViewsPerPost = $data['authors']['top_by_views'];
                    $counter           = 1;

                    if ($topByViewsPerPost) {
                        foreach ($topByViewsPerPost as $author) :
                            View::load("components/author-box", [
                                'show_preview'  => $showPreview,
                                'author_id'     => $author->id,
                                'author_name'   => $author->name,
                                'count'         => $author->average_views,
                                'counter'       => $counter,
                                'count_label'   => sprintf(esc_html__('views/%s', 'wp-statistics'), strtolower($postTypeNameSingular)),
                            ]);
                            $counter++;
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
                    <label for="comments-post"><?php echo sprintf(esc_html__('Comments/Per %s', 'wp-statistics'), $postTypeNameSingular) ?></label>
                    <div class="wps-tabs__content">
                        <?php
                        /** @var stdClass[] */
                        $topByCommentsPerPost = $data['authors']['top_by_comments'];
                        $counter              = 1;

                        if ($topByCommentsPerPost) {
                            foreach ($topByCommentsPerPost as $author) :
                                View::load("components/author-box", [
                                    'show_preview'  => $showPreview,
                                    'author_id'     => $author->id,
                                    'author_name'   => $author->name,
                                    'count'         => $author->average_comments,
                                    'counter'       => $counter,
                                    'count_label'   => sprintf(esc_html__('comments/%s', 'wp-statistics'), strtolower($postTypeNameSingular)),
                                ]);
                                $counter++;
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
                <label for="words-post"><?php echo sprintf(esc_html__('Words/Per %s', 'wp-statistics'), $postTypeNameSingular) ?></label>
                <div class="wps-tabs__content">
                    <?php
                    /** @var stdClass[] */
                    $topByWordsPerPost = $data['authors']['top_by_words'];
                    $counter           = 1;

                    if ($topByWordsPerPost) {
                        foreach ($topByWordsPerPost as $author) :
                            View::load("components/author-box", [
                                'show_preview'  => $showPreview,
                                'author_id'     => $author->id,
                                'author_name'   => $author->name,
                                'count'         => $author->average_words,
                                'counter'       => $counter,
                                'count_label'   => sprintf(esc_html__('words/%s', 'wp-statistics'), strtolower($postTypeNameSingular)),
                            ]);
                            $counter++;
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
    <?php
    View::load("components/objects/view-more", [
        'href'  => Menus::admin_url('author-analytics', ['type' => 'authors', 'pt' => $postType]),
        'title' => __('See all authors', 'wp-statistics'),
    ]);
    ?>
</div>