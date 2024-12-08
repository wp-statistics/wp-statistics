<?php
namespace WP_Statistics\Service\Admin\Metabox\Metaboxes;

use WP_Statistics\Components\View;
use WP_Statistics\Abstracts\BaseMetabox;

class GoPremium extends BaseMetabox
{
    protected $key = 'go_premium';
    protected $priority = 'side';

    public function getName()
    {
        return esc_html__('Go Premium', 'wp-statistics');
    }

    public function getDescription()
    {
        return esc_html__('', 'wp-statistics');
    }


    public function getData()
    {
        $args = [
            'ignore_date' => true
        ];
        $data = [];
        $output = View::load('metabox/go-premium',['data' => $data],true);

        return $output;
    }


    public function render()
    {
        View::load('metabox/go-premium', []);
    }
}