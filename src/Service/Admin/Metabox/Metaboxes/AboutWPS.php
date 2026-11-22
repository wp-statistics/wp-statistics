<?php

namespace WP_Statistics\Service\Admin\Metabox\Metaboxes;

use WP_Statistics\Components\View;
use WP_Statistics\Abstracts\BaseMetabox;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Option;

class AboutWPS extends BaseMetabox
{
    protected $settings = [
        'custom_widget' => '',
        'title'         => '',
        'content'       => '',
        'white_label'   => false,
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
            'custom_widget' => Option::getByAddon('show_wps_about_widget_overview', 'customization', 'yes'),
            'title'         => Option::getByAddon('wps_about_widget_title', 'customization', ''),
            'content'       => Option::getByAddon('wps_about_widget_content', 'customization', ''),
            'white_label'   => Option::getByAddon('wps_white_label', 'customization') == '1',
        ];
    }

    public function getKey()
    {
        if ($this->settings['custom_widget'] === 'yes') {
            $this->key = "custom-{$this->key}";
        }

        return "wp-statistics-{$this->key}-metabox";
    }

    /**
     * White label without a custom widget: the default card (WP Statistics name, version, links to
     * wp-statistics.com, review request) would bring the brand back, so it stays out.
     *
     * @return bool
     */
    public function isActive()
    {
        if ($this->settings['white_label'] && $this->settings['custom_widget'] !== 'yes') {
            return false;
        }

        return parent::isActive();
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
