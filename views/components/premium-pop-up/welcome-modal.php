<?php

use WP_Statistics\Components\View;

?>

<div class="wps-modal wps-modal--premium js-wps-premiumModal">
    <div class="wps-modal__content wps-modal__content--welcome js-wps-premiumModalWelcomeContent">
        <span class="wps-modal__welcome-image"></span>
        <div class="wps-modal__welcome-description">
            <h2><?php esc_html_e('Welcome to the All-New WP Statistics!', 'wp-statistics'); ?></h2>
            <p><?php esc_html_e('Meet the new WP Statistics! Faster, smarter insights await. Try Premium for even more!', 'wp-statistics'); ?></p>
        </div>
        <div class="wps-modal__actions">
            <a class="wps-modal__action-btn js-wps-premiumModalExploreBtn wps-modal__action-btn--premium"><?php esc_html_e('Explore Premium', 'wp-statistics'); ?></a>
            <a class="wps-modal__action-btn wps-modal__action-btn--skip js-wps-premiumModalClose"><?php esc_html_e('Skip', 'wp-statistics'); ?></a>
        </div>
    </div>
    <div class="wps-modal__content wps-modal__content--premium-steps js-wps-premiumModalSteps">
        <?php  View::load("components/premium-pop-up/step-details");  ?>
    </div>
</div>

