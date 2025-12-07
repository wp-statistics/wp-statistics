<?php

namespace WP_Statistics\Abstracts;

use WP_Statistics\Exception\SystemErrorException;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Utils\Request;
use Exception;
use WP_STATISTICS\Admin_Assets;
use WP_Statistics\Globals\Context;

abstract class MultiViewPage extends BasePage
{
    protected $defaultView;
    protected $views;
    protected $filters;

    protected function getViews()
    {
        return apply_filters('wp_statistics_' . str_replace('-', '_', $this->pageSlug) . '_views', $this->views);
    }

    protected function getFilters() {
        return apply_filters('wp_statistics_' . str_replace('-', '_', $this->pageSlug) . '_filters', $this->filters);
    }

    public function getCurrentView()
    {
        $views       = $this->getViews();
        $currentView = $this->defaultView;
        $pageType    = Request::get('type', false);

        if ($pageType && array_key_exists($pageType, $views)) {
            $currentView = $pageType;
        }

        return $currentView;
    }

    public function getCurrentViewClass()
    {
        $views       = $this->getViews();
        $currentView = $this->defaultView;
        $pageType    = Request::get('type', false);

        if ($pageType && array_key_exists($pageType, $views)) {
            $currentView = $pageType;
        }

        return new $views[$currentView];
    }

    public function view()
    {
        try {
            $this->validateDateRange();

            // Get all views
            $views = $this->getViews();

            // Get current view
            $currentView = $this->getCurrentView();

            // Check if the view does not exist, throw exception
            if (!isset($views[$currentView])) {
                throw new SystemErrorException(
                    esc_html__('View is not valid.', 'wp-statistics')
                );
            }

            // Check if the class does not have render method, throw exception
            if (!method_exists($views[$currentView], 'render')) {
                throw new SystemErrorException(
                    sprintf(esc_html__('render method is not defined within %s class.', 'wp-statistics'), $currentView)
                );
            }

            $filters = $this->getFilters();

            if (! empty($filters)) {
                wp_localize_script(Admin_Assets::$prefix, 'wpStatisticsFilters', $filters);
            }

            // Instantiate the view class and render content
            $view = $this->getCurrentViewClass();
            $view->render();
        } catch (Exception $e) {
            Notice::renderNotice($e->getMessage(), $e->getCode(), 'error');
        }
    }
}