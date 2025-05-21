<?php
use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;
use WP_Statistics\Components\View;
?>

<?php if ($show_preview) : ?>
    <div class="disabled wps-tooltip-premium">
        <div class="wps-tabs-item">
            <div class="wps-content-tabs__item--image">
                <span><a href="<?php echo esc_url(Menus::admin_url('author-analytics', ['type' => 'single-author', 'author_id' => esc_html($author_id)])) ?>"></a></span>
                <img src="<?php echo esc_url(get_avatar_url($author_id)); ?>" alt="<?php echo esc_html($author_name); ?>" />
            </div>
            <div class="wps-content-tabs__item--content">
                <h3 class="wps-ellipsis-parent"><span  class="wps-ellipsis-text"><?php echo esc_html($author_name); ?></span></h3>
                <span><span class="wps-content-tabs__item--count"><?php echo esc_html(Helper::formatNumberWithUnit($count, 1)); ?></span><?php echo esc_html($count_label) ?></span>
            </div>
        </div>

        <?php
        View::load("components/lock-sections/tooltip-premium", [
            'class'         => 'tooltip-premium--side tooltip-premium--left',
            'addon_name'    => 'wp-statistics-data-plus',
        ]);
        ?>
    </div>
<?php else: ?>
    <a class="wps-tabs-item" href="<?php echo esc_url(Menus::admin_url('author-analytics', ['type' => 'single-author', 'author_id' => esc_html($author_id)])) ?>">
        <div class="wps-content-tabs__item--image">
            <span># <?php echo esc_html($counter); ?></span>
            <img src="<?php echo esc_url(get_avatar_url($author_id)); ?>" alt="<?php echo esc_html($author_name); ?>" />
        </div>
        <div class="wps-content-tabs__item--content">
            <h3 class="wps-ellipsis-parent"><span class="wps-ellipsis-text" title="<?php echo esc_html($author_name); ?>"><?php echo esc_html($author_name); ?></span></h3>
            <span><span class="wps-content-tabs__item--count"><?php echo esc_html(Helper::formatNumberWithUnit($count, 1)); ?></span><?php echo esc_html($count_label) ?></span>
        </div>
    </a>
<?php endif ?>