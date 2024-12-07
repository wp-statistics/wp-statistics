<?php
namespace WP_Statistics\Service\Admin\Metabox\Metaboxes;

use WP_Statistics\Components\View;
use WP_Statistics\Abstracts\BaseMetabox;
use WP_STATISTICS\Menus;

class BrowserUsage extends BaseMetabox
{
    protected $key = 'browser_usage';
    protected $priority = 'side';

    public function getName()
    {
        return esc_html__('Browser Usage', 'wp-statistics');
    }

    public function getDescription()
    {
        return esc_html__('Distribution of visitors based on the browsers they use.', 'wp-statistics');
    }

    public function getOptions()
    {
        return [
            'datepicker'    => true,
            'button'        => View::load('metabox/action-button',['link'=> Menus::admin_url('devices', ['tab' => 'browsers']),'title'=>'View Browser Usage'],true)
        ];
    }

    public function getData()
    {
        $args = [
            'ignore_date' => true
        ];

        //  @todo  Add data
        $data = [
            'tag_id' => 'wps-browser-usage',
            'browsers_logos' => [
                WP_STATISTICS_URL."assets/images/browser/chrome.svg",
                WP_STATISTICS_URL."assets/images/browser/firefox.svg",
                WP_STATISTICS_URL."assets/images/browser/edge.svg",
                WP_STATISTICS_URL."assets/images/browser/safari.svg",
                ""
            ],
            'data' => [
                121,
                30,
                5,
                4,
                5
            ],
            'label' => [
                "Chrome",
                "Firefox",
                "Edge",
                "Safari",
                "Other"
            ],
        ];

        $output = View::load('metabox/horizontal-bar', ['data' => $data , 'unique_id' => 'wps-browser-usage'], true);

        return [
            'output' => $output,
            'data' => $data
        ];
    }

    public function render()
    {
        View::load('metabox/metabox-skeleton', []);
    }
}