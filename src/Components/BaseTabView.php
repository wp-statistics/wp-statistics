<?php 

namespace WP_Statistics\Components;

use InvalidArgumentException;
use WP_Statistics\Utils\Request;


abstract class BaseTabView
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

    abstract protected function render();
}