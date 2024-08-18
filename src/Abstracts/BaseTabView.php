<?php 

namespace WP_Statistics\Abstracts;

use WP_Statistics\Exception\SystemErrorException;
use WP_Statistics\Utils\Request;

abstract class BaseTabView extends BaseView
{
    protected $defaultTab;
    protected $tabs;

    public function __construct()
    {
        // Throw error when invalid tab provided
        if (!in_array($this->getCurrentTab(), $this->tabs)) {
            throw new SystemErrorException(
                esc_html__('Invalid tab provided.', 'wp-statistics')
            );
        }
    }

    protected function getCurrentTab()
    {
        return Request::get('tab', $this->defaultTab);
    }

    protected function isTab($tab)
    {
        return Request::get('tab', $this->defaultTab) === $tab;
    }

    protected function getTabData()
    {
        $currentTab     = ucwords($this->getCurrentTab(), '-');
        $tabDataMethod  = 'get' . str_replace('-', '', $currentTab) . 'Data';

        if (!method_exists($this, $tabDataMethod)) return [];

        return $this->$tabDataMethod();
    }
}