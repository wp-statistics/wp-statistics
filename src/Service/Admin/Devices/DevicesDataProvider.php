<?php

namespace WP_Statistics\Service\Admin\Devices;

use WP_STATISTICS\Helper;
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
        $args = array_merge($this->args, [
            'field'    => 'agent',
            'group_by' => ['agent'],
        ]);

        return [
            'visitors' => $this->visitorsModel->getVisitorsDevices($args),
            'total'    => $this->visitorsModel->countColumnDistinct($args),
            'visits'   => $this->visitorsModel->countColumnDistinct(array_merge($args, ['field' => 'ID'])),
        ];
    }

    /**
     * Returns data for "Operating Systems" tab.
     *
     * @return  array
     */
    public function getPlatformsData()
    {
        $args = array_merge($this->args, [
            'field'    => 'platform',
            'group_by' => ['platform'],
        ]);

        return [
            'visitors' => $this->visitorsModel->getVisitorsDevices($args),
            'total'    => $this->visitorsModel->countColumnDistinct($args),
            'visits'   => $this->visitorsModel->countColumnDistinct(array_merge($args, ['field' => 'ID'])),
        ];
    }

    /**
     * Returns data for "Device Models" tab.
     *
     * @return  array
     */
    public function getModelsData()
    {
        $args = array_merge($this->args, [
            'field'    => 'model',
            'group_by' => ['model']
        ]);

        return [
            'visitors' => $this->visitorsModel->getVisitorsDevices(array_merge($args, ['where_not_null' => 'model'])),
            'total'    => $this->visitorsModel->countColumnDistinct($args),
            'visits'   => $this->visitorsModel->countColumnDistinct(array_merge($args, ['field' => 'ID'])),
        ];
    }

    /**
     * Returns data for "Device Models" tab.
     *
     * @return  array
     */
    public function getCategoriesData()
    {
        $args = array_merge($this->args, [
            'field'    => 'device',
            'group_by' => ['device']
        ]);

        $visitors = [];

        $data = $this->visitorsModel->getVisitorsDevices(array_merge($args, ['where_not_null' => 'device']));
        foreach ($data as $visitor) {
            if (!empty(trim($visitor->device)) && strtolower($visitor->device) != "bot") {
                $device = Helper::getDeviceCategoryName($visitor->device);
                if (isset($visitors[$device])) {
                    $visitors[$device]->visitors += $visitor->visitors;
                } else {
                    $visitors[$device] = json_decode(json_encode(array(
                        'device'   => ucfirst($device),
                        'visitors' => $visitor->visitors,
                    )));
                }
            }
        }

        return [
            'visitors' => array_filter($visitors),
            'total'    => $this->visitorsModel->countColumnDistinct($args),
            'visits'   => $this->visitorsModel->countColumnDistinct(array_merge($args, ['field' => 'ID', 'where_not_null' => 'device'])),
        ];
    }

    /**
     * Returns data for browser's single page.
     *
     * @param string $selectedBrowser
     *
     * @return  array
     */
    public function getSingleBrowserData($selectedBrowser)
    {
        $args = array_merge($this->args, [
            'field'     => 'agent',
            'where_col' => 'agent',
            'where_val' => esc_sql($selectedBrowser),
        ]);

        return [
            'visitors' => $this->visitorsModel->getVisitorsDevicesVersions($args),
            'total'    => $this->visitorsModel->countColumnDistinct(array_merge($this->args, ['field' => 'agent'])),
            'visits'   => $this->visitorsModel->countColumnDistinct(array_merge($args, ['field' => 'ID'])),
        ];
    }

    /**
     * Returns data for platform's single page.
     *
     * @param string $selectedPlatform
     *
     * @return  array
     */
    public function getSinglePlatformData($selectedPlatform)
    {
        $args = array_merge($this->args, [
            'field'     => 'platform',
            'where_col' => 'platform',
            'where_val' => esc_sql($selectedPlatform),
        ]);

        return [
            'visitors' => $this->visitorsModel->getVisitorsDevicesVersions($args),
            'total'    => $this->visitorsModel->countColumnDistinct(array_merge($this->args, ['field' => 'platform'])),
            'visits'   => $this->visitorsModel->countColumnDistinct(array_merge($args, ['field' => 'ID'])),
        ];
    }

    /**
     * Returns data for model's single page.
     *
     * @param string $selectedModel
     *
     * @return  array
     */
    public function getSingleModelData($selectedModel)
    {
        $args = array_merge($this->args, [
            'field'     => 'model',
            'where_col' => 'model',
            'where_val' => esc_sql($selectedModel),
        ]);

        return [
            'visitors' => $this->visitorsModel->getVisitorsDevicesVersions($args),
            'total'    => $this->visitorsModel->countColumnDistinct(array_merge($this->args, ['field' => 'model'])),
            'visits'   => $this->visitorsModel->countColumnDistinct(array_merge($args, ['field' => 'ID'])),
        ];
    }
}
