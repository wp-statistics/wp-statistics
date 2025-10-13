<div id="enable-automatic-data-deletion" class="wps-modal wps-modal--confirmation" role="dialog" aria-labelledby="confirmation-modal-title">
    <div class="wps-modal__overlay"></div>
    <div class="wps-modal__content">
        <button class="wps-modal__close" type="button" aria-label="<?php esc_attr_e('Close modal', 'wp-statistics'); ?>"></button>
        <h2 id="confirmation-modal-title" class="wps-modal__title">
            <?php esc_attr_e('Confirm aggregation change', 'wp-statistics'); ?>
        </h2>
        <p class="wps-modal__description">
            <?php
            echo sprintf(
                 wp_kses_post(
                    __('You are lowering the retention period to %s. At the next cleanup, data older than this will be aggregated to keep only <b>Visitors</b> and <b>Views</b> for the site and for each page. All other detailed rows will be permanently deleted. This cannot be undone. Consider backing up your database.', 'wp-statistics')
                ),
                '<strong><span></span></strong>'
            );
            ?>
        </p>

        <div class="wps-alert wps-alert__danger">
            <p><?php
                echo sprintf(
                    esc_html__('Data older than %s days will be aggregated at the next cleanup. Detailed logs will be removed.', 'wp-statistics'),
                    '<strong><span></span></strong>'
                ); ?>
        </div>
        <div class="wps-modal__footer">
            <button class="wps-modal__button wps-modal__button--secondary wps-modal__button--cancel" type="button"
                    data-action="closeModal">
                <?php esc_attr_e('Cancel', 'wp-statistics'); ?>
            </button>
            <button class="wps-modal__button wps-modal__button--primary wps-modal__button--danger" type="button" data-action="enable">
                <?php esc_attr_e('I understand', 'wp-statistics'); ?>
            </button>
        </div>
    </div>
</div>
