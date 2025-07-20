<?php

use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHandler;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHelper;

$pluginHandler = new PluginHandler();

$installedPlugins = $pluginHandler->getInstalledPlugins();
$hasLicense       = LicenseHelper::isValidLicenseAvailable();
$isPremium        = LicenseHelper::isPremiumLicenseAvailable();
?>
<div class="wps-premium-step">
    <div class="wps-premium-step__header">
        <span class="wps-premium-step__skip js-wps-premiumModalClose"></span>
        <span><?php esc_html_e('WP Statistics Premium', 'wp-statistics'); ?></span>
        <div class="wps-premium-step__title js-wps-dynamic-title" id="dynamic-title"></div>
    </div>
    <div class="wps-premium-step__body">
        <div class="wps-premium-step__content">
            <?php
            $defaultDescription = __('<p>Get access to all advanced features in one package. With WP Statistics Premium, you can explore powerful add-ons that enhance your analytics experience and provide deeper insights into your site’s performance.</p>
            <p>Curious about what each feature offers?</p><p>Simply click on any add-on to learn more and see how WP Statistics Premium can power your site’s growth.</p>', 'wp-statistics');

            $premiumDescription = __('<p><b>Thank you for supporting us by being a Premium user! Since you previously had the Bundle, you now have access to all the Premium features. There’s nothing more you need to do—just enjoy the full range of advanced tools and insights.</b></p>
            <p>We truly appreciate your continued support. With your help, we’re able to keep improving and providing even better analytics for your site.</p>', 'wp-statistics');

            $licenseDescription = __('<p>It looks like you’ve unlocked some of our great add-ons. Awesome! To get the most out of WP Statistics, upgrade to Premium and get access to all our advanced features and add-ons. Unlock deeper insights and powerful analytics with the full package at your fingertips.</p>', 'wp-statistics');

            $data = [
                'description' => $defaultDescription,
                'step_name'   => 'first-step',
                'step_title'  => $isPremium ? esc_html__('You\'re All Set with WP Statistics Premium', 'wp-statistics') : ($hasLicense && !$isPremium ? esc_html__('You\'re Already Enjoying Some Premium Add-Ons!', 'wp-statistics') : esc_html__('Unlock WP Statistics Premium.', 'wp-statistics')),
                'step_href'   => esc_url(WP_STATISTICS_SITE_URL . '/pricing/?utm_source=wp-statistics&utm_medium=link&utm_campaign=pop-up-premium')
            ];

            if ($isPremium) {
                $data['description'] = $premiumDescription;
            } elseif ($hasLicense && !$isPremium) {
                $data['description'] = $licenseDescription;
            }

            View::load("components/modals/introduce-premium/step-content", $data);


            $data = [
                'description' => sprintf(
                    __('<p>Data Plus supercharges WP Statistics by unlocking advanced analytics features. Get deeper insights into your site\'s performance with tools that help you understand visitor behavior, optimize your content, and track engagement like never before. <a target="_blank" href="%s">Learn more</a></p>', 'wp-statistics'),
                    esc_url(WP_STATISTICS_SITE_URL . '/add-ons/wp-statistics-data-plus/?utm_source=wp-statistics&utm_medium=link&utm_campaign=pop-up-premium')
                ),
                'step_name'   => 'wp-statistics-data-plus',
                'step'        => 'Data Plus',
                'step_title'  => esc_html__('See more data.', 'wp-statistics'),
            ];
            View::load("components/modals/introduce-premium/step-content", $data);

            $data = [
                'description' => sprintf(
                    __('<p>Take your marketing strategy to the next level with the Marketing Add-on. Track your campaign performance with detailed UTM reports, connect to Google Search Console for in-depth traffic analysis, and set conversion goals to measure success—all directly within WP Statistics. Optimize your campaigns, monitor search traffic, and drive results effortlessly. <a target="_blank" href="%s">Learn more</a></p>', 'wp-statistics'),
                    esc_url(WP_STATISTICS_SITE_URL . '/add-ons/wp-statistics-marketing/?utm_source=wp-statistics&utm_medium=link&utm_campaign=pop-up-premium')
                ),
                'step_name'   => 'wp-statistics-marketing',
                'step'        => 'Marketing',
                'step_title'  => esc_html__('Set goals, measure conversions, and optimize your reach for real results.', 'wp-statistics'),
            ];
            View::load("components/modals/introduce-premium/step-content", $data);

            $data = [
                'description' => sprintf(
                    __('<p>Mini Chart gives you quick insights into how your posts, pages, and products are performing by displaying small, customizable charts directly in your admin panel. Track views and engagement at a glance and customize chart types and colors to match your preferences. Analyze content performance easily and stay on top of key metrics with minimal time. <a target="_blank" href="%s">Learn more</a></p>', 'wp-statistics'),
                    esc_url(WP_STATISTICS_SITE_URL . '/add-ons/wp-statistics-mini-chart/?utm_source=wp-statistics&utm_medium=link&utm_campaign=pop-up-premium')
                ),
                'step_name'   => 'wp-statistics-mini-chart',
                'step'        => 'Mini Chart',
                'step_title'  => esc_html__('View real‐time metrics at a glance.', 'wp-statistics'),
            ];
            View::load("components/modals/introduce-premium/step-content", $data);

            $data = [
                'description' => sprintf(
                    __('<p>Advanced Reporting keeps you informed by sending detailed performance reports directly to your inbox. Gain insights into your website’s traffic, views, and key metrics through customizable email reports. Schedule updates to receive data as often as you like, ensuring you always stay up-to-date on your website\'s performance with clear, graphical insights. <a target="_blank" href="%s">Learn more</a></p>', 'wp-statistics'),
                    esc_url(WP_STATISTICS_SITE_URL . '/add-ons/wp-statistics-advanced-reporting/?utm_source=wp-statistics&utm_medium=link&utm_campaign=pop-up-premium')
                ),
                'step_name'   => 'wp-statistics-advanced-reporting',
                'step'        => 'Advanced Reporting',
                'step_title'  => esc_html__('Schedule detailed email reports.', 'wp-statistics'),
            ];
            View::load("components/modals/introduce-premium/step-content", $data);

            $data = [
                'description' => sprintf(
                    __('<p>You can monitor your website\'s traffic in real-time with the Real-Time add-on. Watch live data flow in, track online users, and analyze their activity without page refreshes. Stay connected with real-time insights to make informed decisions about your site’s performance, right when it matters most. <a target="_blank" href="%s">Learn more</a></p>', 'wp-statistics'),
                    esc_url(WP_STATISTICS_SITE_URL . '/add-ons/wp-statistics-realtime-stats/?utm_source=wp-statistics&utm_medium=link&utm_campaign=pop-up-premium')
                ),
                'step_name'   => 'wp-statistics-realtime-stats',
                'step'        => 'Real-Time Stats',
                'step_title'  => esc_html__('See who’s on your site right now and watch engagement as it happens.', 'wp-statistics'),
            ];
            View::load("components/modals/introduce-premium/step-content", $data);

            $data = [
                'description' => sprintf(
                    __('<p>Advanced Widgets improve your website by providing a flexible way to showcase key statistical insights. Whether through Gutenberg blocks or theme widgets, this add-on makes it easy to present vital stats like traffic, top pages, and browsers to your visitors, offering a richer, data-driven user experience. <a target="_blank" href="%s">Learn more</a></p>', 'wp-statistics'),
                    esc_url(WP_STATISTICS_SITE_URL . '/add-ons/wp-statistics-widgets/?utm_source=wp-statistics&utm_medium=link&utm_campaign=pop-up-premium')
                ),
                'step_name'   => 'wp-statistics-widgets',
                'step'        => 'Widgets',
                'step_title'  => esc_html__('Display detailed stats anywhere you want on your website.', 'wp-statistics'),
            ];
            View::load("components/modals/introduce-premium/step-content", $data);

            $data = [
                'description' => sprintf(
                    __('<p>The Customization add-on is designed to help you white-label key areas of your dashboard. Easily replace the WP Statistics branding with your own, giving you full control over how the plugin looks and feels. Create a fully branded experience by customizing menus, headers, and even the Overview page with your own widgets. <a target="_blank" href="%s">Learn more</a></p>', 'wp-statistics'),
                    esc_url(WP_STATISTICS_SITE_URL . '/add-ons/wp-statistics-customization/?utm_source=wp-statistics&utm_medium=link&utm_campaign=pop-up-premium')
                ),
                'step_name'   => 'wp-statistics-customization',
                'step'        => 'Customization',
                'step_title'  => esc_html__('Tailor everything from layout to branding to suit your style.', 'wp-statistics'),
            ];
            View::load("components/modals/introduce-premium/step-content", $data);

            $data = [
                'description' => sprintf(
                    __('<p>Unlock powerful integration options with the Rest API add-on. This feature enables access to your website’s statistics through WordPress REST API endpoints, allowing seamless data retrieval for external applications, custom dashboards, and automation tools. <a target="_blank" href="%s">Learn more</a></p>', 'wp-statistics'),
                    esc_url(WP_STATISTICS_SITE_URL . '/add-ons/wp-statistics-rest-api/?utm_source=wp-statistics&utm_medium=link&utm_campaign=pop-up-premium')
                ),
                'step_name'   => 'wp-statistics-rest-api',
                'step'        => 'REST API',
                'step_title'  => esc_html__('Connect your site’s stats anywhere.', 'wp-statistics'),
            ];
            View::load("components/modals/introduce-premium/step-content", $data);
            ?>
        </div>
        <div class="wps-premium-step__sidebar">
            <div>
                <p><?php esc_html_e('WP Statistics Premium Includes', 'wp-statistics'); ?>:</p>
                <ul class="wps-premium-step__features-list">
                    <?php foreach (PluginHelper::$plugins as $slug => $title) :
                        $class = '';

                        $isActive    = $pluginHandler->isPluginActive($slug);
                        $isInstalled = $pluginHandler->isPluginInstalled($slug);
                        $hasLicense  = LicenseHelper::isPluginLicenseValid($slug);

                        if ($hasLicense && $isActive) {
                            $class = 'activated';
                        } elseif ($hasLicense && $isInstalled && !$isActive) {
                            $class = 'not-active';
                        } elseif (!$hasLicense && ($isInstalled || $isActive)) {
                            $class = 'no-license';
                        }
                        ?>
                        <li class="<?php echo esc_attr($class); ?> wps-premium-step__feature js-wps-premiumStepFeature" data-modal="<?php echo esc_attr($slug) ?>">
                            <?php echo esc_html($title); ?>

                            <?php if ($hasLicense && !$isInstalled) : ?>
                                <span class="wps-premium-step__feature-badge"><?php esc_html_e('Not Installed', 'wp-statistics'); ?></span>
                            <?php elseif ($hasLicense && !$isActive) : ?>
                                <span class="wps-premium-step__feature-badge"><?php esc_html_e('Not activated', 'wp-statistics'); ?></span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="wps-premium-step__actions">
                <div class="js-wps-premium-first-step__head">
                    <p><?php esc_html_e('WP Statistics Premium Includes', 'wp-statistics'); ?>:</p>
                    <?php if ($isPremium) : ?>
                        <a class="wps-premium-step__action-btn wps-premium-step__action-btn--upgrade activated js-wps-premiumModalUpgradeBtn"><?php esc_html_e('Premium Activated', 'wp-statistics'); ?></a>
                    <?php elseif ($hasLicense && !$isPremium) : ?>
                        <a target="_blank" href="<?php echo esc_url(WP_STATISTICS_SITE_URL . '/pricing/?utm_source=wp-statistics&utm_medium=link&utm_campaign=pop-up-premium') ?>" class="wps-premium-step__action-btn wps-premium-step__action-btn--upgrade js-wps-premiumModalUpgradeBtn"><?php esc_html_e('Upgrade to Premium', 'wp-statistics'); ?></a>
                    <?php else : ?>
                        <a target="_blank" href="<?php echo esc_url(WP_STATISTICS_SITE_URL . '/pricing/?utm_source=wp-statistics&utm_medium=link&utm_campaign=pop-up-premium') ?>" class="wps-premium-step__action-btn wps-premium-step__action-btn--upgrade js-wps-premiumModalUpgradeBtn"><?php esc_html_e('Upgrade Now', 'wp-statistics'); ?></a>
                    <?php endif; ?>

                    <?php if (!$isPremium) : ?>
                        <a class="wps-premium-step__action-btn wps-premium-step__action-btn--later js-wps-premiumModalClose"><?php esc_html_e('Maybe Later', 'wp-statistics'); ?></a>
                    <?php endif; ?>
                </div>
                <div class="js-wps-premium-steps__head js-wps-premium-steps__side-buttons">
                    <?php foreach (PluginHelper::$plugins as $slug => $title) :
                        $isActive = $pluginHandler->isPluginActive($slug);
                        $isInstalled = $pluginHandler->isPluginInstalled($slug);
                        $hasLicense = LicenseHelper::isPluginLicenseValid($slug);
                        ?>

                        <div class="wps-premium-step__action-container">
                            <?php if (!$hasLicense && !$isInstalled) : ?>
                                <a href="<?php echo esc_url(WP_STATISTICS_SITE_URL . '/pricing/?utm_source=wp-statistics&utm_medium=link&utm_campaign=pop-up-premium') ?>" target="_blank" class="wps-premium-step__action-btn wps-premium-step__action-btn--upgrade js-wps-premiumModalUpgradeBtn"><?php esc_html_e('Upgrade to Premium', 'wp-statistics'); ?></a>
                                <a class="wps-premium-step__action-btn wps-premium-step__action-btn--later js-wps-premiumModalClose"><?php esc_html_e('Maybe Later', 'wp-statistics'); ?></a>
                            <?php elseif (($hasLicense && !$isActive) || (!$hasLicense && $isInstalled)) : ?>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=wps_plugins_page')) ?>" class="wps-premium-step__action-btn js-wps-premiumModalUpgradeBtn wps-premium-step__action-btn--addons"><?php esc_html_e('Go to Add-ons Page', 'wp-statistics'); ?></a>
                            <?php elseif ($hasLicense && $isActive) : ?>
                                <a class="wps-premium-step__action-btn wps-premium-step__action-btn--upgrade  activated js-wps-premiumModalUpgradeBtn"><?php esc_html_e('Add-on Activated', 'wp-statistics'); ?></a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>