<?php
namespace WP_Statistics\Service\Admin\Metabox\Metaboxes;

use WP_Statistics\Components\View;
use WP_Statistics\Abstracts\BaseMetabox;
use WP_Statistics\Components\Assets;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Option;
use WP_Statistics\Service\Admin\Posts\PostsManager;
use WP_Statistics\Utils\Request;

class PostSummary extends BaseMetabox
{
    protected $key = 'post_summary';
    protected $context = 'side';
    protected $priority = 'high';
    protected $static = true;
    protected $data = [];

    public function __construct()
    {
        parent::__construct();

        $this->data = $this->dataProvider->getPostSummaryData();
    }

    public function getName()
    {
        return esc_html__('Statistics - Summary', 'wp-statistics');
    }

    public function getDescription()
    {
        return '';
    }

    public function getData()
    {
        return false;
    }


    public function isActive()
    {
        return function_exists('get_current_screen') && get_current_screen()->base === 'post' && Request::compare('action', 'edit') && Request::has('post');
    }

    public function getScreen()
    {
        return Helper::getPostTypes();
    }

    public function getCallbackArgs()
    {
        return [
            '__back_compat_meta_box' => false
        ];
    }

    public function enqueueAssets()
    {
        $styleFileName  = is_rtl() ? 'style-post-summary-rtl.css' : 'style-post-summary.css';

        Assets::script('editor-sidebar', 'blocks/post-summary/post-summary.js', ['wp-plugins', 'wp-editor'], $this->data);
        Assets::style('editor-sidebar', "blocks/post-summary/$styleFileName");
    }

    public function render()
    {
        $postId = Request::get('post', 0, 'number');
        $post   = get_post($postId);

        // Check if post ID is set
        if (empty($postId) || empty($post) || $post->post_status != 'publish' || $post->post_status == 'private') {
            esc_html_e('This post is not yet published.', 'wp-statistics');
            return;
        }

        View::load('components/meta-box/post-summary', ['summary' => $this->data]);
    }
}