<?php

use WP_STATISTICS\Admin_Template;
use WP_Statistics\Components\View;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;
use WP_STATISTICS\TimeZone;

$currentDate = TimeZone::getCurrentDate();

$excludedIPs        = $options->getExcludedIPs();
$userRoleExclusions = $options->getUserRoleExclusions();
$excludedUrls       = $options->getExcludedUrls();
$includCountries    = $options->getIncludedCountries();
$excludeCountries   = $options->getExcludedCountries();
$trackerStatus      = $tracker->getTrackerStatus();
?>

<div class="postbox-container wps-postbox-tracker__container">
    <div class="wps-postbox-tracker__box">
        <div class="wps-postbox-tracker__info">
            <img src="<?php echo esc_url(WP_STATISTICS_URL . 'assets/images/tracker/debug.svg') ?>" width="52" height="52">
            <div>
                <h3><?php esc_html_e('Tracker.js Diagnostics Tool', 'wp-statistics'); ?></h3>
                <p><?php esc_html_e('Use this tool to identify and resolve issues affecting tracker.js. Below, you\'ll find diagnostics results, real-time data, and suggestions to ensure your visitor tracking is functioning properly.', 'wp-statistics'); ?></p>
            </div>
        </div>
        <div class="wps-audit-cards">
            <h2 class="wps-audit-cards__title wps-audit-cards__title--dark">
                <?php esc_html_e('Potential Issues', 'wp-statistics'); ?>
            </h2>
            <div class="wps-audit-cards__container">
                <?php
                $trackerIcon = '<svg width="21" height="21" viewBox="0 0 21 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M16.9327 6.66262L13.4688 3.54508C12.482 2.6569 11.9886 2.2128 11.3831 1.98047L11.375 4.37315C11.375 6.43554 11.375 7.46674 12.0157 8.10744C12.6564 8.74814 13.6876 8.74814 15.75 8.74814H18.8826C18.5653 8.13195 17.9973 7.62074 16.9327 6.66262Z" fill="#019939"/>
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M8.75 19.25H12.25C15.5498 19.25 17.1998 19.25 18.2248 18.2248C19.25 17.1998 19.25 15.5498 19.25 12.25V11.8675C19.25 11.1038 19.25 10.5301 19.2127 10.0626H15.75H15.667C14.707 10.0627 13.8587 10.0628 13.1748 9.9708C12.4336 9.87114 11.6925 9.64232 11.0877 9.03744C10.4828 8.4326 10.254 7.69149 10.1543 6.95026C10.0623 6.26644 10.0624 5.41806 10.0625 4.4581L10.0705 1.978C10.0708 1.90586 10.077 1.83452 10.0887 1.76458C9.73122 1.75 9.30633 1.75 8.77608 1.75C5.45885 1.75 3.80026 1.75 2.77512 2.77512C1.75 3.80026 1.75 5.45017 1.75 8.75V12.25C1.75 15.5498 1.75 17.1998 2.77512 18.2248C3.80026 19.25 5.45017 19.25 8.75 19.25ZM9.59849 13.536C9.85477 13.2797 10.2702 13.2797 10.5265 13.536L11.4015 14.411C11.6578 14.6673 11.6578 15.0827 11.4015 15.339L10.5265 16.214C10.2702 16.4703 9.85477 16.4703 9.59849 16.214C9.3422 15.9577 9.3422 15.5423 9.59849 15.286L10.0094 14.875L9.59849 14.464C9.3422 14.2077 9.3422 13.7923 9.59849 13.536ZM9.36442 12.4804C9.49174 12.1411 9.3198 11.7628 8.98039 11.6356C8.64106 11.5083 8.26279 11.6802 8.13553 12.0196L6.82303 15.5196C6.69577 15.8589 6.86772 16.2372 7.20708 16.3644C7.54644 16.4917 7.92471 16.3198 8.05197 15.9804L9.36442 12.4804ZM6.58904 11.786C6.84532 12.0423 6.84532 12.4577 6.58904 12.714L6.17808 13.125L6.58904 13.536C6.84532 13.7923 6.84532 14.2077 6.58904 14.464C6.33276 14.7203 5.91724 14.7203 5.66096 14.464L4.78596 13.589C4.52968 13.3327 4.52968 12.9173 4.78596 12.661L5.66096 11.786C5.91724 11.5297 6.33276 11.5297 6.58904 11.786Z" fill="#019939"/>
                    </svg>
                    ';

                $trackerData = [
                    'svg'         => $trackerIcon,
                    'title'       => __('Tracker.js Not Found', 'wp-statistics'),
                    'description' => __('The tracker.js file is missing or incorrectly placed.', 'wp-statistics'),
                    'content'     => __('Oops! We couldn\'t find your tracker.js file. This means it might be missing or the path is incorrect.', 'wp-statistics'),
                    'suggestion'  => sprintf(__('Please ensure that the tracker.js file exists in the correct directory. Refer to our <a href="%s" target="_blank" rel="noopener">documentation</a> for guidance.', 'wp-statistics'), esc_url(WP_STATISTICS_SITE_URL . '/resources/troubleshoot-the-tracker/?utm_source=wp-statistics&utm_medium=link&utm_campaign=tracker-debugger')),
                    'status'      => 'danger'
                ];

                if (! empty($trackerStatus['exists'])) {
                    $trackerData = [
                        'svg'         => $trackerIcon,
                        'title'       => __('Tracker.js Status: Loaded Successfully', 'wp-statistics'),
                        'description' => '',
                        'content'     => esc_html__('Your tracker.js file is loading correctly with a status code of 200. No issues detected here.', 'wp-statistics'),
                        'status'      => 'success'
                    ];
                }

                View::load("components/audit-card", $trackerData);

                $trackerData = [
                    'svg'         => $trackerIcon,
                    'title'       => esc_html__('Hit Endpoint Status: Successful', 'wp-statistics'),
                    'description' => '',
                    'content'     => esc_html__('Hit recording is responding as expected.', 'wp-statistics'),
                    'status'      => 'success',
                ];

                if (empty($trackerStatus['hitRecordingStatus'])) {
                    $trackerData = [
                        'svg'         => $trackerIcon,
                        'title'       => esc_html__('Hit Endpoint Status: Unexpected Response', 'wp-statistics'),
                        'description' => esc_html__('Hit recording is not responding as expected.', 'wp-statistics'),
                        'content'     => '',
                        'suggestion'  => sprintf(
                            /* %1$s: documentation URL */
                            esc_html__('Please check your security plugins, firewall settings, or any third-party services that might be affecting the request. You may need to review your configuration or whitelist the endpoint. For more information, please visit our %1$s.', 'wp-statistics'),
                            '<a href="https://wp-statistics.com/resources/troubleshoot-the-tracker/?utm_source=wp-statistics&utm_medium=link&utm_campaign=tracker-debugger" target="_blank">' . esc_html__('troubleshooting guide', 'wp-statistics') . '</a>'
                        ),
                        'status'      => 'danger',
                    ];
                }

                View::load("components/audit-card", $trackerData);
                ?>
            </div>
        </div>
        <div class="wps-audit-cards">
            <h2 class="wps-audit-cards__title wps-audit-cards__title--light">
                <?php esc_html_e('Helpful Notes', 'wp-statistics'); ?>
            </h2>

            <div class="wps-audit-cards__container">
                <?php
                $dntIcon = '<svg width="21" height="21" viewBox="0 0 21 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M19.5234 9.7207H17.0174C16.6626 6.71836 14.2816 4.3374 11.2793 3.98262V1.47656C11.2793 1.38633 11.2055 1.3125 11.1152 1.3125H9.88477C9.79453 1.3125 9.7207 1.38633 9.7207 1.47656V3.98262C6.71836 4.3374 4.3374 6.71836 3.98262 9.7207H1.47656C1.38633 9.7207 1.3125 9.79453 1.3125 9.88477V11.1152C1.3125 11.2055 1.38633 11.2793 1.47656 11.2793H3.98262C4.3374 14.2816 6.71836 16.6626 9.7207 17.0174V19.5234C9.7207 19.6137 9.79453 19.6875 9.88477 19.6875H11.1152C11.2055 19.6875 11.2793 19.6137 11.2793 19.5234V17.0174C14.2816 16.6626 16.6626 14.2816 17.0174 11.2793H19.5234C19.6137 11.2793 19.6875 11.2055 19.6875 11.1152V9.88477C19.6875 9.79453 19.6137 9.7207 19.5234 9.7207ZM10.5 15.5039C7.73555 15.5039 5.49609 13.2645 5.49609 10.5C5.49609 7.73555 7.73555 5.49609 10.5 5.49609C13.2645 5.49609 15.5039 7.73555 15.5039 10.5C15.5039 13.2645 13.2645 15.5039 10.5 15.5039Z" fill="#019939"/>
                    <path d="M10.5 8.03906C9.8417 8.03906 9.22647 8.29336 8.76094 8.76094C8.29541 9.22647 8.03906 9.8417 8.03906 10.5C8.03906 11.1583 8.29541 11.7735 8.76094 12.2391C9.22647 12.7025 9.84375 12.9609 10.5 12.9609C11.1562 12.9609 11.7735 12.7046 12.2391 12.2391C12.7025 11.7735 12.9609 11.1562 12.9609 10.5C12.9609 9.84375 12.7046 9.22647 12.2391 8.76094C11.7735 8.29336 11.1583 8.03906 10.5 8.03906Z" fill="#019939"/>
                    </svg>';

                $dntData = [
                    'svg'         => $dntIcon,
                    'title'       => __('Do Not Track (DNT) is Enabled', 'wp-statistics'),
                    'description' => esc_html__('Some visitors are excluded from tracking based on their browser settings.', 'wp-statistics'),
                    'content'     => __('Your site respects visitors\' browser settings to not track their web activity. This may result in a lower number of tracked visitors.', 'wp-statistics'),
                    'suggestion'  => sprintf(__('For more details, visit our DNT feature <a href="%s" target="_blank" rel="noopener">documentation</a>.', 'wp-statistics'), esc_url(WP_STATISTICS_SITE_URL . '/resources/do-not-track/?utm_source=wp-statistics&utm_medium=link&utm_campaign=tracker-debugger')),
                    'status'      => 'info'
                ];

                if (!$options->getOption('do_not_track')) {
                    $dntData = [
                        'svg'         => $dntIcon,
                        'title'       => esc_html__('Do Not Track (DNT) is Disabled', 'wp-statistics'),
                        'description' => '',
                        'content'     => esc_html__('All visitors will be tracked regardless of their browser\'s DNT settings.', 'wp-statistics'),
                        'status'      => 'success'
                    ];
                }

                View::load("components/audit-card", $dntData);

                $atIcon = '<svg width="21" height="21" viewBox="0 0 21 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M11.8689 2.13321C11.5449 2 11.1341 2 10.3125 2C9.49087 2 9.08006 2 8.75605 2.13321C8.32397 2.31083 7.9807 2.65151 7.80174 3.0803C7.72004 3.27605 7.68807 3.50369 7.67555 3.83574C7.65717 4.32372 7.40501 4.7754 6.9789 5.01956C6.55278 5.26372 6.03256 5.2546 5.59755 5.02641C5.30153 4.87114 5.0869 4.78479 4.87524 4.75714C4.41158 4.69656 3.94266 4.82125 3.57164 5.1038C3.29337 5.31571 3.08796 5.66879 2.67716 6.37494C2.26636 7.0811 2.06096 7.43417 2.01518 7.7793C1.95413 8.23945 2.07978 8.70483 2.36448 9.07306C2.49443 9.24115 2.67705 9.38237 2.96049 9.55912C3.37717 9.819 3.64528 10.2617 3.64525 10.75C3.64523 11.2383 3.37713 11.6809 2.96049 11.9407C2.677 12.1175 2.49435 12.2589 2.36439 12.4269C2.07969 12.7951 1.95405 13.2605 2.01509 13.7206C2.06087 14.0657 2.26628 14.4189 2.67707 15.125C3.08788 15.8311 3.29328 16.1843 3.57155 16.3961C3.94257 16.6786 4.41149 16.8033 4.87515 16.7428C5.08681 16.7151 5.30142 16.6288 5.59741 16.4736C6.03245 16.2454 6.55271 16.2362 6.97885 16.4804C7.40499 16.7246 7.65716 17.1763 7.67555 17.6643C7.68807 17.9963 7.72004 18.224 7.80174 18.4197C7.9807 18.8485 8.32397 19.1892 8.75605 19.3668C9.08006 19.5 9.49087 19.5 10.3125 19.5C11.1341 19.5 11.5449 19.5 11.8689 19.3668C12.301 19.1892 12.6443 18.8485 12.8232 18.4197C12.9049 18.224 12.937 17.9963 12.9495 17.6643C12.9679 17.1763 13.2199 16.7246 13.6461 16.4804C14.0722 16.2362 14.5925 16.2454 15.0275 16.4736C15.3235 16.6288 15.5381 16.7151 15.7497 16.7427C16.2134 16.8033 16.6823 16.6786 17.0533 16.3961C17.3317 16.1842 17.537 15.8311 17.9478 15.1249C18.3586 14.4188 18.564 14.0657 18.6099 13.7206C18.6708 13.2605 18.5452 12.795 18.2606 12.4269C18.1305 12.2588 17.9479 12.1174 17.6644 11.9407C17.2478 11.6809 16.9797 11.2382 16.9797 10.7499C16.9797 10.2616 17.2478 9.81909 17.6644 9.5593C17.948 9.38246 18.1306 9.24124 18.2606 9.07306C18.5453 8.70489 18.6709 8.23951 18.6099 7.77935C18.5641 7.43423 18.3587 7.08115 17.9479 6.375C17.5371 5.66885 17.3317 5.31577 17.0534 5.10386C16.6824 4.82131 16.2135 4.69662 15.7498 4.7572C15.5382 4.78485 15.3235 4.87119 15.0276 5.02645C14.5926 5.25464 14.0723 5.26377 13.6462 5.01959C13.22 4.77542 12.9679 4.3237 12.9494 3.8357C12.9369 3.50367 12.9049 3.27604 12.8232 3.0803C12.6443 2.65151 12.301 2.31083 11.8689 2.13321ZM10.3125 13.375C11.7733 13.375 12.9574 12.1998 12.9574 10.75C12.9574 9.30021 11.7733 8.125 10.3125 8.125C8.85169 8.125 7.66751 9.30021 7.66751 10.75C7.66751 12.1998 8.85169 13.375 10.3125 13.375Z" fill="#019939"/>
                    </svg>';

                $atData = [
                    'svg'         => $atIcon,
                    'title'       => __('Consent Plugin Integration is Active', 'wp-statistics'),
                    'description' => esc_html__('Visitors must give consent before tracker.js runs.', 'wp-statistics'),
                    'content'     => __('Tracker.js will not run until visitors provide consent. This may result in up to 50% of visitors not being tracked.', 'wp-statistics'),
                    'suggestion' => sprintf(
                        __('Consider enabling "Anonymous Tracking" to track all visitors anonymously. Learn more in our <a target="_blank" href="%s">Consent Integration guide</a>.', 'wp-statistics'),
                        esc_url(WP_STATISTICS_SITE_URL . '/resources/wp-consent-level-integration/?utm_source=wp-statistics&utm_medium=link&utm_campaign=tracker-debugger')
                    ),
                    'status'      => 'info'
                ];

                $consentLevel = $options->getOption('consent_level_integration', 'disabled');

                if (empty($options->getOption('anonymous_tracking', false)) && ('disabled' === $consentLevel || empty($consentLevel))) {
                    $atData = [
                        'svg'         => $atIcon,
                        'title'       => __('Consent Management is Disabled', 'wp-statistics'),
                        'description' => '',
                        'content'     => esc_html__('Tracker.js runs for all visitors immediately. If you require consent management, please enable it in the settings.', 'wp-statistics'),
                        'status'      => 'success'
                    ];
                }

                if (! empty($options->getOption('anonymous_tracking', false))) {
                    $atData = [
                        'svg'         => $atIcon,
                        'title'       => __('Anonymous Tracking is Enabled', 'wp-statistics'),
                        'description' => '',
                        'content'     => __('All visitors will be tracked anonymously, even without explicit consent, ensuring privacy compliance while collecting essential data.', 'wp-statistics'),
                        'status'      => 'success'
                    ];
                }

                View::load("components/audit-card", $atData);
                ?>
            </div>
        </div>
        <div class="wps-audit-cards">
            <h2 class="wps-audit-cards__title wps-audit-cards__title--light">
                <?php esc_html_e('Verified Items', 'wp-statistics'); ?>
            </h2>

            <div class="wps-audit-cards__container">
                <?php
                $adBlockerIcon = '<svg width="21" height="21" viewBox="0 0 21 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M7.12385 2L2 7.12385V14.3725L7.12385 19.5H14.3725L19.5 14.3725V7.12458L14.3761 2H7.12385ZM7.27115 2.35H14.2289L19.15 7.27115V14.2289L14.2289 19.15H7.27188L2.35 14.2289V7.27188L7.27115 2.35ZM7.67729 3.33L3.33 7.67729V13.8227L7.67729 18.17H13.8227L18.17 13.8227V7.67729L13.8227 3.33H7.67729ZM5.20542 7.6H6.65208L8.31823 13.7323H7.04365L6.71771 12.2725H5.09021L4.765 13.7286H3.54L5.20542 7.6ZM9.07 7.6H10.8864C11.1452 7.59635 11.4041 7.62115 11.66 7.67C11.8802 7.71229 12.0939 7.79615 12.2798 7.91792C12.4548 8.0375 12.5977 8.19865 12.6954 8.3875C12.7975 8.58365 12.8464 8.82865 12.8464 9.1225C12.8464 9.2625 12.8289 9.4025 12.7939 9.53521C12.7589 9.67229 12.7064 9.80135 12.6364 9.92021C12.4866 10.1751 12.245 10.3629 11.9611 10.4452V10.4839C12.3425 10.5641 12.6298 10.7223 12.8216 10.9527C13.0148 11.1839 13.1125 11.5061 13.1125 11.9225C13.1125 12.2375 13.06 12.5073 12.955 12.7311C12.8529 12.9514 12.7027 13.1402 12.5139 13.2911C12.3177 13.4414 12.0939 13.5536 11.8561 13.62C11.5936 13.6929 11.3202 13.7323 11.0475 13.7286H9.07V7.6ZM13.9314 7.6H15.8775C16.1502 7.59635 16.4236 7.63135 16.6898 7.69771C16.9304 7.7575 17.1586 7.86979 17.3511 8.02729C17.5473 8.19135 17.7011 8.40135 17.7959 8.63979C17.9039 8.89135 17.96 9.20271 17.96 9.57385C17.96 9.93115 17.9039 10.2425 17.7923 10.505C17.6902 10.7529 17.5371 10.9739 17.3373 11.1525C17.145 11.3227 16.9196 11.4513 16.6752 11.5302C16.4162 11.6129 16.1457 11.6542 15.8739 11.6527H15.1323V13.7323H13.9314V7.6ZM5.885 8.65365C5.81865 8.97885 5.75229 9.315 5.68521 9.65479C5.61958 9.99385 5.54521 10.3198 5.46865 10.6275L5.33885 11.1911H6.47271L6.35021 10.6275C6.26971 10.3034 6.19629 9.97767 6.13 9.65042C6.06296 9.31728 5.99174 8.98499 5.91635 8.65365H5.885ZM10.2636 8.67479V10.0748H10.8273C11.1211 10.0748 11.3348 10.0077 11.4675 9.87135C11.6002 9.735 11.6673 9.55271 11.6673 9.32229C11.6673 9.09115 11.6002 8.92271 11.4639 8.825C11.3275 8.72729 11.1175 8.67479 10.8339 8.67479H10.2636ZM15.1352 8.7025V10.5546H15.8039C16.4586 10.5546 16.7839 10.2286 16.7839 9.5775C16.7839 9.25885 16.7 9.03135 16.5359 8.89865C16.3711 8.76521 16.1261 8.7025 15.8039 8.7025H15.1352ZM10.2636 11.0504V12.6575H10.9425C11.6002 12.6575 11.9298 12.3775 11.9298 11.8211C11.9298 11.5514 11.8496 11.3552 11.6848 11.2327C11.52 11.1102 11.275 11.0504 10.9425 11.0504H10.2636Z" fill="#019939"/>
                    </svg>';

                $adBlockerData = [
                    'svg'         => $adBlockerIcon,
                    'title'       => __('Ad-blocker Bypass is Disabled', 'wp-statistics'),
                    'description' => __('Visitors using ad-blockers may prevent tracker.js from loading, affecting your analytics data.', 'wp-statistics'),
                    'content'     => '',
                    'suggestion'  => esc_html__('Enable the "Bypass Ad-blocker" option to ensure tracker.js loads for all visitors. Refer to our Ad-blocker Bypass guide for instructions.', 'wp-statistics'),
                    'status'      => 'info'
                ];

                if ($options->getOption('bypass_ad_blockers')) {
                    $adBlockerData = [
                        'svg'         => $adBlockerIcon,
                        'title'       => __('Ad-blocker Bypass is Enabled', 'wp-statistics'),
                        'description' => esc_html__('Tracker.js is working for all visitors, including those using ad-blockers.', 'wp-statistics'),
                        'content'     => esc_html__('Tracker.js is configured to load even for visitors using ad-blockers.', 'wp-statistics'),
                        'status'      => 'success'
                    ];
                }

                View::load("components/audit-card", $adBlockerData);

                $cacheIcon = '<svg width="21" height="21" viewBox="0 0 21 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M15.4109 12.2852C13.4213 12.2852 11.8027 13.9037 11.8027 15.8933C11.8027 17.883 13.4213 19.5015 15.4109 19.5015C17.4005 19.5015 19.0191 17.883 19.0191 15.8933C19.0191 13.9037 17.4008 12.2852 15.4109 12.2852ZM15.4109 18.9002C13.753 18.9002 12.4041 17.5513 12.4041 15.8933C12.4041 14.2354 13.753 12.8865 15.4109 12.8865C17.0689 12.8865 18.4177 14.2354 18.4177 15.8933C18.4177 17.5513 17.0689 18.9002 15.4109 18.9002Z" fill="#019939"/>
                    <path d="M17.2151 15.5892H15.7117V14.0858C15.7117 13.9196 15.5773 13.7852 15.411 13.7852C15.2448 13.7852 15.1104 13.9196 15.1104 14.0858V15.8899C15.1104 16.0562 15.2448 16.1906 15.411 16.1906H17.2151C17.3814 16.1906 17.5158 16.0562 17.5158 15.8899C17.5158 15.7237 17.3814 15.5892 17.2151 15.5892Z" fill="#019939"/>
                    <path d="M11.0037 14.9396C11.332 13.4167 12.4322 12.1815 13.8725 11.6565C12.9686 11.8089 11.9388 11.9109 10.7824 11.9424C10.5208 11.9505 10.2592 11.9548 9.9988 11.9548C9.73751 11.9548 9.47501 11.9505 9.21282 11.9421C7.00551 11.8817 5.24652 11.5699 3.98005 11.1252C3.38469 10.9246 2.87504 10.6883 2.48145 10.418V12.5561V12.7065V12.8568V13.2444C3.33719 14.1377 6.2081 14.9616 9.9985 14.9616C10.3425 14.9616 10.6756 14.9526 11.0037 14.9396Z" fill="#019939"/>
                    <path d="M9.28769 11.0096C9.38571 11.0124 9.48343 11.0151 9.58296 11.0166C9.72007 11.019 9.85868 11.0202 9.9985 11.0202C10.1383 11.0202 10.2769 11.019 10.4143 11.0169C10.5136 11.0154 10.6116 11.0124 10.7096 11.0099C10.7445 11.009 10.78 11.0084 10.8145 11.0075C14.1981 10.8996 16.7224 10.1311 17.5156 9.30328V8.9154V8.76505V8.61471V6.47656C16.0521 7.48174 12.9584 8.01335 9.9985 8.01335C7.03858 8.01335 3.94487 7.48174 2.48145 6.47656V8.61471V8.76505V8.9154V9.30297C3.27464 10.1311 5.79887 10.8996 9.18245 11.0072C9.21733 11.0081 9.25281 11.0087 9.28769 11.0096Z" fill="#019939"/>
                    <path d="M17.5156 5.39951V4.85648V4.70614C17.5156 4.65562 17.4987 4.61022 17.475 4.56843C17.1376 3.27519 14.5433 2 9.9985 2C5.46511 2 2.87353 3.26888 2.52565 4.5588C2.50009 4.60301 2.48145 4.65142 2.48145 4.70614V4.85648V5.39951C3.33448 6.28983 6.18495 7.1116 9.9985 7.1116C13.812 7.1116 16.6628 6.28983 17.5156 5.39951Z" fill="#019939"/>
                    <path d="M10.9005 15.892C10.9005 15.8866 10.9014 15.8814 10.9014 15.876C10.6077 15.8854 10.3085 15.892 9.9985 15.892C6.28628 15.892 3.72447 15.2034 2.48145 14.3281V16.794C2.48145 16.8427 2.49437 16.8887 2.51663 16.9296C2.87173 18.4015 6.04874 19.5002 9.9985 19.5002C10.8831 19.5002 11.7256 19.4424 12.5095 19.3402C11.5272 18.5124 10.9005 17.2745 10.9005 15.892Z" fill="#019939"/>
                    </svg>';

                $cacheData = [
                    'svg'         => $cacheIcon,
                    'title'       => __('No Known Caching Plugins Detected', 'wp-statistics'),
                    'description' => __('No recognized caching plugins were detected.', 'wp-statistics'),
                    'content'     => __('We didn\'t detect any known caching plugins. If you\'re using a caching solution that\'s not recognized, it might still affect tracker.js.', 'wp-statistics'),
                    'suggestion'  => __('Review your caching settings and consult our Caching guide for more information.', 'wp-statistics'),
                    'status'      => 'success'
                ];

                if ($tracker->getCacheStatus()) {
                    $filters = [
                        [
                            'content' => $tracker->getCachePlugin(),
                        ],
                    ];

                    ob_start();
                    View::load('components/objects/tracker-filter-list', ['filters' => $filters]);
                    $filterListsHtml = ob_get_clean();

                    $cacheData = [
                        'svg'         => $cacheIcon,
                        'title'       => __('Caching Plugins Detected', 'wp-statistics'),
                        'description' => __('We have detected the following caching plugin(s) active on your site:', 'wp-statistics'),
                        'content'     => $filterListsHtml . __('Caching may interfere with tracker.js loading properly.', 'wp-statistics'),
                        'suggestion'  => esc_html__('Configure your caching plugin to exclude tracker.js from being cached. See our Caching Compatibility guide for detailed steps.', 'wp-statistics'),
                        'status'      => 'info'
                    ];
                }

                View::load("components/audit-card", $cacheData);

                $itemFilters = [];

                if (! empty($excludedIPs)) {
                    $itemFilters[] = [
                        'title'   => __('IP Addresses', 'wp-statistics'),
                        'content' => $options->formatValuesAsHtml($excludedIPs, 'p'),
                    ];
                }

                if (! empty($userRoleExclusions)) {
                    $itemFilters[] = [
                        'title'   => __('Roles', 'wp-statistics'),
                        'content' => $options->formatValuesAsHtml($userRoleExclusions, 'p'),
                    ];
                }

                if (! empty($includCountries)) {
                    $itemFilters[] = [
                        'title'   => __('Include Countries', 'wp-statistics'),
                        'content' => $options->formatValuesAsHtml($includCountries, 'p'),
                    ];
                }

                if (! empty($excludeCountries)) {
                    $itemFilters[] = [
                        'title'   => __('Exclude Countries', 'wp-statistics'),
                        'content' => $options->formatValuesAsHtml($excludeCountries, 'p'),
                    ];
                }

                if (! empty($excludedUrls)) {
                    $itemFilters[] = [
                        'title'   => __('URLs', 'wp-statistics'),
                        'content' => $options->formatValuesAsHtml($excludedUrls, 'p'),
                    ];
                }

                $filtersIcon = '<svg width="21" height="21" viewBox="0 0 21 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M1.75 4.375C1.75 2.92526 2.92526 1.75 4.375 1.75H16.625C18.0748 1.75 19.25 2.92526 19.25 4.375V5.40012C19.25 6.09632 18.9734 6.76399 18.4811 7.25628L13.3813 12.3561C13.2172 12.5203 13.125 12.7428 13.125 12.9748V15.0251C13.125 15.7213 12.8484 16.389 12.3561 16.8813L10.4294 18.808C9.48675 19.7507 7.875 19.083 7.875 17.75V12.9748C7.875 12.7428 7.78281 12.5203 7.61872 12.3561L2.51884 7.25628C2.02656 6.76399 1.75 6.09632 1.75 5.40012V4.375Z" fill="#019939"/>
                    </svg>';

                ob_start();
                View::load('components/objects/tracker-filter-list', ['filters' => $itemFilters]);
                $itemFilterListsHtml = ob_get_clean();

                $filterData = [
                    'svg'         => $filtersIcon,
                    'title'       => __('No Filters or Exceptions are Applied', 'wp-statistics'),
                    'description' => __('All visitors are being tracked without exclusions.', 'wp-statistics'),
                    'content'     => '',
                    'suggestion'  => sprintf(
                        __('Review these filters in Settings > Filtering & Exceptions. Update if necessary. <a target="_blank" href="%s">Learn more</a>.', 'wp-statistics'),
                        esc_url(WP_STATISTICS_SITE_URL . '/resources/filtering-exceptions-settings/?utm_source=wp-statistics&utm_medium=link&utm_campaign=tracker-debugger')
                    ),
                    'status'      => 'success'
                ];

                if (count($itemFilters) > 0) {
                    $filterData = [
                        'svg'         => $filtersIcon,
                        'title'       => __('Filters or Exceptions are Applied', 'wp-statistics'),
                        'description' => '',
                        'content'     => $itemFilterListsHtml,
                        'suggestion'  => sprintf(
                            __('Review these filters in Settings > Filtering & Exceptions. Update if necessary. <a target="_blank" href="%s">Learn more</a>.', 'wp-statistics'),
                            esc_url(WP_STATISTICS_SITE_URL . '/resources/filtering-exceptions-settings/?utm_source=wp-statistics&utm_medium=link&utm_campaign=tracker-debugger')
                        ),
                        'status'      => 'info'
                    ];
                }

                View::load("components/audit-card", $filterData);

                $errorIcon = '<svg width="21" height="21" viewBox="0 0 21 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M10.5 19.6875C15.5741 19.6875 19.6875 15.5741 19.6875 10.5C19.6875 5.42588 15.5741 1.3125 10.5 1.3125C5.42588 1.3125 1.3125 5.42588 1.3125 10.5C1.3125 15.5741 5.42588 19.6875 10.5 19.6875Z" fill="#019939"/>
                    <path d="M9.47567 6.5625H10.5782L10.2666 8.21901H11.2573C11.8006 8.23062 12.2053 8.35203 12.4716 8.58329C12.7433 8.81449 12.8232 9.25404 12.7114 9.90157L12.1761 12.7896H11.0576L11.5688 10.0316C11.6221 9.74249 11.6061 9.53728 11.5209 9.41587C11.4358 9.29447 11.252 9.23377 10.9697 9.23377L10.0828 9.2251L9.4277 12.7896H8.3252L9.47567 6.5625Z" fill="white"/>
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M4.42373 8.21875H6.56486C7.19329 8.22446 7.64872 8.42114 7.93104 8.80846C8.21336 9.19584 8.30655 9.72484 8.21067 10.3956C8.17346 10.7021 8.09084 11.0027 7.963 11.2976C7.84048 11.5925 7.67012 11.8584 7.45172 12.0955C7.18535 12.3961 6.9004 12.5869 6.59682 12.6679C6.29323 12.7489 5.97894 12.7893 5.65409 12.7893H4.69536L4.39177 14.4372H3.28125L4.42373 8.21875ZM4.87912 11.8093L5.35688 9.21612H5.51827C5.57691 9.21612 5.63811 9.21317 5.70202 9.20746C6.12817 9.20168 6.48227 9.24506 6.76462 9.33753C7.05219 9.42999 7.14807 9.77984 7.05219 10.3869C6.93512 11.1097 6.70596 11.5318 6.36514 11.6532C6.02431 11.7689 5.59816 11.8237 5.08684 11.818H4.97499C4.94303 11.818 4.91108 11.815 4.87912 11.8093Z" fill="white"/>
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M16.0365 8.21875H13.8954L12.7529 14.4372H13.8634L14.1671 12.7893H15.1258C15.4506 12.7893 15.7649 12.7489 16.0685 12.6679C16.3721 12.5869 16.657 12.3961 16.9234 12.0955C17.1418 11.8584 17.3122 11.5925 17.4347 11.2976C17.5625 11.0027 17.6451 10.7021 17.6824 10.3956C17.7782 9.72484 17.6851 9.19584 17.4027 8.80846C17.1204 8.42114 16.665 8.22446 16.0365 8.21875ZM14.8286 9.21612L14.3508 11.8093C14.3828 11.815 14.4148 11.818 14.4467 11.818H14.5585C15.0699 11.8237 15.496 11.7689 15.8368 11.6532C16.1776 11.5318 16.4068 11.1097 16.5239 10.3869C16.6198 9.77984 16.5239 9.42999 16.2363 9.33753C15.954 9.24506 15.5999 9.20168 15.1737 9.20746C15.1098 9.21317 15.0486 9.21612 14.99 9.21612H14.8286Z" fill="white"/>
                    </svg>
                    ';

                $errorData = [
                    'svg'     => $errorIcon,
                    'title'   => __('No PHP Errors Detected', 'wp-statistics'),
                    'content' => __('Tracker.js is functioning without any PHP errors.', 'wp-statistics'),
                    'status'  => 'success'
                ];

                if (count($errors->getErrors()) > 0) {
                    $filters = [
                        [
                            'content' => $errors->printError(),
                        ],
                    ];

                    ob_start();
                    View::load('components/objects/tracker-filter-list', ['filters' => $filters]);
                    $filterListsHtml = ob_get_clean();
                    $errorData       = [
                        'svg'         => $errorIcon,
                        'title'       => __('PHP Errors Detected in tracker.js', 'wp-statistics'),
                        'description' => esc_html__('We found the following error:', 'wp-statistics'),
                        'content'     => $filterListsHtml,
                        'suggestion'  => esc_html__('Suggestion: Please refer to our Error Troubleshooting guide to resolve these issues.', 'wp-statistics'),
                        'status'      => 'warning'
                    ];
                }

                View::load("components/audit-card", $errorData);
                ?>
            </div>
        </div>

        <div class="wps-postbox-tracker__activity">
            <div class="wps-postbox-tracker__activity-head">
                <h4><?php echo esc_html_e('Recent Tracker Activity', 'wp-statistics'); ?></h4>
                <p><?php echo esc_html_e('Get detailed logs of recent user visits for accurate debugging.', 'wp-statistics'); ?></p>
            </div>
            <div class="wps-postbox-tracker__activity-table">
                <div class="o-table-wrapper">
                    <table width="100%" class="o-table wps-new-table">
                        <thead>
                            <tr>
                                <th class="wps-pd-l">
                                    <?php echo esc_html_e('Timestamp', 'wp-statistics'); ?>
                                </th>
                                <th class="wps-pd-l">
                                    <?php echo esc_html_e('Visitor Information', 'wp-statistics'); ?>
                                </th>
                                <th class="wps-pd-l start">
                                    <?php echo esc_html_e('Location', 'wp-statistics'); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($visitors->getLatestVisitors() as $visitor) : ?>
                                <tr>
                                    <td class="wps-pd-l">
                                        <?php
                                        $visitDate = new DateTime($visitor->getLastView(true));
                                        echo esc_html(TimeZone::getElapsedTime($currentDate, $visitDate, $visitor->getLastView()));
                                        ?>
                                    </td>
                                    <td class="wps-pd-l">
                                        <?php View::load("components/visitor-information", ['visitor' => $visitor]); ?>
                                    </td>
                                    <td class="wps-pd-l">
                                        <div class="wps-country-flag wps-ellipsis-parent">
                                            <a href="<?php echo esc_url(Menus::admin_url('geographic', ['type' => 'single-country', 'country' => $visitor->getLocation()->getCountryCode()])) ?>" class="wps-tooltip tooltipstered">
                                                <img src="<?php echo esc_url($visitor->getLocation()->getCountryFlag()) ?>" alt="Hesse, Frankfurt am Main" width="15" height="15">
                                            </a>
                                            <?php $location = Admin_Template::locationColumn($visitor->getLocation()->getCountryCode(), $visitor->getLocation()->getRegion(), $visitor->getLocation()->getCity()); ?>
                                            <span class="wps-ellipsis-text" title="<?php echo esc_attr($location) ?>"><?php echo esc_html($location) ?></span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (count($visitors->getVisitor()) < 1) : ?>
                                <tr>
                                    <td colspan="3">
                                        <div class="o-wrap o-wrap--no-data wps-center">
                                            <?php esc_html_e('No recent data available.', 'wp-statistics'); ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                </div>
            </div>
        </div>

        <div class="wps-postbox-tracker__help">
            <h4><?php echo esc_html_e('Need More Help?', 'wp-statistics'); ?></h4>
            <p>
                <?php
                echo sprintf(
                    __(
                        'If tracker.js is still not working, visit our <a target="_blank" href="%s">troubleshooting guide</a> for detailed steps or <a target="_blank" href="%s">contact our support team</a> for assistance.',
                        'wp-statistics'
                    ),
                    esc_url(WP_STATISTICS_SITE_URL . '/resources-category/troubleshooting/?utm_source=wp-statistics&utm_medium=link&utm_campaign=tracker-debugger'),
                    esc_url(WP_STATISTICS_SITE_URL . '/contact-us/?utm_source=wp-statistics&utm_medium=link&utm_campaign=tracker-debugger')
                );
                ?>
            </p>
        </div>
    </div>
</div>