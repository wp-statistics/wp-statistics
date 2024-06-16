<?php

namespace WP_Statistics\Service\Admin\Devices;

use WP_Statistics\Models\VisitorsModel;

class DevicesDataProvider
{
    protected $args;
    protected $visitorsModel;

    public function __construct($args)
    {
        $this->args = $args;

        $this->visitorsModel = new VisitorsModel();
    }

    /**
     * Returns data for "Browsers" tab.
     *
     * @return  array
     */
    public function getBrowsersData()
    {
        $args = array_merge($this->args, ['group_by' => ['agent']]);

        return [
            'visitors' => $this->visitorsModel->getVisitorsDevices($args),
            'total'    => $this->visitorsModel->countAllVisitors($args),
        ];
    }

    /**
     * Returns data for "Operating Systems" tab.
     *
     * @return  array
     */
    public function getPlatformsData()
    {
        $args = array_merge($this->args, ['group_by' => ['platform']]);

        return [
            'visitors' => $this->visitorsModel->getVisitorsDevices($args),
            'total'    => $this->visitorsModel->countAllVisitors($args),
        ];
    }

    /**
     * Returns data for "Device Models" tab.
     *
     * @return  array
     */
    public function getModelsData()
    {
        $args = array_merge($this->args, ['group_by' => ['model']]);

        return [
            'visitors' => $this->visitorsModel->getVisitorsDevices($args),
            'total'    => $this->visitorsModel->countAllVisitors($args),
        ];
    }

    /**
     * Returns data for model's single page.
     *
     * @param   string  $selectedModel
     *
     * @return  array
     */
    public function getSingleModelsData($selectedModel)
    {
        $args = array_merge($this->args, [
            'where_col' => 'model',
            'where_val' => esc_sql($selectedModel),
            'group_by'  => ['version'],
        ]);

        return [
            'visitors' => $this->visitorsModel->getVisitorsDevices($args),
            'total'    => $this->visitorsModel->countAllVisitors($args),
        ];
    }
}
