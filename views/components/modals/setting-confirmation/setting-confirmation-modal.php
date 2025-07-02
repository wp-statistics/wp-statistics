<div id="setting-confirmation" class="wps-modal wps-modal--confirmation" role="dialog" aria-labelledby="confirmation-modal-title" >
    <div class="wps-modal__overlay"></div>
    <div class="wps-modal__content">
        <button class="wps-modal__close"  type="button" aria-label="<?php esc_attr_e('Close modal', 'wp-statistics'); ?>"></button>
        <h2 id="confirmation-modal-title" class="wps-modal__title">
            <?php echo esc_html($title); ?>
        </h2>
        <?php if( isset($description) ) : ?>
        <p class="wps-modal__description">
            <?php echo esc_html($description); ?>
        </p>
        <?php endif; ?>

        <?php if( isset($alert) ) : ?>
        <div class="wps-alert wps-alert__danger">
            <?php echo esc_html($alert); ?>
        </div>
        <?php endif; ?>

        <div class="wps-modal__footer">
            <button class="wps-modal__button wps-modal__button--secondary wps-modal__button--<?php echo esc_html($secondaryButtonStyle); ?>" type="button"
                    data-action="<?php echo esc_html($actions['secondary']); ?>">
                <?php echo esc_html($secondaryButtonText); ?>
            </button>
            <button class="wps-modal__button wps-modal__button--primary wps-modal__button--<?php echo esc_html($primaryButtonStyle); ?>" type="button" data-action="<?php echo esc_html($actions['primary']); ?>">
                <?php echo esc_html($primaryButtonText); ?>
            </button>
        </div>
    </div>
</div>
