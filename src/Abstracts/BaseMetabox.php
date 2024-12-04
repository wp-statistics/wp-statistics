<?php
namespace WP_Statistics\Abstracts;

use Wp_Statistics\Components\Ajax;
use WP_Statistics\Service\Admin\Metabox\MetaboxDataProvider;
use WP_STATISTICS\User;
use WP_Statistics\Utils\Request;

abstract class BaseMetabox
{
    protected $key;
    protected $priority;
    protected $dataProvider;

    public function __construct()
    {
        $this->dataProvider = new MetaboxDataProvider();
    }

    /**
     * Returns the name of the metabox
     * @return string
     */
    abstract public function getName();

    /**
     * Returns the data for the metabox
     * @return string|array
     */
    abstract public function getData();

    /**
     * Renders the metabox output
     * @return void
     */
    abstract public function render();

    /**
     * Returns the key for the metabox (should be unique)
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Returns the priority of the metabox (side, normal, advanced)
     * @return string
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Returns the options of the metabox (datepicker, button, etc.)
     * @return array
     */
    public function getOptions()
    {
        return [];
    }

    /**
     * Determines if the metabox is active and should be displayed
     * @return bool
     */
    public function isActive()
    {
        return true;
    }

    /**
     * Returns the screens the metabox is active on
     * @todo get overview screen id dynamically
     * @return array
     */
    public function getScreen()
    {
        return ['statistics_page_wps_overview-new_page', 'dashboard'];
    }

    /**
     * Stores the filters for the metabox.
     *
     * @return void
     */
    protected function storeFilters()
    {
        $options = $this->getOptions();

        if (!empty($options['datepicker']) && Request::has('from') && Request::has('to')) {
            $args = [
                'filter'    => Request::get('date_filter'),
                'from'      => Request::get('from'),
                'to'        => Request::get('to')
            ];

            User::saveDefaultDateFilter($this->getKey(), $args);
        }
    }

    /**
     * Returns an array of filters for the metabox.
     * @return array
     */
    protected function getFilters()
    {
        $filters = [];
        $options = $this->getOptions();

        if (!empty($options['datepicker'])) {
            $filters['date'] = User::getDefaultDateFilter($this->getKey());
        }

        return $filters;
    }

    /**
     * Sends a JSON response with the given data and options
     *
     * @param mixed $data The data to be sent
     *
     * @return void
     */
    public function getResponse()
    {
        $this->storeFilters();

        $response = [
            'response'  => $this->getData(),
            'options'   => $this->getOptions(),
            'filters'   => $this->getFilters()
        ];

        wp_send_json($response);
    }

    /**
     * Registers the metabox
     *
     * Registers the metabox with the admin and hooks into the WordPress AJAX handler
     *
     * @return void
     */
    public function register()
    {
        Ajax::register($this->getKey() . '_metabox_get_data', [$this, 'getResponse'], false);
        add_meta_box($this->getKey(), $this->getName(), [$this, 'render'], $this->getScreen(), $this->getPriority());
    }
}