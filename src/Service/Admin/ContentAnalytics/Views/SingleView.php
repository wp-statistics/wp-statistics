<?php 

namespace WP_Statistics\Service\Admin\ContentAnalytics\Views;

use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Request;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Abstracts\BaseView;
use WP_Statistics\Exception\SystemErrorException;

class SingleView extends BaseView 
{
    private $postId;

    public function __construct()
    {
        $this->postId = Request::get('post_id', false, 'number');

        // If post does not exist, show error
        if (!$this->postId || !get_post($this->postId)) {
            throw new SystemErrorException(
                esc_html__('Invalid post id provided.', 'wp-statistics')
            );
        }
    }

    public function isLocked()
    {
        return !Helper::isAddOnActive('data-plus') && in_array(get_post_type($this->postId), Helper::getCustomPostTypes());
    }

    public function render()
    {
        $args       = [];
        $template   = 'single';

        if ($this->isLocked()) {
            $template = 'single-locked';
        }

        Admin_Template::get_template(['layout/header', 'layout/title', "pages/content-analytics/$template", 'layout/footer'], $args);
    }
}