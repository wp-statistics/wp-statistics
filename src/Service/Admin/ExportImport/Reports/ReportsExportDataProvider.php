<?php
namespace WP_Statistics\Service\Admin\ExportImport\Reports;

use WP_Statistics\Service\Admin\PageInsights\PageInsightsDataProvider;
use WP_Statistics\Service\Admin\VisitorInsights\VisitorInsightsDataProvider;

class ReportsExportDataProvider
{
    protected $page;
    protected $report;
    protected $args = [];

    public function __construct($page, $report, $args = [])
    {
        $this->page   = $page;
        $this->report = $report;
        $this->args   = $args;
    }

    public function getData()
    {
        $handlerMethod = 'get'. str_replace(['_', '-'], '', $this->page) . 'Data';

        if (!method_exists($this, $handlerMethod)) {
            return [];
        }

        return $this->$handlerMethod();
    }

    protected function getVisitorsData()
    {
        $dataProvider = new VisitorInsightsDataProvider($this->args);

        if ($this->report === 'visitors') {
            $data = $dataProvider->getVisitorsData();
            return ReportsExportDataTransformer::transformVisitorsData($data['data']);
        }

        if ($this->report === 'views') {
            $data = $dataProvider->getViewsData();
            return ReportsExportDataTransformer::transformVisitorsData($data['data']);
        }

        if ($this->report === 'online') {
            $data = $dataProvider->getOnlineVisitorsData();
            return ReportsExportDataTransformer::transformVisitorsData($data['data']);
        }

        if ($this->report === 'top-visitors') {
            $data = $dataProvider->getTopVisitorsData();
            return ReportsExportDataTransformer::transformVisitorsData($data['data']);
        }

        if ($this->report === 'logged-in-users') {
            $data = $dataProvider->getLoggedInUsersData();
            return ReportsExportDataTransformer::transformVisitorsData($data['data']);
        }

        return [];
    }

    protected function getPagesData()
    {
        $args = wp_parse_args($this->args, [
            'order'     => 'DESC',
            'taxonomy'  => 'category'
        ]);

        $dataProvider = new PageInsightsDataProvider($args);

        if ($this->report === 'top') {
            $data = $dataProvider->getTopData();
            return ReportsExportDataTransformer::transformPostsData($data['posts']);
        }

        if ($this->report === 'category') {
            $data = $dataProvider->getCategoryData();
            return ReportsExportDataTransformer::transformCategoriesData($data['categories']);
        }

        if ($this->report === 'author') {
            $data = $dataProvider->getAuthorsData();
            return ReportsExportDataTransformer::transformAuthorsData($data['authors']);
        }

        if ($this->report === '404') {
            $data = $dataProvider->get404Data();
            return $data['data'];
        }

        return [];
    }
}