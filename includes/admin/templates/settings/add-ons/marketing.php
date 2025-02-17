<?php

use WP_STATISTICS\Admin_Template;
use WP_Statistics\Components\View;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Option;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;

$isLicenseValid     = LicenseHelper::isPluginLicenseValid('marketing');
$isMarketingActive  = Helper::isAddOnActive('marketing');

if (!$isMarketingActive) {
    echo Admin_Template::get_template(
        'layout/partials/addon-premium-feature',
        [
            'addon_slug'         => esc_url(WP_STATISTICS_SITE_URL . '/add-ons/wp-statistics-marketing/?utm_source=wp-statistics&utm_medium=link&utm_campaign=plugin-settings'),
            'addon_title'        => __('Marketing Add-On', 'wp-statistics'),
            'addon_modal_target' => 'wp-statistics-marketing',
            'addon_description'  => __('The settings on this page are part of the Marketing add-on, which enhances WP Statistics by expanding tracking capabilities and providing detailed visitor insights.', 'wp-statistics'),
            // 'addon_features'     => [
            //     __('Track custom post types and taxonomies.', 'wp-statistics'),
            //     __('Use advanced filtering for specific query parameters and UTM tags.', 'wp-statistics'),
            //     __('Monitor outbound link clicks and downloads.', 'wp-statistics'),
            //     __('Compare weekly traffic and view hourly visitor patterns.', 'wp-statistics'),
            //     __('Analyze individual content pieces with detailed widgets.', 'wp-statistics'),
            // ],
            'addon_info'        => __('Unlock deeper insights into your website\'s performance with Marketing.', 'wp-statistics'),
        ],
        true
    );
}

if ($isMarketingActive && !$isLicenseValid) {
    View::load("components/lock-sections/notice-inactive-license-addon");
}
?>
<div class="postbox">
    <table class="form-table <?php echo !$isMarketingActive ? esc_attr('form-table--preview') : '' ?>">
        <tbody>

        </tbody>
    </table>
</div>

<?php
if ($isMarketingActive) {
    submit_button(__('Update', 'wp-statistics'), 'primary', 'submit', '', array('OnClick' => "var wpsCurrentTab = getElementById('wps_current_tab'); wpsCurrentTab.value='marketing-settings'"));
}
?>