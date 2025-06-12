<?php

namespace WP_Statistics\Models;

use WP_Statistics\Abstracts\BaseModel;
use WP_STATISTICS\Helper;
use WP_Statistics\Models\Legacy\LegacyPostsModel;
use WP_Statistics\Service\Admin\Posts\WordCountService;
use WP_Statistics\Utils\Query;

class PostsModel extends BaseModel
{
    private $legacy;

    public function __construct()
    {
        $this->legacy = new LegacyPostsModel();
    }

    public function countPosts($args = [])
    {
        if (false) {
            return $this->legacy->countPosts($args);
        }

        return (new ResourceModel())->countResources($args);
    }

    public function countDailyPosts($args = [])
    {
        if (false) {
            return $this->legacy->countDailyPosts($args);
        }

        return (new ResourceModel())->countDailyResources($args);
    }

    public function countWords($args = [])
    {
        if (false) {
            return $this->legacy->countWords($args);
        }

        return (new ResourceModel())->countWords($args);
    }

    public function countComments($args = [])
    {
        if (false) {
            return $this->legacy->countComments($args);
        }

        return (new ResourceModel())->countComments($args);
    }

    public function getPostsReportData($args = [])
    {
        if (false) {
            return $this->legacy->getPostsReportData($args);
        }

        return (new ResourceModel())->getResourcesReportData($args);
    }

    public function getPostsViewsData($args = [])
    {
        if (false) {
            return $this->legacy->getPostsViewsData($args);
        }

        return (new ResourceModel())->getResourcesViewsData($args);
    }

    public function getPostsCommentsData($args = [])
    {
        if (false) {
            return $this->legacy->getPostsCommentsData($args);
        }

        return (new ResourceModel())->getResourcesCommentsData($args);
    }

    public function getPostsWordsData($args = [])
    {
        if (false) {
            return $this->legacy->getPostsWordsData($args);
        }

        return (new ResourceModel())->getResourcesWordsData($args);
    }

    public function getInitialPostDate($args = [])
    {
        if (false) {
            return $this->legacy->getInitialPostDate($args);
        }

        return (new ResourceModel())->getInitialResourceDate($args);
    }

    public function get404Data($args = [])
    {
        if (false) {
            return $this->legacy->get404Data($args);
        }

        return (new ResourceModel())->get404Data($args);
    }

    public function count404Data($args = [])
    {
        if (false) {
            return $this->legacy->count404Data($args);
        }

        return (new ResourceModel())->count404Data($args);
    }
}
