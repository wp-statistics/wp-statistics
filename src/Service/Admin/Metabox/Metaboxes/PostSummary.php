<?php
namespace WP_Statistics\Service\Admin\Metabox\Metaboxes;

use WP_STATISTICS\Option;
use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Request;
use WP_Statistics\Components\View;
use WP_Statistics\Components\Assets;
use WP_Statistics\Abstracts\BaseMetabox;

class PostSummary extends BaseMetabox
{
    protected $key = 'post_summary';
    protected $context = 'side';
    protected $priority = 'high';
    protected $static = true;

    public function getName()
    {
        return esc_html__('Statistics - Summary', 'wp-statistics');
    }

    public function getDescription()
    {
        return '';
    }

    public function isActive()
    {
        global $pagenow;

        return $pagenow === 'post.php' && Request::compare('action', 'edit') && Request::has('post') && !Option::get('disable_editor');
    }

    public function getScreen()
    {
        return Helper::getPostTypes();
    }

    public function getData()
    {
        $data = $this->dataProvider->getPostSummaryData();

        $output = View::load('components/meta-box/post-summary', ['summary' => $data], true);

        return $output;
    }

    public function render()
    {
        View::load('metabox/metabox-skeleton');
    }
}