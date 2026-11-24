<?php
namespace WP_STATISTICS;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Components\View;

?>
<div class="wps-modal wps-modal--premium js-wps-premiumModal" role="dialog" aria-modal="true" tabindex="-1" aria-label="<?php esc_attr_e('WP Statistics Premium', 'wp-statistics'); ?>">
    <div class="wps-modal__content wps-modal__content--premium-steps js-wps-premiumModalSteps">
        <?php View::load("components/modals/introduce-premium/step-details", ['id_prefix' => 'wps-premium']); ?>
    </div>
</div>
