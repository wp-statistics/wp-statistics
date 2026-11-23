<?php

namespace WP_Statistics\Tests\Views;

use WP_Statistics\Components\View;
use WP_UnitTestCase;

/**
 * The "Statistics Summary" meta box on the post edit screen: labelled rows with
 * plural-safe counts, the 7-day row only for posts older than a week, a chart caption
 * with the peak day, and no `exit` when there is no summary. All values are made up.
 */
class Test_PostSummaryView extends WP_UnitTestCase
{
    private function summary($overrides = [])
    {
        return array_merge([
            'postId'                     => 1,
            'fromString'                 => 'Nov 14',
            'toString'                   => 'Nov 20',
            'publishDateString'          => gmdate('Y-m-d H:i:s', strtotime('-30 days')),
            'totalVisitors'              => 1308,
            'totalViews'                 => 1531,
            'topReferrer'                => 'https://www.example.org/some/page',
            'topReferrerCount'           => 182,
            'thisPeriodVisitors'         => 1,
            'thisPeriodViews'            => 60,
            'thisPeriodTopReferrer'      => 'https://example.net/',
            'thisPeriodTopReferrerCount' => 8,
            'postChartData'              => [
                '14 Nov' => ['ymdDate' => '2026-11-14', 'hits' => 3, 'fullDate' => 'November 14, 2026'],
                '15 Nov' => ['ymdDate' => '2026-11-15', 'hits' => 27, 'fullDate' => 'November 15, 2026'],
                '16 Nov' => ['ymdDate' => '2026-11-16', 'hits' => 0, 'fullDate' => 'November 16, 2026'],
            ],
            'postChartSettings'          => ['color' => '#404BF2', 'label' => 'Views', 'metric' => 'views'],
            'contentAnalyticsUrl'        => 'https://example.test/wp-admin/admin.php?page=wps_content-analytics_page',
        ], $overrides);
    }

    private function render($summary)
    {
        return View::load('components/meta-box/post-summary', ['summary' => $summary], true);
    }

    public function test_rows_use_plural_safe_counts_and_referrer_hosts()
    {
        $html = $this->render($this->summary());

        $this->assertStringContainsString('Last 7 days · Nov 14 - Nov 20', $html);
        $this->assertStringContainsString('<b>60</b> views', $html);
        $this->assertMatchesRegularExpression('#<b>1</b> visitor\s#', $html);
        $this->assertStringContainsString('All time', $html);
        $this->assertStringContainsString('<b>1,531</b> views', $html);
        $this->assertStringContainsString('>example.net</a> (8)', $html);
        $this->assertStringContainsString('>example.org</a> (182)', $html);
        $this->assertStringContainsString('View full analytics', $html);
    }

    public function test_seven_day_row_is_hidden_for_posts_younger_than_a_week()
    {
        $html = $this->render($this->summary(['publishDateString' => gmdate('Y-m-d H:i:s', strtotime('-2 days'))]));

        $this->assertStringNotContainsString('Last 7 days', $html);
        $this->assertStringContainsString('All time', $html);
    }

    public function test_chart_caption_names_the_peak_day()
    {
        $html = $this->render($this->summary());

        $this->assertStringContainsString('Views per day', $html);
        $this->assertStringContainsString('Peak 27 on November 15, 2026', $html);
        $this->assertStringContainsString('id="postSummaryChart"', $html);
    }

    public function test_no_traffic_shows_an_empty_caption_and_no_referrer()
    {
        $chart = $this->summary()['postChartData'];
        foreach ($chart as $key => $day) {
            $chart[$key]['hits'] = 0;
        }

        $html = $this->render($this->summary([
            'totalViews'       => 0,
            'totalVisitors'    => 0,
            'topReferrerCount' => 0,
            'postChartData'    => $chart,
            'publishDateString' => gmdate('Y-m-d H:i:s'),
        ]));

        $this->assertStringContainsString('No views in this period', $html);
        $this->assertStringContainsString('<b>0</b> views', $html);
        $this->assertStringNotContainsString('Top referrer', $html);
    }

    public function test_empty_summary_renders_a_message_instead_of_exiting()
    {
        $html = $this->render(null);

        $this->assertStringContainsString('o-wrap--no-data', $html);
    }
}
