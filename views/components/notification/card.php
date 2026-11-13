<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly ?>
<div class="wps-notification-sidebar__card <?php echo esc_attr($notification->backgroundColor()); ?> wps-notification-<?php echo esc_attr($notification->getID()) ?>">
    <?php if ($notification->getIcon() && !$notification->isPromo()): ?>
        <div class="wps-notification-sidebar__card-icon">
            <span><?php echo esc_html($notification->getIcon()); ?></span>
        </div>
    <?php endif; ?>
    <div class="wps-notification-sidebar__card-body">
        <div class="wps-notification-sidebar__card-title">
            <?php if ($notification->isPromo()): ?>
                <div class="wps-notification-sidebar__eyebrow"><span><?php echo esc_html__('Black Friday', 'wp-statistics'); ?></span><?php if ($notification->getEndsAt()): ?><span class="wps-notification-sidebar__countdown" data-ends-at="<?php echo esc_attr($notification->getEndsAt()); ?>" data-prefix="<?php echo esc_attr__('Ends in', 'wp-statistics'); ?>" data-d="<?php echo esc_attr_x('d', 'days, short', 'wp-statistics'); ?>" data-h="<?php echo esc_attr_x('h', 'hours, short', 'wp-statistics'); ?>" data-m="<?php echo esc_attr_x('m', 'minutes, short', 'wp-statistics'); ?>"></span><?php endif; ?></div>
                <?php if ($notification->getTitle()): ?><div class="wps-notification-sidebar__promo-title"><?php echo esc_html($notification->getTitle()) ?></div><?php endif; ?>
            <?php elseif ($notification->getTitle()): ?>
                <div><?php echo esc_html($notification->getTitle()) ?></div>
            <?php endif; ?>
            <?php if (!$notification->isPromo() && $notification->activatedAt()): ?>
                <div class="wps-notification-sidebar__card-date"><?php echo esc_html($notification->activatedAt()) ?></div>
            <?php endif; ?>
        </div>
        <?php if ($notification->getDescription()): ?>
            <div class="wps-notification-sidebar__card-content">
                <?php echo $notification->getDescription() ?>
            </div>
        <?php endif; ?>
        <?php if ($notification->isPromo() && $notification->getCode()): ?>
            <div class="wps-notification-sidebar__code">
                <code><?php echo esc_html($notification->getCode()); ?></code>
                <button type="button" class="wps-notification-sidebar__copy" data-code="<?php echo esc_attr($notification->getCode()); ?>" data-copied="<?php echo esc_attr__('Copied', 'wp-statistics'); ?>"><?php echo esc_html__('Copy', 'wp-statistics'); ?></button>
            </div>
        <?php endif; ?>
        <div class="wps-notification-sidebar__card-actions">
            <?php if ($notification->primaryButtonTitle() && $notification->primaryButtonUrl()): ?>
                <a href="<?php echo esc_url($notification->primaryButtonUrl()); ?>" class="wps-notification-sidebar__button" target="_blank" rel="noopener noreferrer">
                    <?php echo esc_html($notification->primaryButtonTitle()); ?>
                </a>
            <?php endif; ?>
            <?php if (!$notification->isPromo() && $notification->secondaryButtonTitle() && $notification->secondaryButtonUrl()): ?>
                <a href="<?php echo esc_url($notification->secondaryButtonUrl()); ?>" class="wps-notification-sidebar__button" target="_blank" rel="noopener noreferrer">
                    <?php echo esc_html($notification->secondaryButtonTitle()); ?>
                </a>
            <?php endif; ?>
            <?php if (!$notification->getDismiss()): ?>
                <a href="#" role="button" class="wps-notification-sidebar__dismiss" data-notification-id="<?php echo esc_attr($notification->getID()); ?>">
                    <?php echo esc_html__('Dismiss', 'wp-statistics'); ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>