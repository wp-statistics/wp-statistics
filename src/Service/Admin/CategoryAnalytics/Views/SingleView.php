<?php 

namespace WP_Statistics\Service\Admin\CategoryAnalytics\Views;

use Exception;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Admin_Assets;
use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Request;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Abstracts\BaseView;
use WP_Statistics\Exception\SystemErrorException;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\Admin\CategoryAnalytics\CategoryAnalyticsDataProvider;

class SingleView extends BaseView 
{
    private $term;
    private $termId;

    public function __construct()
    {
        $this->termId = Request::get('term_id', false, 'number');

        // If term does not exist, show error
        if (!$this->termId || !term_exists($this->termId)) {
            throw new SystemErrorException(
                esc_html__('Invalid term id provided.', 'wp-statistics')
            );
        }

        $this->term = get_term($this->termId);

        $this->dataProvider = new CategoryAnalyticsDataProvider([
            'term'      => $this->termId,
            'taxonomy'  => $this->term->taxonomy
        ]);
    }

    public function isLocked()
    {
        return !Helper::isAddOnActive('data-plus') && Helper::isCustomTaxonomy($this->term->taxonomy);
    }

    public function getData()
    {
        wp_localize_script(Admin_Assets::$prefix, 'Wp_Statistics_Category_Analytics_Object', $this->dataProvider->getChartsData());

        return $this->dataProvider->getSingleTermData();
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
                'custom_get'    => ['type' => 'single', 'term_id' => Request::get('term_id', '', 'number')],
                'title'         => sprintf(esc_html__('%s: "%s"', 'wp-statistics'), Helper::getTaxonomyName($this->term->taxonomy, true), $this->term->name),
                'backTitle'     => esc_html__('Category Analytics', 'wp-statistics'),
                'DateRang'      => Admin_Template::DateRange(),
                'hasDateRang'   => true,
                'data'          => $this->getData()
            ];

            Admin_Template::get_template(['layout/header', 'layout/title', "pages/category-analytics/$template", 'layout/footer'], $args);
        } catch (Exception $e) {
            Notice::renderNotice($e->getMessage(), $e->getCode(), 'error');
        }
    }
}