<?php
namespace WP_Statistics\Service\Admin\Geographic\Views;

use WP_STATISTICS\Menus;
use WP_Statistics\Service\Admin\ExportImport\ExportTypes;
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
            'page_title' => __('See traffic from each country in detail', 'wp-statistics'),
            'benefits'   => [
                __('Visitors from each region and city', 'wp-statistics'),
                __('Devices and browsers in that country', 'wp-statistics'),
                __('How visitors found you', 'wp-statistics'),
            ],
            'addon_name'        => esc_html__('Data Plus', 'wp-statistics'),
            'addon_slug'        => 'wp-statistics-data-plus',
            'campaign'          => 'data-plus',
            'images'            => ['geographic-single.png'],
            'export'            => [ExportTypes::CSV_METRICS, ExportTypes::PDF_PAGE]
        ];

        Admin_Template::get_template(['layout/header']);
        View::load("pages/lock-page", $args);
        Admin_Template::get_template(['layout/footer']);
    }
}