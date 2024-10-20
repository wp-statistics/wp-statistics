<?php
namespace WP_STATISTICS;
use WP_Statistics\Components\View;

$isModalDisplayed = User::getMeta('wp_statistics_welcome_modal_displayed', true);
?>

<?php if (!$isModalDisplayed) : ?>
    <div class="wps-modal wps-modal--premium js-wps-premiumModal"  style="display: block">
         <div class="wps-modal__content wps-modal__content--welcome js-wps-premiumModalWelcomeContent">
            <span class="wps-modal__welcome-image"></span>
            <div class="wps-modal__welcome-description">
                <h2><?php esc_html_e('Discover the Full Power of WP Statistics', 'wp-statistics'); ?></h2>
                <p><?php esc_html_e('Explore the powerful features and smart insights that help you track and understand your site’s performance like never before. Want to dive deeper? Unlock the full potential of WP Statistics by exploring Premium features.', 'wp-statistics'); ?></p>
            </div>
            <div class="wps-modal__actions">
                <a class="wps-modal__action-btn js-wps-premiumModalExploreBtn wps-modal__action-btn--premium"><?php esc_html_e('Discover Premium Features', 'wp-statistics'); ?></a>
                <a class="wps-modal__action-btn wps-modal__action-btn--skip js-wps-premiumModalClose"><?php esc_html_e('Skip', 'wp-statistics'); ?></a>
            </div>
        </div>
         <div class="wps-modal__content wps-modal__content--premium-steps js-wps-premiumModalSteps">
            <?php  View::load("components/modals/premium/step-details");  ?>
        </div>
    </div>
<?php endif; ?>