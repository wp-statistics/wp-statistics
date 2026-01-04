<?php

namespace WP_Statistics\Service\Admin\Metabox\Metaboxes;

use WP_Statistics\Components\View;
use WP_Statistics\Abstracts\BaseMetabox;
use WP_Statistics\Globals\Option;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;

class AboutWPS extends BaseMetabox
{
    protected $settings = [
        'custom_widget' => '',
        'title'         => '',
        'content'       => ''
    ];

    protected $key = 'about';
    protected $context = 'side';
    protected $static = true;

    public function __construct()
    {
        if (! Helper::isAddOnActive('customization')) {
            return;
        }

        $this->settings = [
            'custom_widget' => Option::getAddonValue('show_wps_about_widget_overview', 'customization', 'yes'),
            'title'         => Option::getAddonValue('wps_about_widget_title', 'customization', ''),
            'content'       => Option::getAddonValue('wps_about_widget_content', 'customization', ''),
        ];
    }

    public function getKey()
    {
        if ($this->settings['custom_widget'] === 'yes') {
            $this->key = "custom-{$this->key}";
        }

        return "wp-statistics-{$this->key}-metabox";
    }

    public function getName()
    {
        if ($this->settings['custom_widget'] === 'yes' && !empty($this->settings['title'])) {
            return $this->settings['title'];
        }

        return esc_html__('WP Statistics', 'wp-statistics');
    }

    public function getDescription()
    {
        return '';
    }

    public function getScreen()
    {
        return [Menus::get_action_menu_slug('overview')];
    }

    public function getData()
    {
        return false;
    }

    public function render()
    {
        if ($this->settings['custom_widget'] === 'yes') {
            echo '<div class="o-wrap o-wrap--no-data">' . $this->settings['content'] . '</div>';
        } else {
            View::load('metabox/about', []);
        }
    }
}
