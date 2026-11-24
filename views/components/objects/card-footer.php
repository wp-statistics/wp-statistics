<?php
/**
 * Card footer: 1px rule, date range (or any filter) on the left, "View ..." link on the right.
 *
 * Args:
 *  - href   (string) Link target. Omit to print no link.
 *  - title  (string) Link text, e.g. "View Entry Pages".
 *  - range  (string) Optional plain text on the left, e.g. "Last 30 days".
 *  - filter (string) Optional HTML for the left side (e.g. a date picker), already escaped by the caller.
 *
 * Legacy classes (.c-footer, .c-footer__filter, .c-footer__more, .c-footer__more__link) are kept
 * because add-ons (Data Plus) and older styles target them.
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly
?>
<div class="c-footer wps-card-footer">
    <div class="c-footer__filter wps-card-footer__range">
        <?php if (!empty($filter)) : ?>
            <?php echo $filter; // phpcs:ignore WordPress.Security.EscapeOutput -- caller passes escaped HTML ?>
        <?php elseif (!empty($range)) : ?>
            <span><?php echo esc_html($range); ?></span>
        <?php endif; ?>
    </div>
    <?php if (!empty($href)) : ?>
        <div class="c-footer__more">
            <a href="<?php echo esc_url($href) ?>" class="c-footer__more__link wps-card-footer__link">
                <span class="wps-card-footer__label"><?php echo esc_html($title ?? '') ?></span>
                <svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                    <path d="M10.4191 7.98577L7.73705 5.22727L8.44415 4.5L12.3333 8.50003L8.44415 12.5L7.73705 11.7727L10.4191 9.01429H4.33325V7.98577H10.4191Z" fill="currentColor"></path>
                </svg>
            </a>
        </div>
    <?php endif; ?>
</div>
