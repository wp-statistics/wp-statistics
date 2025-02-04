<div id="delete-visitor-record-confirmation" class="wps-modal wps-modal--confirmation" role="dialog" aria-labelledby="confirmation-modal-title" >
    <div class="wps-modal__overlay"></div>
    <div class="wps-modal__content">
        <button class="wps-modal__close"  type="button" aria-label="<?php esc_attr_e('Close modal', 'wp-statistics'); ?>"></button>
        <h2 id="confirmation-modal-title" class="wps-modal__title">
            <?php esc_html_e('Delete Visitor Record?', 'wp-statistics'); ?>
        </h2>
        <div class="wps-modal__body">
            <p class="wps-modal__description wps-modal__description__dark">
                <?php esc_html_e('Are you sure you want to delete this visitor record?', 'wp-statistics'); ?>
            </p>
            <div class="wps-modal__alert wps-modal__alert__danger">
                <?php esc_html_e('This action is irreversible and will permanently remove all associated data, including visits, views, and events.', 'wp-statistics'); ?>
            </div>
        </div>
        <div class="wps-modal__footer">
            <button class="wps-modal__button wps-modal__button--secondary wps-modal__button--cancel" type="button"
                    data-action="">
               <?php esc_html_e('Cancel', 'wp-statistics'); ?>
            </button>
            <button class="wps-privacy-list__button wps-modal__button wps-modal__button--primary wps-modal__button--danger" type="button"
                data-action="">
                <?php esc_html_e('Yes, Delete', 'wp-statistics'); ?>
            </button>
        </div>
    </div>
</div>
