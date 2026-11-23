<?php
/**
 * Calm grey bar on the Add-ons and Download screens when the remote add-on list couldn't be loaded and
 * the saved list is shown instead.
 *
 * Args:
 *  - retry_url (string) "Try again" link (nonce'd, clears the cached list).
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly
?>
<div class="wps-addons-fallback" role="status">
    <svg class="wps-addons-fallback__icon" width="16" height="16" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="9.5" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M12 11v5.5" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/><circle cx="12" cy="7.6" r="1.2" fill="currentColor"/></svg>
    <span class="wps-addons-fallback__text"><?php esc_html_e('Couldn\'t refresh the list. Showing saved info.', 'wp-statistics'); ?></span>
    <?php if (!empty($retry_url)) : ?>
        <a class="wps-addons-fallback__retry" href="<?php echo esc_url($retry_url); ?>"><?php esc_html_e('Try again', 'wp-statistics'); ?></a>
    <?php endif; ?>
</div>
