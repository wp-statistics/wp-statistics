<?php 

namespace WP_Statistics\Service\Admin\CategoryAnalytics\Views;

use Exception;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;
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

    public function isLocked()
    {
        return !Helper::isAddOnActive('data-plus');
    }

    public function render()
    {
        try {
            $args = [
                'backUrl'   => Menus::admin_url('category-analytics'),
                'title'   => esc_html__('Category Analytics', 'wp-statistics'),
                'backTitle' => esc_html__('Category Analytics', 'wp-statistics'),
                'DateRang'    => Admin_Template::DateRange(),
                'hasDateRang' => true,
            ];
            $template = 'category-single';

            if ($this->isLocked()) {
                $template = 'category-single-locked';
            }

            Admin_Template::get_template(['layout/header', 'layout/title', "pages/category-analytics/$template", 'layout/footer'], $args);
        } catch (Exception $e) {
            Notice::renderNotice($e->getMessage(), $e->getCode(), 'error');
        }
    }
}