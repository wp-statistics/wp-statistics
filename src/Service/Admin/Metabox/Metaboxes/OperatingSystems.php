<?php
namespace WP_Statistics\Service\Admin\Metabox\Metaboxes;

use WP_Statistics\Components\View;
use WP_Statistics\Abstracts\BaseMetabox;
use WP_STATISTICS\Menus;

class OperatingSystems extends BaseMetabox
{
    protected $key = 'most_used_operating_systems';
    protected $priority = 'side';

    public function getName()
    {
        return esc_html__('Most Used Operating Systems', 'wp-statistics');
    }

    public function getDescription()
    {
        return esc_html__('Identify the operating systems most commonly used by your website visitors.', 'wp-statistics');
    }

    public function getOptions()
    {
        return [
            'datepicker'    => true,
            'button'        => View::load('metabox/action-button',[
                'link'  => Menus::admin_url('devices', ['tab' => 'platforms']),
                'title' => esc_html__('View Most Used OS', 'wp-statistics')
            ],true)
        ];
    }

    public function getData()
    {
        $args = [
            'ignore_date' => true
        ];

        //  @todo  Add data
        $data = [
            'tag_id' => 'wps-most-used-operating-systems',
            'os_logos' => [
                WP_STATISTICS_URL."assets/images/operating-system/windows.svg",
                WP_STATISTICS_URL."assets/images/operating-system/os_x.svg",
                WP_STATISTICS_URL."assets/images/operating-system/android.svg",
                WP_STATISTICS_URL."assets/images/operating-system/linux.svg",
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
                "Windows",
                "OS X",
                "Android",
                "Linux",
                "Other"
            ],
        ];

        $output = View::load('metabox/horizontal-bar', ['data' => $data , 'unique_id' => 'wps-most-used-operating-systems'], true);

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