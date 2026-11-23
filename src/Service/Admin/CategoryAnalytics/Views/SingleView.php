<?php

namespace WP_Statistics\Service\Admin\CategoryAnalytics\Views;

use Exception;
use WP_Statistics\Components\View;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Admin_Assets;
use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Request;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Abstracts\BaseView;
use WP_Statistics\Exception\SystemErrorException;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\Admin\CategoryAnalytics\CategoryAnalyticsDataProvider;
use WP_Statistics\Service\Admin\ExportImport\ExportTypes;

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

    public function renderLocked()
    {
        $args = [
            'page_title' => __('See how each category and tag performs', 'wp-statistics'),
            'benefits'   => [
                __('Reports for custom taxonomies', 'wp-statistics'),
                __('Views and top posts for each term', 'wp-statistics'),
                __('Link clicks and file downloads', 'wp-statistics'),
            ],
            'addon_name'        => esc_html__('Data Plus', 'wp-statistics'),
            'addon_slug'        => 'wp-statistics-data-plus',
            'campaign'          => 'data-plus',
            'images'            => ['data-plus-advanced-filtering.png','data-plus-category.png','data-plus-comparison-widget.png','data-plus-download-tracker-recents.png'],
        ];

        Admin_Template::get_template(['layout/header']);
        View::load("pages/lock-page", $args);
        Admin_Template::get_template(['layout/footer']);
    }

    public function renderContent()
    {
        $args = [
            'backUrl'       => Menus::admin_url('category-analytics'),
            'pageName'      => Menus::get_page_slug('category-analytics'),
            'custom_get'    => ['type' => 'single', 'term_id' => Request::get('term_id', '', 'number')],
            'backTitle'     => esc_html__('Category Analytics', 'wp-statistics'),
            'DateRang'      => Admin_Template::DateRange(),
            'hasDateRang'   => true,
            'export'        => [ExportTypes::CSV_METRICS, ExportTypes::PDF_PAGE],
            'data'          => $this->getData()
        ];

        Admin_Template::get_template(['layout/header', 'layout/title', "pages/category-analytics/category-single", 'layout/footer'], $args);
    }

    public function render()
    {
        if ($this->isLocked()) {
            $this->renderLocked();
        } else {
            $this->renderContent();
        }
    }
}