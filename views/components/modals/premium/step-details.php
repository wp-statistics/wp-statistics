<?php

use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHelper;

?>
<div class="wps-premium-step">
    <div class="wps-premium-step__header">
        <span class="wps-premium-step__skip js-wps-premiumModalClose"></span>
        <span><?php esc_html_e('WP Statistics Premium', 'wp-statistics'); ?></span>
        <div class="js-wps-premium-first-step__head">
            <?php if (LicenseHelper::isPremiumLicenseAvailable()) : ?>
                <p><?php esc_html_e('You\'re All Set with WP Statistics Premium', 'wp-statistics'); ?></p>
            <?php elseif (LicenseHelper::isLicenseAvailable() && !LicenseHelper::isPremiumLicenseAvailable()) : ?>
                <p><?php esc_html_e('You\'re Already Enjoying Some Premium Add-Ons!', 'wp-statistics'); ?></p>
            <?php else : ?>
                <p><?php esc_html_e('Unlock WP Statistics Premium', 'wp-statistics'); ?></p>
            <?php endif; ?>
        </div>
         <div class="js-wps-premium-steps__head">
             <p><?php esc_html_e('Try the upgrade. See more. Do more.', 'wp-statistics'); ?></p>
         </div>
     </div>
    <div class="wps-premium-step__body">
        <div class="wps-premium-step__content">
            <?php
            $default_description = __('<p>Get access to all advanced features in one package. With WP Statistics Premium, you can explore powerful add-ons that enhance your analytics experience and provide deeper insights into your site’s performance.</p>
            <p>Curious about what each feature offers?</p><p>Simply click on any add-on to learn more and see how WP Statistics Premium can power your site’s growth.</p>', 'wp-statistics');

            $premium_description = __('<p>Thank you for supporting us by being a Premium user! Since you previously had the Bundle, you now have access to all the Premium features. There’s nothing more you need to do—just enjoy the full range of advanced tools and insights.</p>
            <p>We truly appreciate your continued support. With your help, we’re able to keep improving and providing even better analytics for your site.</p>', 'wp-statistics');

            $license_description = __('<p>It looks like you’ve unlocked some of our great add-ons. Awesome! To get the most out of WP Statistics, upgrade to Premium and get access to all our advanced features and add-ons. Unlock deeper insights and powerful analytics with the full package at your fingertips.</p>', 'wp-statistics');

            $data = [
                'description' => $default_description,
                'step_name'   => 'first-step',
                'step_href'   => esc_url(WP_STATISTICS_SITE_URL . '/product/add-ons-bundle/?utm_source=wp-statistics&utm_medium=link&utm_campaign=pop-up-premium')
            ];

            if (LicenseHelper::isPremiumLicenseAvailable()) {
                $data['description'] = $premium_description;
            } elseif (LicenseHelper::isLicenseAvailable() && !LicenseHelper::isPremiumLicenseAvailable()) {
                $data['description'] = $license_description;
            }

            View::load("components/modals/premium/step-content", $data);


            $data = [
                'description' => sprintf(
                    __('<p>Elevate your analytics with custom post tracking, detailed visitor behavior insights, and advanced filtering. Gain deeper understanding with content-specific widgets and traffic analysis tools. <a target="_blank" href="%s">Learn more</a></p>', 'wp-statistics'),
                    esc_url(WP_STATISTICS_SITE_URL . '/product/wp-statistics-data-plus/?utm_source=wp-statistics&utm_medium=link&utm_campaign=pop-up-premium')
                ),
                'step_name'   => 'wp-statistics-data-plus',
                'step_href'   => esc_url(WP_STATISTICS_SITE_URL . '/product/wp-statistics-data-plus/?utm_source=wp-statistics&utm_medium=link&utm_campaign=pop-up-premium')
            ];
            View::load("components/modals/premium/step-content", $data);

            $data = [
                'description' => sprintf(__('<p>Instantly view customizable performance charts for all posts and pages, with quick access to traffic data via the admin bar. Gain insights at a glance.<a target="_blank" href="%s">Learn more</a></p>', 'wp-statistics'),
                    esc_url(WP_STATISTICS_SITE_URL . '/product/wp-statistics-mini-chart/?utm_source=wp-statistics&utm_medium=link&utm_campaign=pop-up-premium')
                ),
                'step_name'   => 'wp-statistics-mini-chart',
                'step_href'   => esc_url(WP_STATISTICS_SITE_URL . '/product/wp-statistics-mini-chart/?utm_source=wp-statistics&utm_medium=link&utm_campaign=pop-up-premium')
            ];
            View::load("components/modals/premium/step-content", $data);

            $data = [
                'description' => sprintf(__('<p>Receive scheduled, customizable traffic reports with detailed charts straight to your inbox. Stay informed about your website\'s performance effortlessly. <a target="_blank" href="%s">Learn more</a></p>', 'wp-statistics'),
                    esc_url(WP_STATISTICS_SITE_URL . '/product/wp-statistics-advanced-reporting/?utm_source=wp-statistics&utm_medium=link&utm_campaign=pop-up-premium')
                ),
                'step_name'   => 'wp-statistics-advanced-reporting',
                'step_href'   => esc_url(WP_STATISTICS_SITE_URL . '/product/wp-statistics-advanced-reporting/?utm_source=wp-statistics&utm_medium=link&utm_campaign=pop-up-premium')
            ];
            View::load("components/modals/premium/step-content", $data);

            $data = [
                'description' => sprintf(__('<p>Monitor visitor activity and online users live, without refreshing the page. Stay updated on your website\'s performance in real-time. <a target="_blank" href="%s">Learn more</a></p>', 'wp-statistics'),
                    esc_url(WP_STATISTICS_SITE_URL . '/product/wp-statistics-realtime-stats/?utm_source=wp-statistics&utm_medium=link&utm_campaign=pop-up-premium')
                ),
                'step_name'   => 'wp-statistics-real-time',
                'step_href'   => esc_url(WP_STATISTICS_SITE_URL . '/product/wp-statistics-realtime-stats/?utm_source=wp-statistics&utm_medium=link&utm_campaign=pop-up-premium')
            ];
            View::load("components/modals/premium/step-content", $data);

            $data = [
                'description' => sprintf(__('<p>Display vital site stats using customizable Gutenberg blocks or theme widgets. Enhance your audience\'s experience with flexible, real-time data presentations.<a target="_blank" href="%s">Learn more</a></p>', 'wp-statistics'),
                    esc_url(WP_STATISTICS_SITE_URL . '/product/wp-statistics-widgets/?utm_source=wp-statistics&utm_medium=link&utm_campaign=pop-up-premium')
                ),
                'step_name'   => 'wp-statistics-widgets',
                'step_href'   => esc_url(WP_STATISTICS_SITE_URL . '/product/wp-statistics-widgets/?utm_source=wp-statistics&utm_medium=link&utm_campaign=pop-up-premium')
            ];
            View::load("components/modals/premium/step-content", $data);

            $data = [
                'description' => sprintf(__('<p>Manage admin menus, modify plugin headers, and create white-label products. Enhance the Overview page with fully customized widgets tailored to your needs.<a target="_blank" href="%s">Learn more</a></p>', 'wp-statistics'),
                    esc_url(WP_STATISTICS_SITE_URL . '/product/wp-statistics-customization/?utm_source=wp-statistics&utm_medium=link&utm_campaign=pop-up-premium')
                ),
                'step_name'   => 'wp-statistics-customization',
                'step_href'   => esc_url(WP_STATISTICS_SITE_URL . '/product/wp-statistics-customization/?utm_source=wp-statistics&utm_medium=link&utm_campaign=pop-up-premium')
            ];
            View::load("components/modals/premium/step-content", $data);

            $data = [
                'description' => sprintf(__('<p>Unlock new endpoints in the WordPress REST API for detailed insights, including browsers, referrers, visitors, and more. Easily access and integrate key statistics. <a target="_blank" href="%s">Learn more</a></p>', 'wp-statistics'),
                    esc_url(WP_STATISTICS_SITE_URL . '/product/wp-statistics-rest-api/?utm_source=wp-statistics&utm_medium=link&utm_campaign=pop-up-premium')
                ),
                'step_name'   => 'wp-statistics-rest-api',
                'step_href'   => esc_url(WP_STATISTICS_SITE_URL . '/product/wp-statistics-rest-api/?utm_source=wp-statistics&utm_medium=link&utm_campaign=pop-up-premium')
            ];
            View::load("components/modals/premium/step-content", $data);
            ?>
        </div>
        <div class="wps-premium-step__sidebar">
            <div>
                <p><?php esc_html_e('WP Statistics Premium Include:', 'wp-statistics'); ?></p>
                <ul class="wps-premium-step__features-list">
                    <li class="<?php echo PluginHelper::isPluginPurchased('wp-statistics-data-plus') ? 'activated' : '' ?> wps-premium-step__feature js-wps-premiumStepFeature" data-modal="wp-statistics-data-plus"><?php esc_html_e('Data Plus', 'wp-statistics'); ?></li>
                    <li class="<?php echo PluginHelper::isPluginPurchased('wp-statistics-mini-chart') ? 'activated' : '' ?> wps-premium-step__feature js-wps-premiumStepFeature" data-modal="wp-statistics-mini-chart"><?php esc_html_e('Mini Chart', 'wp-statistics'); ?></li>
                    <li class="<?php echo PluginHelper::isPluginPurchased('wp-statistics-advanced-reporting') ? 'activated' : '' ?> wps-premium-step__feature js-wps-premiumStepFeature" data-modal="wp-statistics-advanced-reporting"><?php esc_html_e('Advanced Reporting', 'wp-statistics'); ?></li>
                    <li class="<?php echo PluginHelper::isPluginPurchased('wp-statistics-realtime-stats') ? 'activated' : '' ?> wps-premium-step__feature js-wps-premiumStepFeature" data-modal="wp-statistics-real-time"><?php esc_html_e('Real-Time Stats', 'wp-statistics'); ?></li>
                    <li class="<?php echo PluginHelper::isPluginPurchased('wp-statistics-widgets') ? 'activated' : '' ?> wps-premium-step__feature js-wps-premiumStepFeature" data-modal="wp-statistics-widgets"><?php esc_html_e('Widgets', 'wp-statistics'); ?></li>
                    <li class="<?php echo PluginHelper::isPluginPurchased('wp-statistics-customization') ? 'activated' : '' ?> wps-premium-step__feature js-wps-premiumStepFeature" data-modal="wp-statistics-customization"><?php esc_html_e('Customization', 'wp-statistics'); ?></li>
                    <li class="<?php echo PluginHelper::isPluginPurchased('wp-statistics-rest-api') ? 'activated' : '' ?> wps-premium-step__feature js-wps-premiumStepFeature" data-modal="wp-statistics-rest-api"><?php esc_html_e('REST API', 'wp-statistics'); ?></li>
                </ul>
            </div>
            <div class="wps-premium-step__actions">
                <?php if (LicenseHelper::isPremiumLicenseAvailable()) : ?>
                    <a target="_blank" class="wps-premium-step__action-btn wps-premium-step__action-btn--upgrade activated js-wps-premiumModalUpgradeBtn"><?php esc_html_e('Premium Activated', 'wp-statistics'); ?></a>
                <?php elseif (LicenseHelper::isLicenseAvailable() && !LicenseHelper::isPremiumLicenseAvailable()) : ?>
                    <a target="_blank" class="wps-premium-step__action-btn wps-premium-step__action-btn--upgrade js-wps-premiumModalUpgradeBtn"><?php esc_html_e('Upgrade to Premium', 'wp-statistics'); ?></a>
                <?php else : ?>
                    <a target="_blank" class="wps-premium-step__action-btn wps-premium-step__action-btn--upgrade js-wps-premiumModalUpgradeBtn"><?php esc_html_e('Upgrade Now', 'wp-statistics'); ?></a>
                <?php endif; ?>

                <?php if (!LicenseHelper::isPremiumLicenseAvailable()) : ?>
                <a class="wps-premium-step__action-btn wps-premium-step__action-btn--later js-wps-premiumModalClose"><?php esc_html_e('Maybe Later', 'wp-statistics'); ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>