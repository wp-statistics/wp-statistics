<?php
namespace WP_Statistics\Service\Admin\Geographic\Views;

use WP_STATISTICS\Menus;
use WP_Statistics\Utils\Request;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Abstracts\BaseView;
use WP_STATISTICS\Country;
use WP_Statistics\Components\View;
use WP_Statistics\Exception\SystemErrorException;

class SingleCountryView extends BaseView
{
    public function __construct()
    {
        if (!Request::has('country')) {
            throw new SystemErrorException(
                esc_html__('Invalid country code provided.', 'wp-statistics')
            );
        }
    }

    public function render()
    {
        $args = [
            'page_title'        => esc_html__('Unlock Detailed Geographic Traffic Insights', 'wp-statistics'),
            'addon_name'        => esc_html__('Data Plus', 'wp-statistics'),
            'addon_slug'        => 'wp-statistics-data-plus',
            'campaign'          => 'data-plus',
            'more_title'        => esc_html__('Learn More', 'wp-statistics'),
            'premium_btn_title' => esc_html__('Go Premium for Complete Geographic Reports', 'wp-statistics'),
            'images'            => ['geographic-single.png'],
            'description'       => esc_html__('Discover your traffic on a country-by-country basis. Discover how visitors from each region, city, and country interact with your site. Know where your audience comes from, what devices they use, and how they found you.', 'wp-statistics')
        ];

        Admin_Template::get_template(['layout/header']);
        View::load("pages/lock-page", $args);
        Admin_Template::get_template(['layout/footer']);
    }
}