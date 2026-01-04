<?php

use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;
use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\Notification\NotificationFactory;

$isPremium               = LicenseHelper::isPremiumLicenseAvailable() ? true : false;
$hasUpdatedNotifications = NotificationFactory::hasUpdatedNotifications();
$displayNotifications    = WP_STATISTICS\Option::get('display_notifications') ? true : false;
$notificationCount       = NotificationFactory::getNewNotificationCount();
?>

<div class="wps-wrap__main wps-wrap__help-center <?php echo $isPremium ? 'wps-wrap__help-center--premium' : '' ?>">
    <div class="wp-header-end"></div>
    <section class="wps-help">
        <div class="wps-help__header">
            <?php if ($displayNotifications && $hasUpdatedNotifications && $notificationCount > 0): ?>
                <a class="wps-help__notification js-wps-open-notification wps-notifications--has-items">
                    <span class="wps-help__notification__dot"></span>
                    <span class="wps-help__notification__text">
                        <?php echo sprintf(_n('You have %s notification', 'You have %s notifications', $notificationCount, 'wp-statistics'), esc_html($notificationCount)); ?>
                    </span>
                </a>
            <?php endif; ?>
            <h1 class="wps-help__title">
                <?php esc_html_e('How can we help you?', 'wp-statistics'); ?>
            </h1>
            <p class="wps-help__description">
                <?php
                printf(
                    esc_html__('Please review the %1$sdocumentation%2$s first. If you still can’t find the answer, open a %3$ssupport ticket%4$s and we will be happy to answer your questions and assist you with any problems.', 'wp-statistics'),
                    '<a href="https://wp-statistics.com/documentation/?utm_source=wp-statistics&utm_medium=link&utm_campaign=help" target="_blank">',
                    '</a>',
                    '<a href="https://wp-statistics.com/contact-us/technical-support/?utm_source=wp-statistics&utm_medium=link&utm_campaign=help" target="_blank">',
                    '</a>'
                );
                ?>
            </p>
            <form class="wps-help__search-form" action="https://wp-statistics.com/?utm_source=wp-statistics&utm_medium=link&utm_campaign=help" method="get" target="_blank">
                <button type="submit" aria-label="<?php esc_attr_e('Submit search for WP Statistics documentation', 'wp-statistics'); ?>"></button>
                <input type="search" name="s" class="wps-help__search-input" aria-label="<?php esc_attr_e('Search Our Documentation', 'wp-statistics'); ?>" placeholder="<?php esc_attr_e('Search Our Documentation', 'wp-statistics'); ?>"/>
            </form>
            <div class="wps-help__popular">
                <ul class="wps-help__popular-items">
                    <li class="wps-help__popular-item">
                        <span><?php esc_html_e('Popular searches:', 'wp-statistics'); ?></span>
                    </li>
                    <li class="wps-help__popular-item">
                        <a href="https://wp-statistics.com/resources/tracker-debugger/?utm_source=wp-statistics&utm_medium=link&utm_campaign=help" class="wps-help__popular-link" target="_blank"">
                            <?php esc_html_e('WP Statistics views not showing', 'wp-statistics'); ?>
                        </a>
                    </li>
                    <li class="wps-help__popular-item">
                        <a href="https://wp-statistics.com/resources/avoiding-pii-data-collection/?utm_source=wp-statistics&utm_medium=link&utm_campaign=help" class="wps-help__popular-link" target="_blank"">
                            <?php esc_html_e('Configuring for Maximum Privacy', 'wp-statistics'); ?>
                        </a>
                    </li>
                    <li class="wps-help__popular-item">
                        <a href="https://wp-statistics.com/resources/enhancing-data-accuracy/?utm_source=wp-statistics&utm_medium=link&utm_campaign=help" class="wps-help__popular-link" target="_blank"">
                            <?php esc_html_e('Improve WP Statistics accuracy', 'wp-statistics'); ?>
                        </a>
                    </li>
                    <li class="wps-help__popular-item">
                        <a href="https://wp-statistics.com/resources/counting-unique-visitors-without-cookies/?utm_source=wp-statistics&utm_medium=link&utm_campaign=help" class="wps-help__popular-link" target="_blank"">
                            <?php esc_html_e('Counting Unique Visitors Without Cookies', 'wp-statistics'); ?>
                        </a>
                    </li>
                    <li class="wps-help__popular-item">
                        <a href="https://wp-statistics.com/resources/integrating-wp-statistics-with-consent-management-plugins/?utm_source=wp-statistics&utm_medium=link&utm_campaign=help" class="wps-help__popular-link" target="_blank"">
                            <?php esc_html_e('Connect WP Statistics to consent plugins', 'wp-statistics'); ?>
                        </a>
                    </li>
                    <li class="wps-help__popular-item">
                        <a href="https://wp-statistics.com/resources/location-detection-methods-in-wp-statistics/?utm_source=wp-statistics&utm_medium=link&utm_campaign=help" class="wps-help__popular-link" target="_blank"">
                            <?php esc_html_e('Best IP-location method for WP Statistics', 'wp-statistics'); ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="wps-help__grid">
            <?php

            $card1 = [
                'icon'            => '<svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12.1802 26.5578H18.3602V40.9578C18.3602 44.3178 20.1802 44.9978 22.4002 42.4778L37.5402 25.2778C39.4002 23.1778 38.6202 21.4378 35.8002 21.4378H29.6202V7.03784C29.6202 3.67784 27.8002 2.99784 25.5802 5.51784L10.4402 22.7178C8.6002 24.8378 9.3802 26.5578 12.1802 26.5578Z" stroke="#1E1E20" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg> 
                                        ',
                'category'        => esc_html__('Getting Started', 'wp-statistics'),
                'title'           => esc_html__('Learn how WP Statistics works.', 'wp-statistics'),
                'view_more_title' => esc_html__('View all articles', 'wp-statistics'),
                'view_more_link'  => esc_url(WP_STATISTICS_SITE_URL.'/resources-category/getting-started/?utm_source=wp-statistics&utm_medium=link&utm_campaign=help'),
                'description'     => esc_html__('Explore the full documentation to understand key features and get the most out of WP Statistics.', 'wp-statistics'),
            ];
            View::load("components/objects/help-center-card", $card1);


            $card2 = [
                'icon'            => '<svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M44 33.4833V9.34331C44 6.94331 42.04 5.16331 39.66 5.36331H39.54C35.34 5.72331 28.96 7.86331 25.4 10.1033L25.06 10.3233C24.48 10.6833 23.52 10.6833 22.94 10.3233L22.44 10.0233C18.88 7.80331 12.52 5.68331 8.32 5.34331C5.94 5.14331 4 6.94331 4 9.32331V33.4833C4 35.4033 5.56 37.2033 7.48 37.4433L8.06 37.5233C12.4 38.1033 19.1 40.3033 22.94 42.4033L23.02 42.4433C23.56 42.7433 24.42 42.7433 24.94 42.4433C28.78 40.3233 35.5 38.1033 39.86 37.5233L40.52 37.4433C42.44 37.2033 44 35.4033 44 33.4833Z" stroke="#1E1E20" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M24 10.9766V40.9766" stroke="#1E1E20" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M15.502 16.9766H11.002" stroke="#1E1E20" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M17.002 22.9766H11.002" stroke="#1E1E20" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>',
                'category'        => esc_html__('Guides', 'wp-statistics'),
                'title'           => esc_html__('Make the most of WP Statistics.', 'wp-statistics'),
                'view_more_title' => esc_html__('View all articles', 'wp-statistics'),
                'view_more_link'  => esc_url(WP_STATISTICS_SITE_URL.'/resources-category/guides/?utm_source=wp-statistics&utm_medium=link&utm_campaign=help'),
                'description'     => esc_html__('Find easy, step-by-step instructions to track user activity, analyze data, and improve your site’s performance.', 'wp-statistics'),
            ];
            View::load("components/objects/help-center-card", $card2);

            $card3 = [
                'icon'            => '<svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M16 19.9961L12 23.9961L16 27.9961" stroke="#1E1E20" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M32.002 19.9961L36.002 23.9961L32.002 27.9961" stroke="#1E1E20" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M24 44.0039C35.0457 44.0039 44 35.0496 44 24.0039C44 12.9582 35.0457 4.00391 24 4.00391C12.9543 4.00391 4 12.9582 4 24.0039C4 35.0496 12.9543 44.0039 24 44.0039Z" stroke="#1E1E20" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M25.998 19.3398L21.998 28.6599" stroke="#1E1E20" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    ',
                'category'        => esc_html__('Privacy', 'wp-statistics'),
                'title'           => esc_html__('Your data, your rules.', 'wp-statistics'),
                'view_more_title' => esc_html__('View all articles', 'wp-statistics'),
                'view_more_link'  => esc_url(WP_STATISTICS_SITE_URL.'/resources-category/privacy/?utm_source=wp-statistics&utm_medium=link&utm_campaign=help'),
                'description'     => esc_html__('Learn how WP Statistics protects user privacy and how you can manage data settings to stay compliant.', 'wp-statistics'),
            ];
            View::load("components/objects/help-center-card", $card3);


            $card4 = [
                'icon'            => '<svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M43.3184 20.881L41.3584 29.241C39.6784 36.461 36.3584 39.381 30.1184 38.781C29.1184 38.701 28.0384 38.521 26.8784 38.241L23.5184 37.441C15.1784 35.461 12.5984 31.341 14.5584 22.981L16.5184 14.601C16.9184 12.901 17.3984 11.421 17.9984 10.201C20.3384 5.36099 24.3184 4.06099 30.9984 5.64099L34.3384 6.42099C42.7184 8.38099 45.2784 12.521 43.3184 20.881Z" stroke="#1E1E20" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M30.1187 38.7831C28.8787 39.6231 27.3187 40.3231 25.4187 40.9431L22.2587 41.9831C14.3187 44.5431 10.1387 42.4031 7.55873 34.4631L4.99873 26.5631C2.43873 18.6231 4.55873 14.4231 12.4987 11.8631L15.6587 10.8231C16.4787 10.5631 17.2587 10.3431 17.9987 10.2031C17.3987 11.4231 16.9187 12.9031 16.5187 14.6031L14.5587 22.9831C12.5987 31.3431 15.1787 35.4631 23.5187 37.4431L26.8787 38.2431C28.0387 38.5231 29.1187 38.7031 30.1187 38.7831Z" stroke="#1E1E20" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M25.2793 17.0625L34.9793 19.5225" stroke="#1E1E20" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M23.3203 24.8008L29.1203 26.2808" stroke="#1E1E20" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>',
                'category'        => esc_html__('Troubleshooting', 'wp-statistics'),
                'title'           => esc_html__('Solve issues quickly.', 'wp-statistics'),
                'view_more_title' => esc_html__('View all articles', 'wp-statistics'),
                'view_more_link'  => esc_url(WP_STATISTICS_SITE_URL.'/resources-category/troubleshooting/?utm_source=wp-statistics&utm_medium=link&utm_campaign=help'),
                'description'     => esc_html__('Get help fixing common problems, errors, or setup issues and keep your site running smoothly.', 'wp-statistics')
            ];
            View::load("components/objects/help-center-card", $card4);

            $card5 = [
                'icon'            => '<svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M27.4598 7.02125L30.9798 14.0612C31.4598 15.0412 32.7398 15.9812 33.8198 16.1612L40.1998 17.2212C44.2798 17.9012 45.2398 20.8612 42.2998 23.7812L37.3398 28.7412C36.4998 29.5812 36.0398 31.2012 36.2998 32.3612L37.7198 38.5012C38.8398 43.3612 36.2598 45.2412 31.9598 42.7012L25.9798 39.1612C24.8998 38.5212 23.1198 38.5212 22.0198 39.1612L16.0398 42.7012C11.7598 45.2412 9.15982 43.3412 10.2798 38.5012L11.6998 32.3612C11.9598 31.2012 11.4998 29.5812 10.6598 28.7412L5.69982 23.7812C2.77982 20.8612 3.71982 17.9012 7.79982 17.2212L14.1798 16.1612C15.2398 15.9812 16.5198 15.0412 16.9998 14.0612L20.5198 7.02125C22.4398 3.20125 25.5598 3.20125 27.4598 7.02125Z" stroke="#1E1E20" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>',
                'category'        => esc_html__('FEATURE REQUESTS', 'wp-statistics'),
                'title'           => esc_html__('Help Shape The Future.', 'wp-statistics'),
                'view_more_title' => esc_html__('Submit Request', 'wp-statistics'),
                'view_more_link'  => 'https://feedback.veronalabs.com/boards/wp-statistics',
                'description'     => esc_html__('Submit your ideas or vote on features requested by others. Your feedback helps us improve WP Statistics based on what matters most to you.', 'wp-statistics')
            ];

            $card5_pro = [
                'icon'            => '<svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M27.4598 7.02125L30.9798 14.0612C31.4598 15.0412 32.7398 15.9812 33.8198 16.1612L40.1998 17.2212C44.2798 17.9012 45.2398 20.8612 42.2998 23.7812L37.3398 28.7412C36.4998 29.5812 36.0398 31.2012 36.2998 32.3612L37.7198 38.5012C38.8398 43.3612 36.2598 45.2412 31.9598 42.7012L25.9798 39.1612C24.8998 38.5212 23.1198 38.5212 22.0198 39.1612L16.0398 42.7012C11.7598 45.2412 9.15982 43.3412 10.2798 38.5012L11.6998 32.3612C11.9598 31.2012 11.4998 29.5812 10.6598 28.7412L5.69982 23.7812C2.77982 20.8612 3.71982 17.9012 7.79982 17.2212L14.1798 16.1612C15.2398 15.9812 16.5198 15.0412 16.9998 14.0612L20.5198 7.02125C22.4398 3.20125 25.5598 3.20125 27.4598 7.02125Z" stroke="#1E1E20" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>',
                'category'        => esc_html__('REVIEWS', 'wp-statistics'),
                'title'           => esc_html__('Leave Us a Review.', 'wp-statistics'),
                'view_more_title' => esc_html__('Leave a Review', 'wp-statistics'),
                'view_more_link'  => 'https://wordpress.org/support/plugin/wp-statistics/reviews/?filter=5#new-post',
                'description'     => esc_html__('Love WP Statistics? Post a public review so other WordPress users can discover the plugin’s benefits.', 'wp-statistics')
            ];

            if (!$isPremium) {
                View::load("components/objects/help-center-card", $card5);
            } else {
                View::load("components/objects/help-center-card", $card5_pro);
            }


            $card6 = [
                'icon'        => '<svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M36 37.7195H34.48C32.88 37.7195 31.36 38.3395 30.24 39.4595L26.8199 42.8395C25.2599 44.3795 22.72 44.3795 21.16 42.8395L17.74 39.4595C16.62 38.3395 15.08 37.7195 13.5 37.7195H12C8.68 37.7195 6 35.0595 6 31.7795V9.95947C6 6.67947 8.68 4.01953 12 4.01953H36C39.32 4.01953 42 6.67947 42 9.95947V31.7795C42 35.0395 39.32 37.7195 36 37.7195Z" stroke="#1E1E20" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M23.9999 19.9996C26.5735 19.9996 28.6599 17.9132 28.6599 15.3396C28.6599 12.766 26.5735 10.6797 23.9999 10.6797C21.4262 10.6797 19.3398 12.766 19.3398 15.3396C19.3398 17.9132 21.4262 19.9996 23.9999 19.9996Z" stroke="#1E1E20" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M32 31.3207C32 27.7207 28.42 24.8008 24 24.8008C19.58 24.8008 16 27.7207 16 31.3207" stroke="#1E1E20" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>',
                'category'    => esc_html__('Follow Us', 'wp-statistics'),
                'title'       => esc_html__('Stay informed.', 'wp-statistics'),
                'social'      => true,
                'description' => esc_html__('Get updates, tips, and news about WP Statistics to help you get even more from your analytics.', 'wp-statistics')
            ];
            View::load("components/objects/help-center-card", $card6);

            ?>
        </div>

        <?php if (!$isPremium) {

            $cta = [
                'cta_title'   => esc_html__('Upgrade Now', 'wp-statistics'),
                'cta_link'    => esc_url(WP_STATISTICS_SITE_URL.'/pricing/?utm_source=wp-statistics&utm_medium=link&utm_campaign=help'),
                'title'       => esc_html__('Want More Features?', 'wp-statistics'),
                'description' => esc_html__('Upgrade your plan to access more tools and supercharge your growth.', 'wp-statistics')
            ];
            View::load("components/objects/help-center-cta", $cta);
        } else {
            $cta = [
                'cta_title'   => esc_html__('Submit a Feature Request', 'wp-statistics'),
                'cta_link'    => 'https://feedback.veronalabs.com/boards/wp-statistics',
                'title'       => esc_html__('Got Ideas for New Features?', 'wp-statistics'),
                'description' => esc_html__('We’re building WP Statistics with you in mind. Share your suggestions or upvote existing ones to help shape what comes next.', 'wp-statistics')
            ];
            View::load("components/objects/help-center-cta", $cta);
        }
        ?>
    </section>
