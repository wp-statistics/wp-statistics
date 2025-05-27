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
            'page_title'        => esc_html__('Data Plus: Advanced Analytics for Deeper Insights', 'wp-statistics'),
            'page_second_title' => esc_html__('WP Statistics Premium: Beyond Just Data Plus', 'wp-statistics'),
            'addon_name'        => esc_html__('Data Plus', 'wp-statistics'),
            'addon_slug'        => 'wp-statistics-data-plus',
            'campaign'          => 'data-plus',
            'more_title'        => esc_html__('Learn More About Data Plus', 'wp-statistics'),
            'premium_btn_title' => esc_html__('Upgrade Now to Unlock All Premium Features!', 'wp-statistics'),
            'images'            => ['data-plus-advanced-filtering.png','data-plus-category.png','data-plus-comparison-widget.png','data-plus-download-tracker-recents.png'],
            'description'       => esc_html__('Data Plus is a premium add-on for WP Statistics that unlocks powerful analytics features, providing a complete view of your site’s performance. Take advantage of advanced tools that help you understand visitor behavior, enhance your content, and track engagement on a new level. With Data Plus, you can make data-driven decisions to grow your site more effectively.', 'wp-statistics'),
            'second_description'=> esc_html__('When you upgrade to WP Statistics Premium, you don’t just get Data Plus — you gain access to all premium add-ons, delivering detailed insights and tools for every aspect of your site.', 'wp-statistics')
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