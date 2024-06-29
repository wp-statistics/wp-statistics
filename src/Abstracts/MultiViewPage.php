<?php

namespace WP_Statistics\Abstracts;

use WP_Statistics\Exception\SystemErrorException;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Utils\Request;
use Exception;

abstract class MultiViewPage extends BasePage
{
    protected $defaultView;
    protected $views;

    protected function getViews()
    {
        return apply_filters('wp_statistics_' . str_replace('-', '_', $this->pageSlug) . '_views', $this->views);
    }

    protected function getCurrentView()
    {
        $views       = $this->getViews();
        $currentView = $this->defaultView;
        $pageType    = Request::get('type', false);

        if ($pageType && array_key_exists($pageType, $views)) {
            $currentView = $pageType;
        }

        return $currentView;
    }

    public function view()
    {
        try {
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

            // Instantiate the view class and render content
            $view = new $views[$currentView];
            $view->render();
        } catch (Exception $e) {
            Notice::renderNotice($e->getMessage(), $e->getCode(), 'error');
        }
    }
}