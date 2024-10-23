<div class="wps-modal__premium-step js-wps-premiumModalStep wps-modal__premium-step--<?php echo esc_attr($step_name) ?>" data-href="<?php echo esc_url($step_href); ?>">
    <?php echo $description; ?>
    <?php if ( $step_name !== 'first-step') : ?>
        <img  loading="lazy" class="wps-premium-step__image" src="<?php echo WP_STATISTICS_URL . 'assets/images/premium-modal/' . esc_attr($step_name) . '.png'; ?>" alt="<?php echo esc_attr($step_name); ?>">
    <?php endif; ?>
</div>