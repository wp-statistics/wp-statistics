<div class="wps-privacy-audit__head">
    <span class="wps-privacy-audit__status-icon"></span>
    <div class="wps-privacy-audit__status-title">
        <span>
            <?php esc_html_e('Privacy Status', 'wp-statistics'); ?>
        </span>

        <h3>
            <?php if ($isCompliant) : ?>
                <?php esc_html_e('Good', 'wp-statistics'); ?>
            <?php else : ?>
                <?php esc_html_e('Should be improved', 'wp-statistics'); ?>
            <?php endif; ?>
        </h3>
    </div>

    <div class="wps-privacy-audit__status-description">
        <?php esc_html_e('The Privacy Audit ensures WP Statistics settings comply with privacy standards, offering insights and actionable steps to protect user data.', 'wp-statistics'); ?>
    </div>

    <div class="wps-privacy-audit__status-bar">
        <div class="wps-privacy-status loading">
            <div class="wps-privacy-status__content">
                <div class="wps-privacy-status__percent-container">
                    <div class="wps-privacy-status__percent">
                        <span class="wps-privacy-status__percent-value"></span><?php esc_html_e('% Ready', 'wp-statistics'); ?>
                    </div>
                    <div class="wps-privacy-status__mapped">
                        <span class="dot"></span> <span class="wps-privacy-status__passed-value"></span> <?php esc_html_e('of', 'wp-statistics'); ?> <span class="wps-privacy-status__final-value"></span> <?php esc_html_e('Rules Passed', 'wp-statistics'); ?>
                    </div>
                </div>
                <div class="wps-privacy-status__bars">
                    <div class="wps-privacy-status__bar wps-privacy-status__bar-passed">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>