<?php

namespace WP_Statistics\Service\Admin\LicenseManager;

class LicenseManagerDataProvider
{
    protected $args;

    public function __construct($args = [])
    {
        $this->args = $args;
    }

    /**
     * Returns data for step 1.
     *
     * @return array
     */
    public function getStepOneData()
    {
        return ['step' => 1];
    }

    /**
     * Returns data for step 2.
     *
     * @return array
     */
    public function getStepTwoData()
    {
        return ['step' => 2];
    }

    /**
     * Returns data for step 3.
     *
     * @return array
     */
    public function getStepThreeData()
    {
        return ['step' => 3];
    }
}
