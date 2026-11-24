<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly ?>
<div class="wps-referral-link">
    <?php if (!empty($title)) : ?>
        <a class="wps-link-arrow wps-link-arrow--external" title="<?php echo esc_html($title)?>" target="_blank"  href="<?php echo esc_url($url); ?>">
            <span title="<?php echo esc_html($title)?>"><?php echo esc_html($title)?></span>
            <?php if (isset($badge)) : ?>
            <b class="badge"><?php echo esc_html($badge)?></b>
            <?php endif; ?>
        </a>
    <?php elseif (!empty($source_name)) : ?>
        <?php // No referrer, but the source is known from the landing URL (for example Microsoft Ads via msclkid). ?>
        <span title="<?php echo esc_attr($source_name) ?>"><?php echo esc_html($source_name) ?></span>
    <?php endif; ?>

    <span class="wps-referral-label"><?php echo esc_html($label)?></span>
</div>
