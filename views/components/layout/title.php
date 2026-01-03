<?php
/**
 * V15 Layout Title
 *
 * Simple page title component.
 *
 * @var string $title Page title
 * @var string $tooltip Optional tooltip text
 */
?>
<div class="wps-wrap__top">
    <?php if (isset($title)) : ?>
        <h2 class="wps_title">
            <?php echo esc_html($title); ?>
            <?php if (!empty($tooltip)) : ?>
                <span class="wps-tooltip" title="<?php echo esc_attr($tooltip); ?>"><i class="wps-tooltip-icon info"></i></span>
            <?php endif; ?>
        </h2>
    <?php endif; ?>
</div>
<div class="wps-wrap__main">
    <div class="wp-header-end"></div>
