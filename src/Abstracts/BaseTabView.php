<?php 

namespace WP_Statistics\Abstracts;

use InvalidArgumentException;
use WP_Statistics\Utils\Request;

abstract class BaseTabView extends BaseView
{
    protected $defaultTab;
    protected $tabs;

    public function __construct()
    {
        // Throw error when invalid tab provided
        if (!in_array($this->getCurrentTab(), $this->tabs)) {
            throw new InvalidArgumentException(
                esc_html__('Invalid tab provided.', 'wp-statistics')
            );
        }
    }

    protected function getCurrentTab()
    {
        return Request::get('tab', $this->defaultTab);
    }
}