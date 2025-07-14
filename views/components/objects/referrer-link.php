<div class="wps-referral-link">
    <?php if (!empty($title)) : ?>
        <a class="wps-link-arrow wps-link-arrow--external" title="<?php echo esc_html($title)?>" target="_blank"  href="<?php echo esc_url($url); ?>">
            <span title="<?php echo esc_html($title)?>"><?php echo esc_html($title)?></span>
            <?php if (isset($badge)) : ?>
            <b class="badge"><?php echo esc_html($badge)?></b>
            <?php endif; ?>
        </a>
    <?php endif; ?>

    <span class="wps-referral-label"><?php echo esc_html($label)?></span>
</div>
