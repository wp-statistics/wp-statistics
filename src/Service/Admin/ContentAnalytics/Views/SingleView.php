<?php

namespace WP_Statistics\Service\Admin\ContentAnalytics\Views;

use Exception;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Request;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Abstracts\BaseView;
use WP_Statistics\Exception\SystemErrorException;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\Admin\ContentAnalytics\ContentAnalyticsDataProvider;
use WP_STATISTICS\Admin_Assets;

class SingleView extends BaseView
{
    protected $dataProvider;
    private $postId;

    public function __construct()
    {
        $this->postId = Request::get('post_id', false, 'number');

        // If post does not exist, show error
        if (!$this->postId || !get_post($this->postId)) {
            throw new SystemErrorException(
                esc_html__('Invalid post id provided.', 'wp-statistics')
            );
        }

        $this->dataProvider = new ContentAnalyticsDataProvider([
            'date' => [
                'from'  => Request::get('from', date('Y-m-d', strtotime('-30 days'))),
                'to'    => Request::get('to', date('Y-m-d'))
            ],
            'post_id' => $this->postId
        ]);
    }

    public function getData()
    {
        $data = $this->dataProvider->getSinglePostData();

        wp_localize_script(Admin_Assets::$prefix, 'Wp_Statistics_Content_Analytics_Object', [
            'performance_chart_data'    => $this->dataProvider->getPerformanceChartData(),
            'search_engine_chart_data'  => $this->dataProvider->getSearchEnginesChartData(),
            'os_chart_data'             => [
                'labels'    => array_keys($data['visitors_data']['platform']), 
                'data'      => array_values($data['visitors_data']['platform'])
            ],
            'browser_chart_data'        => [
                'labels'    => array_keys($data['visitors_data']['agent']), 
                'data'      => array_values($data['visitors_data']['agent'])
            ],
            'device_chart_data'         => [
                'labels'    => array_keys($data['visitors_data']['device']), 
                'data'      => array_values($data['visitors_data']['device'])
            ],
            'model_chart_data'          => [
                'labels'    => array_keys($data['visitors_data']['model']), 
                'data'      => array_values($data['visitors_data']['model'])
            ],
        ]);

        return $data;
    }

    public function render()
    {
        try {
            $template = 'single';

            if ($this->isLocked()) {
                $template = 'single-locked';
            }

            $args = [
                'backUrl'       => Menus::admin_url('content-analytics'),
                'backTitle'     => esc_html__('Content Analytics', 'wp-statistics'),
                'pageName'      => Menus::get_page_slug('content-analytics'),
                'DateRang'      => Admin_Template::DateRange(),
                'hasDateRang'   => true,
                'data'          => $this->getData()
            ];

            Admin_Template::get_template(['layout/header', 'layout/title', "pages/content-analytics/$template", 'layout/footer'], $args);
        } catch (Exception $e) {
            Notice::renderNotice($e->getMessage(), $e->getCode(), 'error');
        }
    }

    public function isLocked()
    {
        return !Helper::isAddOnActive('data-plus') && in_array(get_post_type($this->postId), Helper::getCustomPostTypes());
    }
}