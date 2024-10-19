<?php

use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHelper;

?>
<div class="wps-premium-step">
    <div class="wps-premium-step__header">
        <span class="wps-premium-step__skip js-wps-premiumModalClose"></span>
        <span><?php esc_html_e('WP Statistics Premium', 'wp-statistics'); ?></span>
        <p><?php esc_html_e('Try the upgrade. See more. Do more.', 'wp-statistics'); ?></p>
    </div>
    <div class="wps-premium-step__body">
        <div class="wps-premium-step__content">
            <?php
            $data = [
                'description' => __('<p><b>Say hello to WP Statistics Premium!</b> We’ve combined all your favorite features into one powerful package—no need for separate tools anymore.</p>
                <p><b>With Premium, you get everything in one place:</b> real-time stats, advanced reports, and full customization to track and display your data just the way you want.</p>
                <p>Ready to level up your site? <b>WP Statistics Premium</b> gives you the insights and flexibility you need to make smarter decisions with ease.</p>', 'wp-statistics'),
                'step_name'   => 'first-step',
                'step_href'   => esc_url(WP_STATISTICS_SITE_URL . '/product/add-ons-bundle/?utm_source=wp-statistics&utm_medium=link&utm_campaign=pop-up-premium')
            ];
            View::load("components/premium-pop-up/step-content", $data);

            $data = [
                'description' => sprintf(__('<p>Elevate your analytics with custom post tracking, detailed visitor behavior insights, and advanced filtering. Gain deeper understanding with content-specific widgets and traffic analysis tools.<a href="%s">Learn more</a></p>', 'wp-statistics'), 'url'),
                'step_name'   => 'wp-statistics-data-plus',
                'step_href'   => esc_url(WP_STATISTICS_SITE_URL . '/product/wp-statistics-data-plus/?utm_source=wp-statistics&utm_medium=link&utm_campaign=pop-up-premium')
            ];
            View::load("components/premium-pop-up/step-content", $data);

            $data = [
                'description' => sprintf(__('<p>Instantly view customizable performance charts for all posts and pages, with quick access to traffic data via the admin bar. Gain insights at a glance.<a href="%s">Learn more</a></p>', 'wp-statistics'), 'url'),
                'step_name'   => 'wp-statistics-mini-chart',
                'step_href'   => esc_url(WP_STATISTICS_SITE_URL . '/product/wp-statistics-mini-chart/?utm_source=wp-statistics&utm_medium=link&utm_campaign=pop-up-premium')
            ];
            View::load("components/premium-pop-up/step-content", $data);

            $data = [
                'description' => sprintf(__('<p>Receive scheduled, customizable traffic reports with detailed charts straight to your inbox. Stay informed about your website\'s performance effortlessly. <a href="%s">Learn more</a></p>', 'wp-statistics'), 'url'),
                'step_name'   => 'wp-statistics-advanced-reporting',
                'step_href'   => esc_url(WP_STATISTICS_SITE_URL . '/product/wp-statistics-advanced-reporting/?utm_source=wp-statistics&utm_medium=link&utm_campaign=pop-up-premium')
            ];
            View::load("components/premium-pop-up/step-content", $data);

            $data = [
                'description' => sprintf(__('<p>Monitor visitor activity and online users live, without refreshing the page. Stay updated on your website\'s performance in real-time. <a href="%s">Learn more</a></p>', 'wp-statistics'), 'url'),
                'step_name'   => 'wp-statistics-real-time',
                'step_href'   => esc_url(WP_STATISTICS_SITE_URL . '/product/wp-statistics-realtime-stats/?utm_source=wp-statistics&utm_medium=link&utm_campaign=pop-up-premium')
            ];
            View::load("components/premium-pop-up/step-content", $data);

            $data = [
                'description' => sprintf(__('<p>Display vital site stats using customizable Gutenberg blocks or theme widgets. Enhance your audience\'s experience with flexible, real-time data presentations.<a href="%s">Learn more</a></p>', 'wp-statistics'), 'url'),
                'step_name'   => 'wp-statistics-widgets',
                'step_href'   => esc_url(WP_STATISTICS_SITE_URL . '/product/wp-statistics-widgets/?utm_source=wp-statistics&utm_medium=link&utm_campaign=pop-up-premium')
            ];
            View::load("components/premium-pop-up/step-content", $data);

            $data = [
                'description' => sprintf(__('<p>Manage admin menus, modify plugin headers, and create white-label products. Enhance the Overview page with fully customized widgets tailored to your needs.<a href="%s">Learn more</a></p>', 'wp-statistics'), 'url'),
                'step_name'   => 'wp-statistics-customization',
                'step_href'   => esc_url(WP_STATISTICS_SITE_URL . '/product/wp-statistics-customization/?utm_source=wp-statistics&utm_medium=link&utm_campaign=pop-up-premium')
            ];
            View::load("components/premium-pop-up/step-content", $data);

            $data = [
                'description' => sprintf(__('<p>Unlock new endpoints in the WordPress REST API for detailed insights, including browsers, referrers, visitors, and more. Easily access and integrate key statistics. <a href="%s">Learn more</a></p>', 'wp-statistics'), 'url'),
                'step_name'   => 'wp-statistics-rest-api',
                'step_href'   => esc_url(WP_STATISTICS_SITE_URL . '/product/wp-statistics-rest-api/?utm_source=wp-statistics&utm_medium=link&utm_campaign=pop-up-premium')
            ];
            View::load("components/premium-pop-up/step-content", $data);
            ?>
        </div>
        <div class="wps-premium-step__sidebar">
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
            <div class="wps-premium-step__actions">
                <a target="_blank" class="wps-premium-step__action-btn wps-premium-step__action-btn--upgrade js-wps-premiumModalUpgradeBtn"><?php esc_html_e('Upgrade Now', 'wp-statistics'); ?></a>
                <a class="wps-premium-step__action-btn wps-premium-step__action-btn--later js-wps-premiumModalClose"><?php esc_html_e('Maybe Later', 'wp-statistics'); ?></a>
            </div>
        </div>
    </div>
</div>
