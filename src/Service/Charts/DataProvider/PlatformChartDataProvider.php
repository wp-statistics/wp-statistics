<?php
namespace WP_Statistics\Service\Charts\DataProvider;

use WP_Statistics\Service\Charts\AbstractChartDataProvider;
use WP_Statistics\Service\Charts\ChartDataProviderFactory;

class PlatformChartDataProvider extends AbstractChartDataProvider
{
    public function getOsData()
    {
        return ChartDataProviderFactory::osChart($this->args)->getData();
    }

    public function getBrowserData()
    {
        return ChartDataProviderFactory::browserChart($this->args)->getData();
    }

    public function getDeviceData()
    {
        return ChartDataProviderFactory::deviceChart($this->args)->getData();
    }

    public function getModelData()
    {
        return ChartDataProviderFactory::modelChart($this->args)->getData();
    }
}