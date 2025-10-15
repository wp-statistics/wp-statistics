<?php
use WP_Statistics\Components\View;
use WP_STATISTICS\Country;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;

$timezoneCountry = Country::getName(Helper::getTimezoneCountry());
?>

<div class="metabox-holder wps-referral-overview">
    <div class="postbox-container" id="wps-postbox-container-1">

        <?php
            View::load("components/objects/glance-card", ['metrics' => [
                ['label' => esc_html__('Top Country', 'wp-statistics'), 'value' => $data['summary']['country']],
                ['label' => esc_html__('Top Region', 'wp-statistics'), 'value' => $data['summary']['region']],
                ['label' => esc_html__('Top City', 'wp-statistics'), 'value' => $data['summary']['city']],
            ]]);

            if (isset($data['regions'])) {
                View::load("components/tables/geographic-top-regions", [
                    'title'        => esc_html__('Regions of', 'wp-statistics') . ' ' . $timezoneCountry,
                    'top_title'    => esc_html__('Regions', 'wp-statistics'),
                    'footer_title' => esc_html__('View Regions of', 'wp-statistics') . ' ' . $timezoneCountry,
                    'footer_link'  => esc_url(Menus::admin_url('geographic', ['tab' => 'regions'])),
                    'data'         => $data['regions']
                ]);
            }

            View::load("components/tables/geographic-top-regions", [
                'title'        => esc_html__('Top US States', 'wp-statistics'),
                'top_title'    => esc_html__('States', 'wp-statistics'),
                'footer_title' => esc_html__('View US States', 'wp-statistics'),
                'footer_link'  => esc_url(Menus::admin_url('geographic', ['tab' => 'us'])),
                'data'         => $data['states']
            ]);


            View::load("components/charts/horizontal-bar", [
                'title'        => esc_html__('Top European Countries', 'wp-statistics'),
                'footer_title' => esc_html__('View European Countries', 'wp-statistics'),
                'footer_link'  => esc_url(Menus::admin_url('geographic', ['tab' => 'europe'])),
                'unique_id'    => 'geographic--top-countries'
            ]);

            View::load("components/charts/horizontal-bar", [
                'title'     => esc_html__('Visitors by Continent', 'wp-statistics'),
                'unique_id' => 'geographic-visitors-continent'
            ]);
        ?>

    </div>
    <div class="postbox-container" id="wps-postbox-container-2">
        <div class="wps-card">
            <div class="wps-card__title">
                <h2><?php esc_html_e('Global Visitor Map', 'wp-statistics') ?></h2>
            </div>
            <div class="inside wps-geo-map">
                <?php
                    View::load("metabox/global-visitor-distribution", ['data' => $data['map_data']]);
                ?>
            </div>
        </div>

        <?php
            View::load("components/tables/geographic-top-countries", ['data' => $data['countries']]);

            View::load("components/tables/geographic-top-cities", ['data' => $data['cities']]);
        ?>
    </div>
</div>