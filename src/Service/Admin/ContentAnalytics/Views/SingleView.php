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
            'post_id'       => $this->postId,
            'query_param'   => Helper::isAddOnActive('data-plus') ? Request::get('qp', '', 'number') : ''
        ]);
    }

    public function getData()
    {
        wp_localize_script(Admin_Assets::$prefix, 'Wp_Statistics_Content_Analytics_Object', $this->dataProvider->getChartsData());

        return $this->dataProvider->getSinglePostData();
    }

    public function render()
    {
        try {
            $template = 'single';

            if ($this->isLocked()) {
                $template = 'single-locked';
            }

            $args = [
                'backUrl'       => Menus::admin_url('content-analytics', ['tab' => get_post_type($this->postId)]),
                'custom_get'    => ['type' => 'single', 'post_id' => Request::get('post_id', '', 'number'), 'qp' => Request::get('qp', '', 'number')],
                'backTitle'     => esc_html__('Content Analytics', 'wp-statistics'),
                'pageName'      => Menus::get_page_slug('content-analytics'),
                'DateRang'      => Admin_Template::DateRange(),
                'filters'       => ['query-params'],
                'hasDateRang'   => true,
                'data'          => $this->getData(),
                'allTimeOption' => true
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