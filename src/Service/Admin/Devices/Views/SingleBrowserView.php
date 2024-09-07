<?php

namespace WP_Statistics\Service\Admin\Devices\Views;

use WP_STATISTICS\Admin_Template;
use WP_STATISTICS\Menus;
use WP_Statistics\Abstracts\BaseView;
use WP_Statistics\Exception\SystemErrorException;
use WP_Statistics\Service\Admin\Devices\DevicesDataProvider;
use WP_Statistics\Utils\Request;

class SingleBrowserView extends BaseView
{
    protected $browser;

    public function __construct()
    {
        $this->browser = Request::get('browser');

        if (empty($this->browser)) {
            throw new SystemErrorException(esc_html__('Invalid browser provided!', 'wp-statistics'));
        }

        $this->dataProvider = new DevicesDataProvider([
            'per_page' => 10,
            'page'     => Admin_Template::getCurrentPaged()
        ]);
    }

    public function getData()
    {
        return $this->dataProvider->getSingleBrowserData($this->browser);
    }

    public function render()
    {
        $args = [
            // translators: %s: Browser/OS/Model/etc. name.
            'title'         => sprintf(esc_html__('%s Report', 'wp-statistics'), $this->browser),
            'backTitle'     => esc_html__('Browsers', 'wp-statistics'),
            'backUrl'       => Menus::admin_url('devices', ['tab' => 'browsers']),
            'firstColTitle' => esc_html__('Version', 'wp-statistics'),
            'pageName'      => Menus::get_page_slug('devices'),
            'paged'         => Admin_Template::getCurrentPaged(),
            'DateRang'      => Admin_Template::DateRange(),
            'hasDateRang'   => true,
            'custom_get'    => ['type' => 'single-browser', 'browser' => $this->browser],
            'data'          => $this->getData()
        ];

        if ($args['data']['total'] > 0) {
            $args['total'] = $args['data']['total'];

            $args['pagination'] = Admin_Template::paginate_links([
                'item_per_page' => 10,
                'total'         => $args['total'],
                'echo'          => false
            ]);
        }

        Admin_Template::get_template(['layout/header', 'layout/title', 'pages/devices/single-browser', 'layout/footer'], $args);
    }
}
