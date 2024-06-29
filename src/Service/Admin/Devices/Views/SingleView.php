<?php

namespace WP_Statistics\Service\Admin\Devices\Views;

use WP_Statistics\Abstracts\BaseView;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Exception\SystemErrorException;
use WP_STATISTICS\Menus;
use WP_Statistics\Service\Admin\Devices\DevicesDataProvider;
use WP_Statistics\Utils\Request;

class SingleView extends BaseView
{
    protected $viewArgs;

    /**
     * Constructs a single view for devices.
     *
     * @param   array   $viewArgs   Required indexes: `key`, `back_title`, `back_url`, `first_col_title`.
     */
    public function __construct($viewArgs = [])
    {
        if (!isset($viewArgs['key'], $viewArgs['back_title'], $viewArgs['back_url'], $viewArgs['first_col_title'])) {
            throw new SystemErrorException(
                esc_html__('Invalid single view arguments provided!', 'wp-statistics')
            );
        }
        $this->viewArgs = $viewArgs;

        if (!Request::has($this->viewArgs['key'])) {
            throw new SystemErrorException(
                esc_html__('Invalid single view key provided!', 'wp-statistics')
            );
        }

        $this->dataProvider = new DevicesDataProvider([
            'date'     => [
                'from' => Request::get('from', date('Y-m-d', strtotime('-30 days'))),
                'to'   => Request::get('to', date('Y-m-d')),
            ],
            'per_page' => 10,
            'page'     => Admin_Template::getCurrentPaged()
        ]);
    }

    public function render()
    {
        $args = [
            // translators: %s: Browser/OS/Model/etc. name.
            'title'           => sprintf(esc_html__('%s Report', 'wp-statistics'), Request::get($this->viewArgs['key'])),
            'backTitle'       => $this->viewArgs['back_title'],
            'backUrl'         => $this->viewArgs['back_url'],
            'firstColTitle'   => $this->viewArgs['first_col_title'],
            'pageName'        => Menus::get_page_slug('devices'),
            'paged'           => Admin_Template::getCurrentPaged(),
            'DateRang'        => Admin_Template::DateRange(),
            'hasDateRang'     => true,
            'data'            => $this->dataProvider->{'getSingle' . ucfirst(strtolower($this->viewArgs['key'])) . 'Data'}(Request::get($this->viewArgs['key'])),
        ];

        if ($args['data']['total'] > 0) {
            $args['total'] = $args['data']['total'];

            $args['pagination'] = Admin_Template::paginate_links([
                'item_per_page' => 10,
                'total'         => $args['total'],
                'echo'          => false
            ]);
        }

        Admin_Template::get_template(['layout/header', 'layout/title', 'pages/devices/single-locked', 'layout/footer'], $args);
    }
}
