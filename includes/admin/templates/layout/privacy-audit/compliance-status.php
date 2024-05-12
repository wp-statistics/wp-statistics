<div class="wps-flex wps-privacy-head">
    <div class="postbox-container wps-half-card">
        <div class="postbox wps-postbox-wrap wps-privacy-mode">
            <div class="postbox-header">
                <h2 class="hndle ui-sortable-handle">
                    <?php esc_html_e('Privacy Mode', 'wp-statistics'); ?>
                </h2>
            </div>
            <div class="wps-privacy-mode__items">
                <div class="wps-privacy-mode__item loading" >
                    <input type="radio" id="privacy-mode-friendly" name="privacy-mode" checked>

                    <label for="privacy-mode-friendly">
                        <div class="wps-privacy-mode__head">
                            <div class="wps-privacy-mode__icon">
                                <svg width="17" height="22" viewBox="0 0 17 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M8.5 0.916016L0.967814 2.58983C0.548403 2.68304 0.25 3.05503 0.25 3.48467V12.6392C0.25 14.4781 1.16906 16.1954 2.69915 17.2155L8.5 21.0827L14.3008 17.2155C15.8309 16.1954 16.75 14.4781 16.75 12.6392V3.48467C16.75 3.05503 16.4516 2.68304 16.0322 2.58983L8.5 0.916016ZM2.08333 12.6392V4.21999L8.5 2.79407L14.9167 4.21999V12.6392C14.9167 13.8651 14.304 15.0099 13.2839 15.69L8.5 18.8793L3.7161 15.69C2.69603 15.0099 2.08333 13.8651 2.08333 12.6392ZM12.2777 6.76249C11.2407 5.72328 9.57692 5.6912 8.50029 6.65761C7.42055 5.68976 5.75972 5.72473 4.72196 6.76249C3.68537 7.79908 3.64932 9.45732 4.6138 10.5372L8.49979 14.4293L12.3859 10.5372C13.3504 9.45732 13.3148 7.80169 12.2777 6.76249Z" fill="#56585A"/>
                                </svg>
                            </div>
                            <div>
                                <p><?php esc_html_e('Mode', 'wp-statistics'); ?></p>
                                <h3>
                                    <?php esc_html_e('Privacy Friendly', 'wp-statistics'); ?>
                                    <a href="#" class="wps-tooltip" title="<?php echo __('This indicator reflects the privacy compliance of your WP Statistics settings.', 'wp-statistics') ?>"><i class="wps-tooltip-icon"></i></a>
                                </h3>
                            </div>
                        </div>
                        <div class="wps-privacy-mode__content"></div>
                    </label>
                </div>
                <div class="wps-privacy-mode__item loading">
                    <input type="radio" id="privacy-mode-first" disabled name="privacy-mode">
                    <label for="privacy-mode-first">
                        <div class="wps-privacy-mode__head">
                            <div class="wps-privacy-mode__icon">
                                <svg width="17" height="22" viewBox="0 0 17 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M0.967814 2.58983L8.5 0.916016L16.0322 2.58983C16.4516 2.68304 16.75 3.05503 16.75 3.48467V12.6392C16.75 14.4781 15.8309 16.1954 14.3008 17.2155L8.5 21.0827L2.69915 17.2155C1.16906 16.1954 0.25 14.4781 0.25 12.6392V3.48467C0.25 3.05503 0.548403 2.68304 0.967814 2.58983ZM2.08333 4.21999V12.6392C2.08333 13.8651 2.69603 15.01 3.7161 15.69L8.5 18.8793L13.2839 15.69C14.304 15.01 14.9167 13.8651 14.9167 12.6392V4.21999L8.5 2.79407L2.08333 4.21999ZM9.41667 9.16602H12.1667L7.58333 15.5827V10.9993H4.83333L9.41667 4.58268V9.16602Z" fill="#56585A"/>
                                </svg>
                            </div>
                            <div>
                                <p><?php esc_html_e('Mode', 'wp-statistics'); ?></p>
                                <h3>
                                    <?php esc_html_e('Privacy First', 'wp-statistics'); ?>
                                    <a href="#" class="wps-tooltip" title="<?php echo __('Coming soon in 2024. In Privacy first mode, we change our data tracking method to not use IP address at all (instead we use page_refer to detect return user + timezone to detect the country)', 'wp-statistics') ?>"><i class="wps-tooltip-icon"></i></a>
                                </h3>
                            </div>
                        </div>
                        <div class="wps-privacy-mode__content">
                            <span class="coming-soon">
                                <?php esc_html_e('Coming in 2024', 'wp-statistics'); ?>
                            </span>
                        </div>
                    </label>
                </div>
            </div>
        </div>
    </div>
    <div class="postbox-container wps-half-card">
        <!-- Classes : success or warning -->
        <div class="postbox wps-postbox-wrap wps-privacy-status loading">
            <div class="wps-privacy-status__header">
                <div class="privacy-status__icon"></div>
                <div>
                    <h3><?php esc_html_e('Compliance Status', 'wp-statistics'); ?></h3>
                    <p><span class="wps-privacy-status__rules-mapped-value"></span> <?php esc_html_e('Rules mapped', 'wp-statistics'); ?></p>
                </div>
            </div>
            <div class="wps-privacy-status__content">
                <div class="wps-privacy-status__percent"><span class="wps-privacy-status__percent-value"></span><?php esc_html_e('% Ready', 'wp-statistics'); ?></div>
                <div class="wps-privacy-status__bars">
                    <div class="wps-privacy-status__bar wps-privacy-status__bar-passed">
                        <span class="dot"></span> <span class="wps-privacy-status__passed-value"></span> <?php esc_html_e('Passed', 'wp-statistics'); ?>
                    </div>
                    <div class="wps-privacy-status__bar wps-privacy-status__bar-need-work">
                        <span class="dot"></span> <span class="wps-privacy-status__need-work-value"></span> <?php esc_html_e('Need Work', 'wp-statistics'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
