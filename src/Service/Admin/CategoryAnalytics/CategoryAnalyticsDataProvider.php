<?php 

namespace WP_Statistics\Service\Admin\CategoryAnalytics;

use WP_Statistics\Utils\Request;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Models\TaxonomyModel;

class CategoryAnalyticsDataProvider
{
    protected $taxonomyModel;
    protected $args;
    
    public function __construct($args)
    {
        $this->args = $args;

        $this->taxonomyModel = new TaxonomyModel();
    }

    public function getPagesData()
    {
        $args = array_merge($this->args, [
            'order'     => Request::get('order', 'DESC'),
            'order_by'  => Request::get('order_by', 'views'),
            'per_page'  => Admin_Template::$item_per_page,
            'page'      => Admin_Template::getCurrentPaged(),
        ]);

        $data = $this->taxonomyModel->getTaxonomiesData($args);

        return [
            'categories'  => $data,
            'total'       => count($data)
        ];
    }
}