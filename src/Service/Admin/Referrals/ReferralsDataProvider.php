<?php 

namespace WP_Statistics\Service\Admin\Referrals;

class ReferralsDataProvider
{
    protected $args;
    
    public function __construct($args)
    {
        $this->args = $args;
    }
}