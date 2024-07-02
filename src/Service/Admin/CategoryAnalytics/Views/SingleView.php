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
    private $termId;

    public function __construct()
    {
        $this->termId = Request::get('term_id', false, 'number');

        // If term does not exist, show error
        if (!$this->termId) {
            throw new SystemErrorException(
                esc_html__('Invalid term id provided.', 'wp-statistics')
            );
        }
    }

    public function isLocked()
    {
        $term = get_term($this->termId);

        return !Helper::isAddOnActive('data-plus') && Helper::isCustomTaxonomy($term->taxonomy);
    }

    public function render()
    {
        try {
            $template = 'category-single';

            if ($this->isLocked()) {
                $template = 'category-single-locked';
            }

            $args = [
                'backUrl'       => Menus::admin_url('category-analytics'),
                'pageName'      => Menus::get_page_slug('category-analytics'),
                'title'         => esc_html__('Category Analytics', 'wp-statistics'),
                'backTitle'     => esc_html__('Category Analytics', 'wp-statistics'),
                'DateRang'      => Admin_Template::DateRange(),
                'hasDateRang'   => true,
            ];

            Admin_Template::get_template(['layout/header', 'layout/title', "pages/category-analytics/$template", 'layout/footer'], $args);
        } catch (Exception $e) {
            Notice::renderNotice($e->getMessage(), $e->getCode(), 'error');
        }
    }
}