<?php 
namespace WP_Statistics\Service\Admin\Geographic\Views;

use WP_STATISTICS\Menus;
use WP_Statistics\Utils\Request;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Abstracts\BaseView;
use WP_STATISTICS\Country;
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
            'title'     => sprintf(esc_html__('%s Traffic Report', 'wp-statistics'), Country::getName(Request::get('country'))),
            'backUrl'   => Menus::admin_url('geographic'),
            'backTitle' => esc_html__('Geographic', 'wp-statistics'),
        ];

        Admin_Template::get_template(['layout/header', 'layout/title', 'pages/geographic/single-locked', 'layout/footer'], $args);
    }
}