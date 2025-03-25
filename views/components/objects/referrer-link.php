<div class="wps-referral-link">
    <?php if (!empty($title)) : ?>
        <a class="wps-link-arrow wps-link-arrow--external"  target="_blank"  href="<?php echo esc_url($url); ?>">
            <span title="<?php echo esc_html($title)?>"><?php echo esc_html($title)?></span>
        </a>
    <?php endif; ?>

    <span class="wps-referral-label"><?php echo esc_html($label)?></span>
</div>
