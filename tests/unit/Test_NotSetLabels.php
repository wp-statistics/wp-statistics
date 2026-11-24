<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\Admin_Template;
use WP_STATISTICS\Country;
use WP_Statistics\Service\Charts\DataProvider\CountryChartDataProvider;

/**
 * "(not set)" is the one label for a missing value (device model, country), and a
 * country list has one "(not set)" row, placed by its visitor count.
 */
class Test_NotSetLabels extends WP_UnitTestCase
{
    public function test_models_tab_prints_not_set_for_a_missing_model()
    {
        $html = Admin_Template::get_template(['pages/devices/models'], [
            'data' => [
                'visitors' => [(object)['model' => '', 'visitors' => 3], (object)['model' => 'Unknown', 'visitors' => 2], (object)['model' => 'Pixel 9', 'visitors' => 1]],
                'visits'   => 6,
            ],
        ], true);

        $this->assertStringContainsString('(not set)', $html);
        $this->assertStringNotContainsString('Unknown', $html);
        $this->assertStringContainsString('Pixel 9', $html);
    }

    public function test_countries_list_has_one_not_set_row_in_count_order()
    {
        $rows = CountryChartDataProvider::mergeUnknownCountries([
            (object)['country' => 'DE', 'visitors' => 10],
            (object)['country' => 'FR', 'visitors' => 4],
            (object)['country' => '', 'visitors' => 3],
            (object)['country' => 'NL', 'visitors' => 2],
            (object)['country' => Country::$unknown_location, 'visitors' => 3],
        ]);

        $codes = array_map(function ($row) {
            return $row->country;
        }, $rows);

        $this->assertSame(['DE', Country::$unknown_location, 'FR', 'NL'], $codes, 'merged (not set) row (6) sorts by its count');
    }
}
