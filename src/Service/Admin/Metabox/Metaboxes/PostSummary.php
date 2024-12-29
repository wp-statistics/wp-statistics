<?php
namespace WP_Statistics\Service\Admin\Metabox\Metaboxes;

use WP_STATISTICS\Option;
use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Request;
use WP_Statistics\Components\View;
use WP_Statistics\Abstracts\BaseMetabox;

class PostSummary extends BaseMetabox
{
    protected $key = 'post-summary';
    protected $context = 'side';
    protected $priority = 'high';

    public function getName()
    {
        return esc_html__('Statistics - Summary', 'wp-statistics');
    }

    public function getDescription()
    {
        return '';
    }

    public function isOptionEnabled()
    {
        return !Option::get('disable_editor');
    }

    public function isActive()
    {
        return $this->isOptionEnabled() && $this->isSinglePost();
    }

    public function getScreen()
    {
        return Helper::getPostTypes();
    }

    public function getData()
    {
        $data = $this->dataProvider->getPostSummaryData();

        $output = View::load('components/meta-box/post-summary', ['summary' => $data], true);

        return [
            'output'    => $output,
            'data'      => $data
        ];
    }

    public function render()
    {
        View::load('metabox/metabox-skeleton');
    }
}