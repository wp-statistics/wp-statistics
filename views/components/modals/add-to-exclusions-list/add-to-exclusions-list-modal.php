<div id="add-to-exclusions-list" class="wps-modal wps-modal--confirmation" role="dialog" aria-labelledby="confirmation-modal-title" >
    <div class="wps-modal__overlay"></div>
    <div class="wps-modal__content">
        <button class="wps-modal__close"  type="button" aria-label="<?php esc_attr_e('Close modal', 'wp-statistics'); ?>"></button>
        <h2 id="confirmation-modal-title" class="wps-modal__title">
            <?php esc_html_e('Add to Exclusions List', 'wp-statistics'); ?>
            <a href="" target="_blank"><?php esc_html_e('Learn More', 'wp-statistics'); ?> </a>
        </h2>
        <div class="wps-modal__body">
            <div class="wps-modal__alert wps-modal__alert__success">
                <?php esc_html_e('Visitor record successfully deleted.', 'wp-statistics'); ?>
            </div>
            <p class="wps-modal__description wps-modal__description__dark">
                <?php esc_html_e('Exclude this visitor\'s IP address or referrer from future tracking. You can choose one or both options below.', 'wp-statistics'); ?>
            </p>
            
            <form class="wps-modal__options">
                <div class="wps-modal__option">
                    <div>
                        <input id="excludeIp" type="checkbox"  name="wps_exclude_ip" checked="checked">
                        <label for="excludeIp"><?php esc_html_e('Exclude IP Address:', 'wp-statistics'); ?></label>
                    </div>
                    <ul>
                        <li>192.168.0.205</li>
                    </ul>
                </div>
                <div class="wps-modal__option">
                    <div>
                        <input id="excludeReferrer" type="checkbox" name="wps_exclude_referrer"  >
                        <label for="excludeReferrer"><?php esc_html_e('Exclude Referrer:', 'wp-statistics'); ?></label>
                    </div>
                    <ul>
                        <li>anotherwebsite.com</li>
                    </ul>
                </div>
            </form>
        </div>


        <div class="wps-modal__footer">
            <button class="wps-modal__button wps-modal__button--secondary wps-modal__button--cancel" type="button"
                    data-action="">
                <?php esc_html_e('Cancel', 'wp-statistics'); ?>
            </button>
            <button class="wps-privacy-list__button wps-modal__button wps-modal__button--primary wps-modal__button--info" type="button"
                    data-action="">
                <?php esc_html_e('Add to Exclusions', 'wp-statistics'); ?>
            </button>
        </div>
    </div>
</div>
