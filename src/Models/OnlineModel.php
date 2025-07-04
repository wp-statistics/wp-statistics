<?php

namespace WP_Statistics\Models;

use WP_Statistics\Abstracts\BaseModel;
use WP_Statistics\Models\Legacy\LegacyOnlineModel;

class OnlineModel extends BaseModel
{
    private $legacy;

    public function __construct()
    {
        $this->legacy = new LegacyOnlineModel();
    }

    public function countOnlines($args = [])
    {
        if (false) {
            return $this->legacy->countOnlines($args);
        }

        return (new SessionModel())->countOnlines($args);
    }

    public function getOnlineVisitorsData($args = [])
    {
        if (false) {
            return $this->legacy->getOnlineVisitorsData($args);
        }

        return (new SessionModel())->getOnlineVisitorsData($args);
    }
}