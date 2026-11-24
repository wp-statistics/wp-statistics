<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;
use WP_Statistics\Service\Admin\LicenseManagement\UpsellLink;

// Keep the selected post type when opening the author's page
$authorUrlArgs = ['type' => 'single-author', 'author_id' => $author_id];
if (!empty($post_type)) {
    $authorUrlArgs['pt'] = $post_type;
}
?>

<?php if ($show_preview) : ?>
    <?php // Preview of a Data Plus report: a click opens the Premium popup ("See what's included"); white label: no popup. ?>
    <?php if (LicenseHelper::areAddonPromptsHidden()) : ?>
    <div class="disabled">
    <?php else : ?>
    <div class="disabled wps-tooltip-premium js-wps-openPremiumModal"<?php echo UpsellLink::popupAttributes('wp-statistics-data-plus', 'author-box'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the helper ?> title="<?php esc_attr_e("Premium feature. See what's included", 'wp-statistics'); ?>">
    <?php endif; ?>
        <div class="wps-tabs-item">
            <div class="wps-content-tabs__item--image">
                <span><a href="<?php echo esc_url(Menus::admin_url('author-analytics', $authorUrlArgs)) ?>"> <span class="screen-reader-text"><?php esc_html_e('View author analytics', 'wp-statistics') ?></span></a></span>
                <img src="<?php echo esc_url(get_avatar_url($author_id)); ?>" alt="<?php echo esc_html($author_name); ?>" />
            </div>
            <div class="wps-content-tabs__item--content">
                <h3 class="wps-ellipsis-parent"><span  class="wps-ellipsis-text"><?php echo esc_html($author_name); ?></span></h3>
                <span><span class="wps-content-tabs__item--count"><?php echo esc_html(Helper::formatNumberWithUnit($count, 1)); ?></span><?php echo esc_html($count_label) ?></span>
            </div>
        </div>
    </div>
<?php else: ?>
    <a class="wps-tabs-item" href="<?php echo esc_url(Menus::admin_url('author-analytics', $authorUrlArgs)) ?>">
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
