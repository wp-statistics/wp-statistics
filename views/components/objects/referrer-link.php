<?php
use WP_Statistics\Service\Analytics\Referrals\SourceChannels;
?>

<div class="wps-referral-link">

    <?php if (!empty($title)) : ?>
        <a class="wps-link-arrow wps-link-arrow--external" title="<?php echo esc_html($title)?>" target="_blank"  href="<?php echo esc_url($url); ?>">
            <span><?php echo esc_html($title)?></span>
        </a>
    <?php endif; ?>

    <?php if (!empty($label)) : ?>
        <span class="wps-referral-label"><?php echo esc_html($label)?></span>
    <?php elseif (empty($label) && empty($title)) : ?>
        <span class="wps-referral-label"><?php echo esc_html(SourceChannels::getName('direct')) ?></span>
    <?php endif; ?>
</div>
