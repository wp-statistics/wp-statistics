<?php
namespace WP_Statistics\Service\Admin\Metabox\Metaboxes;

use WP_STATISTICS\Option;
use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Request;
use WP_Statistics\Components\View;
use WP_Statistics\Abstracts\BaseMetabox;
use WP_Statistics\Service\Admin\Posts\EditorPanel;

class PostSummary extends BaseMetabox
{
    protected $key = 'post-summary';
    protected $context = 'side';
    protected $priority = 'high';

    public function getName()
    {
        return esc_html__('Statistics Summary', 'wp-statistics');
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

    /**
     * In the block editor the "WP Statistics" sidebar panel shows this content, so the
     * meta box is only kept for the Classic Editor (`__back_compat_meta_box`).
     *
     * @return array
     */
    public function getCallbackArgs()
    {
        return [
            '__back_compat_meta_box' => EditorPanel::isAvailable(),
        ];
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