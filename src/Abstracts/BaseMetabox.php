<?php
namespace WP_Statistics\Abstracts;

use Wp_Statistics\Components\Ajax;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Option;
use WP_Statistics\Service\Admin\Metabox\MetaboxDataProvider;
use WP_STATISTICS\User;
use WP_Statistics\Utils\Request;

abstract class BaseMetabox
{
    protected $key;
    protected $context = 'normal';
    protected $priority = 'default';
    protected $static = false;
    protected $dismissible = false;
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
     * Returns the description of the metabox
     * @return string
     */
    abstract public function getDescription();

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
        return "wp-statistics-{$this->key}-widget";
    }

    /**
     * Returns the context of the metabox (side, normal, advanced)
     * @return string
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Returns the priority of the metabox (low, default, high)
     * @return string
     */
    public function getPriority()
    {
        return $this->priority;
    }


    /**
     * Returns the arguments for the metabox callback function (if any)
     * @return array|null
     */
    public function getCallbackArgs()
    {
        return null;
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
     * Determines if the metabox is dismissed by the user
     * @return bool
     */
    public function isDismissed()
    {
        if (!$this->dismissible) {
            return false;
        }

        $dismissedWidgets = Option::getOptionGroup('dismissed_widgets');

        return in_array($this->getKey(), $dismissedWidgets);
    }

    /**
     * Is the widget statically rendered, or not (static widgets don't have any dynamic data)
     * @return bool
     */
    public function isStatic()
    {
        return $this->static;
    }

    /**
     * Determines if the metabox is active and should be displayed
     * @return bool
     */
    public function isActive()
    {
        if ($this->isDismissed()) {
            return false;
        }

        return true;
    }

    /**
     * Check if the request is coming from a single post
     * @return bool
     */
    public function isSinglePost()
    {
        $isSingle = false;

        // Check if the post has ID
        if (Request::isFrom('ajax')) {
            $currentPage = Request::get('current_page', [], 'array');
            $isSingle    = !empty($currentPage['ID']) && !empty($currentPage['file']) && $currentPage['file'] === 'post.php';
        } else {
            $isSingle = Request::has('post');
        }

        return $isSingle;
    }

    /**
     * Returns the screens the metabox is active on
     * @return array
     */
    public function getScreen()
    {
        return [Menus::get_action_menu_slug('overview'), 'dashboard'];
    }

    /**
     * Enqueues the required scripts and styles for the metabox.
     *
     * This can be overridden in child classes to enqueue custom assets.
     *
     * @return void
     */
    public function enqueueAssets()
    {

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
        // Check nonce
        if (!wp_verify_nonce(Request::get('wps_nonce'), 'wp_rest')) {
            throw new \Exception('Invalid nonce.');
        }

        $this->storeFilters();

        $response = [
            'response'  => $this->getData(),
            'options'   => $this->getOptions(),
            'filters'   => $this->getFilters(),
            'meta'      => [
                'description' => $this->getDescription()
            ],
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
        $userCapability = Option::get('read_capability');
        $screens        = $this->getScreen();

        // If the dashboard widgets are disabled, remove them from the screens
        if (Option::get('disable_dashboard') && in_array('dashboard', $screens)) {
            $screens = array_diff($screens, ['dashboard']);
        }

        // Return early if the user doesn't have the capability to view the stats
        if ($userCapability && !current_user_can($userCapability)) {
            return;
        }

        $this->enqueueAssets();

        // If widget is not static, register ajax callback to get dynamic data
        if (!$this->isStatic()) {
            $key = str_replace('-', '_', $this->key);
            Ajax::register("{$key}_metabox_get_data", [$this, 'getResponse'], false);
        }

        add_meta_box($this->getKey(), $this->getName(), [$this, 'render'], $screens, $this->getContext(), $this->getPriority(), $this->getCallbackArgs());
    }
}