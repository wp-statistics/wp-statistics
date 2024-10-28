<?php

use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHandler;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHelper;

$pluginHandler = new PluginHandler();
?>
<div class="wps-premium-step">
    <div class="wps-premium-step__header">
        <span class="wps-premium-step__skip js-wps-premiumModalClose"></span>
        <span><?php esc_html_e('WP Statistics Premium', 'wp-statistics'); ?></span>
        <div class="js-wps-premium-first-step__head">
            <?php if (LicenseHelper::isPremiumLicenseAvailable()) : ?>
                <p><?php esc_html_e('You\'re All Set with WP Statistics Premium', 'wp-statistics'); ?></p>
            <?php elseif (LicenseHelper::isValidLicenseAvailable() && !LicenseHelper::isPremiumLicenseAvailable()) : ?>
                <p><?php esc_html_e('You\'re Already Enjoying Some Premium Add-Ons!', 'wp-statistics'); ?></p>
            <?php else : ?>
                <p><?php esc_html_e('Unlock WP Statistics Premium', 'wp-statistics'); ?></p>
            <?php endif; ?>
        </div>
        <div class="js-wps-premium-steps__head">
            <?php if (LicenseHelper::isPremiumLicenseAvailable()) : ?>
                <p><?php esc_html_e('You Have the Premium Version!', 'wp-statistics'); ?></p>
            <?php else : ?>
                <p><?php esc_html_e('Try the upgrade. See more. Do more.', 'wp-statistics'); ?></p>
            <?php endif; ?>

        </div>
    </div>
    <div class="wps-premium-step__body">
        <div class="wps-premium-step__content">
            <?php
            $default_description = __('<p>Get access to all advanced features in one package. With WP Statistics Premium, you can explore powerful add-ons that enhance your analytics experience and provide deeper insights into your site’s performance.</p>
            <p>Curious about what each feature offers?</p><p>Simply click on any add-on to learn more and see how WP Statistics Premium can power your site’s growth.</p>', 'wp-statistics');

            $premium_description = __('<p><b>Thank you for supporting us by being a Premium user! Since you previously had the Bundle, you now have access to all the Premium features. There’s nothing more you need to do—just enjoy the full range of advanced tools and insights.</b></p>
            <p>We truly appreciate your continued support. With your help, we’re able to keep improving and providing even better analytics for your site.</p>', 'wp-statistics');

            $license_description = __('<p>It looks like you’ve unlocked some of our great add-ons. Awesome! To get the most out of WP Statistics, upgrade to Premium and get access to all our advanced features and add-ons. Unlock deeper insights and powerful analytics with the full package at your fingertips.</p>', 'wp-statistics');

            $data = [
                'description' => $default_description,
                'step_name'   => 'first-step',
                'step_href'   => esc_url(WP_STATISTICS_SITE_URL . '/product/add-ons-bundle/?utm_source=wp-statistics&utm_medium=link&utm_campaign=pop-up-premium')
            ];

            if (LicenseHelper::isPremiumLicenseAvailable()) {
                $data['description'] = $premium_description;
            } elseif (LicenseHelper::isValidLicenseAvailable() && !LicenseHelper::isPremiumLicenseAvailable()) {
                $data['description'] = $license_description;
            }

            View::load("components/modals/introduce-premium/step-content", $data);


            $data = [
                'description' => sprintf(
                    __('<p>Elevate your analytics with custom post tracking, detailed visitor behavior insights, and advanced filtering. Gain deeper understanding with content-specific widgets and traffic analysis tools. <a target="_blank" href="%s">Learn more</a></p>', 'wp-statistics'),
                    esc_url(WP_STATISTICS_SITE_URL . '/product/wp-statistics-data-plus/?utm_source=wp-statistics&utm_medium=link&utm_campaign=pop-up-premium')
                ),
                'step_name'   => 'wp-statistics-data-plus',
                'step_title'  => esc_html__('Data Plus', 'wp-statistics'),
            ];
            View::load("components/modals/introduce-premium/step-content", $data);

            $data = [
                'description' => sprintf(__('<p>Instantly view customizable performance charts for all posts and pages, with quick access to traffic data via the admin bar. Gain insights at a glance.<a target="_blank" href="%s">Learn more</a></p>', 'wp-statistics'),
                    esc_url(WP_STATISTICS_SITE_URL . '/product/wp-statistics-mini-chart/?utm_source=wp-statistics&utm_medium=link&utm_campaign=pop-up-premium')
                ),
                'step_name'   => 'wp-statistics-mini-chart',
                'step_title'  => esc_html__('Mini Chart', 'wp-statistics'),
            ];
            View::load("components/modals/introduce-premium/step-content", $data);

            $data = [
                'description' => sprintf(__('<p>Receive scheduled, customizable traffic reports with detailed charts straight to your inbox. Stay informed about your website\'s performance effortlessly. <a target="_blank" href="%s">Learn more</a></p>', 'wp-statistics'),
                    esc_url(WP_STATISTICS_SITE_URL . '/product/wp-statistics-advanced-reporting/?utm_source=wp-statistics&utm_medium=link&utm_campaign=pop-up-premium')
                ),
                'step_name'   => 'wp-statistics-advanced-reporting',
                'step_title'  => esc_html__('Advanced Reporting', 'wp-statistics'),
            ];
            View::load("components/modals/introduce-premium/step-content", $data);

            $data = [
                'description' => sprintf(__('<p>Monitor visitor activity and online users live, without refreshing the page. Stay updated on your website\'s performance in real-time. <a target="_blank" href="%s">Learn more</a></p>', 'wp-statistics'),
                    esc_url(WP_STATISTICS_SITE_URL . '/product/wp-statistics-realtime-stats/?utm_source=wp-statistics&utm_medium=link&utm_campaign=pop-up-premium')
                ),
                'step_name'   => 'wp-statistics-realtime-stats',
                'step_title'  => esc_html__('Real-Time Stats', 'wp-statistics'),
            ];
            View::load("components/modals/introduce-premium/step-content", $data);

            $data = [
                'description' => sprintf(__('<p>Display vital site stats using customizable Gutenberg blocks or theme widgets. Enhance your audience\'s experience with flexible, real-time data presentations.<a target="_blank" href="%s">Learn more</a></p>', 'wp-statistics'),
                    esc_url(WP_STATISTICS_SITE_URL . '/product/wp-statistics-widgets/?utm_source=wp-statistics&utm_medium=link&utm_campaign=pop-up-premium')
                ),
                'step_name'   => 'wp-statistics-widgets',
                'step_title'  => esc_html__('Widgets', 'wp-statistics'),
            ];
            View::load("components/modals/introduce-premium/step-content", $data);

            $data = [
                'description' => sprintf(__('<p>Manage admin menus, modify plugin headers, and create white-label products. Enhance the Overview page with fully customized widgets tailored to your needs.<a target="_blank" href="%s">Learn more</a></p>', 'wp-statistics'),
                    esc_url(WP_STATISTICS_SITE_URL . '/product/wp-statistics-customization/?utm_source=wp-statistics&utm_medium=link&utm_campaign=pop-up-premium')
                ),
                'step_name'   => 'wp-statistics-customization',
                'step_title'  => esc_html__('Customization', 'wp-statistics'),
            ];
            View::load("components/modals/introduce-premium/step-content", $data);

            $data = [
                'description' => sprintf(__('<p>Unlock new endpoints in the WordPress REST API for detailed insights, including browsers, referrers, visitors, and more. Easily access and integrate key statistics. <a target="_blank" href="%s">Learn more</a></p>', 'wp-statistics'),
                    esc_url(WP_STATISTICS_SITE_URL . '/product/wp-statistics-rest-api/?utm_source=wp-statistics&utm_medium=link&utm_campaign=pop-up-premium')
                ),
                'step_name'   => 'wp-statistics-rest-api',
                'step_title'  => esc_html__('REST API', 'wp-statistics'),
            ];
            View::load("components/modals/introduce-premium/step-content", $data);
            ?>
        </div>
        <div class="wps-premium-step__sidebar">
            <div>
                <p><?php esc_html_e('WP Statistics Premium Include', 'wp-statistics'); ?>:</p>
                <ul class="wps-premium-step__features-list">
                    <?php
                    $plugins = [
                        'wp-statistics-data-plus'          => esc_html__('Data Plus', 'wp-statistics'),
                        'wp-statistics-mini-chart'         => esc_html__('Mini Chart', 'wp-statistics'),
                        'wp-statistics-advanced-reporting' => esc_html__('Advanced Reporting', 'wp-statistics'),
                        'wp-statistics-realtime-stats'     => esc_html__('Real-Time Stats', 'wp-statistics'),
                        'wp-statistics-widgets'            => esc_html__('Widgets', 'wp-statistics'),
                        'wp-statistics-customization'      => esc_html__('Customization', 'wp-statistics'),
                        'wp-statistics-rest-api'           => esc_html__('REST API', 'wp-statistics'),
                    ];
                    ?>

                    <?php foreach ($plugins as $slug => $title) : ?>
                        <li class="<?php echo LicenseHelper::isPluginLicenseValid($slug) ? 'activated' : '' ?> wps-premium-step__feature js-wps-premiumStepFeature" data-modal="<?php echo esc_attr($slug) ?>">
                            <?php echo esc_html($title); ?>

                            <?php if (!$pluginHandler->isPluginInstalled($slug)) : ?>
                                <span class="wps-premium-step__feature-badge"><?php esc_html_e('Not Installed', 'wp-statistics'); ?></span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="wps-premium-step__actions">
                <div class="js-wps-premium-first-step__head">
                    <?php if (LicenseHelper::isPremiumLicenseAvailable()) : ?>
                        <a class="wps-premium-step__action-btn wps-premium-step__action-btn--upgrade activated js-wps-premiumModalUpgradeBtn"><?php esc_html_e('Premium Activated', 'wp-statistics'); ?></a>
                    <?php elseif (LicenseHelper::isValidLicenseAvailable() && !LicenseHelper::isPremiumLicenseAvailable()) : ?>
                        <a target="_blank" class="wps-premium-step__action-btn wps-premium-step__action-btn--upgrade js-wps-premiumModalUpgradeBtn"><?php esc_html_e('Upgrade to Premium', 'wp-statistics'); ?></a>
                    <?php else : ?>
                        <a target="_blank" class="wps-premium-step__action-btn wps-premium-step__action-btn--upgrade js-wps-premiumModalUpgradeBtn"><?php esc_html_e('Upgrade Now', 'wp-statistics'); ?></a>
                    <?php endif; ?>
                    <?php if (!LicenseHelper::isPremiumLicenseAvailable()) : ?>
                        <a class="wps-premium-step__action-btn wps-premium-step__action-btn--later js-wps-premiumModalClose"><?php esc_html_e('Maybe Later', 'wp-statistics'); ?></a>
                    <?php endif; ?>
                </div>
                <div class="js-wps-premium-steps__head js-wps-premium-steps__side-buttons">
                    <?php foreach ($plugins as $slug => $title) : ?>
                        <?php if (LicenseHelper::isPremiumLicenseAvailable()) : ?>
                            <?php if (!$pluginHandler->isPluginInstalled($slug)) : ?>
                                <a  href="<?php echo esc_url(admin_url('admin.php?page=wps_plugins_page')) ?>" class="wps-premium-step__action-btn js-wps-premiumModalUpgradeBtn wps-premium-step__action-btn--addons"><?php esc_html_e('Go to Add-Ons Page', 'wp-statistics'); ?></a>
                            <?php else : ?>
                                <a class="wps-premium-step__action-btn wps-premium-step__action-btn--upgrade  activated js-wps-premiumModalUpgradeBtn"><?php esc_html_e('Premium Activated', 'wp-statistics'); ?></a>
                            <?php endif; ?>
                        <?php else : ?>
                            <a href="<?php echo esc_url(WP_STATISTICS_SITE_URL . '/product/' . $slug . '/?utm_source=wp-statistics&utm_medium=link&utm_campaign=pop-up-premium') ?>" target="_blank" class="wps-premium-step__action-btn wps-premium-step__action-btn--upgrade js-wps-premiumModalUpgradeBtn"><?php esc_html_e('Upgrade to Premium', 'wp-statistics'); ?></a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if (!LicenseHelper::isPremiumLicenseAvailable()) : ?>
                        <a class="wps-premium-step__action-btn wps-premium-step__action-btn--later js-wps-premiumModalClose"><?php esc_html_e('Maybe Later', 'wp-statistics'); ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>