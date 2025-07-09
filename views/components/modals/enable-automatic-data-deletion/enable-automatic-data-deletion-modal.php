<div id="enable-automatic-data-deletion" class="wps-modal wps-modal--confirmation" role="dialog" aria-labelledby="confirmation-modal-title">
    <div class="wps-modal__overlay"></div>
    <div class="wps-modal__content">
        <button class="wps-modal__close" type="button" aria-label="<?php esc_attr_e('Close modal', 'wp-statistics'); ?>"></button>
        <h2 id="confirmation-modal-title" class="wps-modal__title">
            <?php esc_attr_e('Enable automatic data deletion', 'wp-statistics'); ?>
        </h2>
        <p class="wps-modal__description">
            <?php esc_attr_e('You are about to enable automatic data deletion.', 'wp-statistics'); ?>
            <?php esc_attr_e('This will delete all data older than the selected timeframe, reducing the size of the database tables that Independent Analytics uses.', 'wp-statistics'); ?>
        </p>
        <div class="wps-alert wps-alert__danger">
            <p><?php
                echo sprintf(
                    esc_html__('Data older than %s days will be deleted the next time the cleanup runs.', 'wp-statistics'),
                    '<span></span>'
                ); ?>
        </div>
        <div class="wps-modal__footer">
            <button class="wps-modal__button wps-modal__button--secondary wps-modal__button--cancel" type="button"
                    data-action="closeModal">
                <?php esc_attr_e('Cancel', 'wp-statistics'); ?>
            </button>
            <button class="wps-modal__button wps-modal__button--primary wps-modal__button--danger" type="button" data-action="enable">
                <?php esc_attr_e('Enable Automatic Data Deletion', 'wp-statistics'); ?>
            </button>
        </div>
    </div>
</div>
