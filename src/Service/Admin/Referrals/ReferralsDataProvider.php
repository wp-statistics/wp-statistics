<?php

namespace WP_Statistics\Service\Admin\Referrals;
use WP_Statistics\Models\VisitorsModel;

class ReferralsDataProvider
{
    protected $args;
    private $visitorsModel;

    public function __construct($args)
    {
        $this->args = $args;

        $this->visitorsModel = new VisitorsModel();
    }

    public function getReferredVisitors()
    {
        return [
            'visitors' => $this->visitorsModel->getReferredVisitors($this->args)
        ];
    }
}