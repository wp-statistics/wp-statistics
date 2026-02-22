<?php

namespace WP_Statistics\Tests\EmailReport;

use WP_Statistics\Components\View;
use WP_Statistics\Service\EmailReport\EmailReportRenderer;
use WP_UnitTestCase;

class Test_EmailReportRenderer extends WP_UnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        remove_all_filters('wp_statistics_email_report_sections');
    }

    public function tearDown(): void
    {
        remove_all_filters('wp_statistics_email_report_sections');
        parent::tearDown();
    }

    public function test_new_section_slugs_render_when_data_exists()
    {
        add_filter('wp_statistics_email_report_sections', function () {
            return ['engagement-overview', 'top-entry-pages', 'top-exit-pages', 'top-countries', 'devices'];
        });

        $renderer = new EmailReportRenderer();
        $output   = $renderer->render([
            'engagement_kpis'  => [
                ['label' => 'Sessions', 'value' => '120', 'change_percent' => 12.5],
            ],
            'top_entry_pages'  => [
                ['label' => 'Homepage', 'value' => '95'],
            ],
            'top_exit_pages'   => [
                ['label' => 'Checkout', 'value' => '42'],
            ],
            'top_countries'    => [
                ['label' => 'United States', 'value' => '88'],
            ],
            'device_breakdown' => [
                'types'             => [['label' => 'Desktop', 'value' => '60']],
                'browsers'          => [['label' => 'Chrome', 'value' => '50']],
                'operating_systems' => [['label' => 'Windows', 'value' => '45']],
            ],
        ]);

        $this->assertStringContainsString('Engagement Overview', $output);
        $this->assertStringContainsString('Top Entry Pages', $output);
        $this->assertStringContainsString('Top Exit Pages', $output);
        $this->assertStringContainsString('Top Countries', $output);
        $this->assertStringContainsString('Device Breakdown', $output);
    }

    public function test_new_section_slugs_render_empty_when_data_missing()
    {
        add_filter('wp_statistics_email_report_sections', function () {
            return ['engagement-overview', 'top-entry-pages', 'top-exit-pages', 'top-countries', 'devices'];
        });

        $renderer = new EmailReportRenderer();
        $output   = $renderer->render([
            'engagement_kpis'  => [],
            'top_entry_pages'  => [],
            'top_exit_pages'   => [],
            'top_countries'    => [],
            'device_breakdown' => [
                'types'             => [],
                'browsers'          => [],
                'operating_systems' => [],
            ],
        ]);

        $this->assertSame('', trim($output));
    }

    public function test_data_table_handles_optional_change_and_share_columns()
    {
        $template = WP_STATISTICS_DIR . 'views/emails/partials/data-table.php';

        $withColumns = View::renderFile($template, [
            'title'           => 'Top Countries',
            'column_label'    => 'Country',
            'value_label'     => 'Visitors',
            'show_comparison' => true,
            'rows'            => [
                ['label' => 'USA', 'value' => '120', 'change_percent' => 12.5, 'share_percent' => 60.0],
                ['label' => 'Canada', 'value' => '80', 'change_percent' => -4.2, 'share_percent' => 40.0],
            ],
        ]);

        $withoutColumns = View::renderFile($template, [
            'title'           => 'Top Countries',
            'column_label'    => 'Country',
            'value_label'     => 'Visitors',
            'show_comparison' => true,
            'rows'            => [
                ['label' => 'USA', 'value' => '120'],
            ],
        ]);

        $this->assertStringContainsString('Change', $withColumns);
        $this->assertStringContainsString('Share', $withColumns);
        $this->assertStringContainsString('+12.5%', $withColumns);
        $this->assertStringContainsString('-4.2%', $withColumns);
        $this->assertStringContainsString('60.0%', $withColumns);

        $this->assertStringNotContainsString('Change', $withoutColumns);
        $this->assertStringNotContainsString('Share', $withoutColumns);
    }
}
