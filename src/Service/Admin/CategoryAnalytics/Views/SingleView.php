<?php 

namespace WP_Statistics\Service\Admin\CategoryAnalytics\Views;

use Exception;
use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Request;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Abstracts\BaseView;
use WP_Statistics\Exception\SystemErrorException;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;

class SingleView extends BaseView 
{
    private $taxId;

    public function __construct()
    {
        $this->taxId = Request::get('tax_id', false, 'number');

        // If taxonomy does not exist, show error
        if (!$this->taxId) {
            throw new SystemErrorException(
                esc_html__('Invalid taxonomy id provided.', 'wp-statistics')
            );
        }
    }

    public function render()
    {
        try {
            $args = [];

            Admin_Template::get_template(['layout/header', 'layout/title', "pages/category-analytics/category-single-locked", 'layout/footer'], $args);
        } catch (Exception $e) {
            Notice::renderNotice($e->getMessage(), $e->getCode(), 'error');
        }
    }
}