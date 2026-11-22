<?php

use WP_STATISTICS\Admin_Template;
use WP_Statistics\Globals\Context;

/**
 * The Referrals > Google Search tab is shown (locked) while Marketing is off. Opening it must not
 * call Marketing classes: before the fix the header died on a missing AuthHelper class.
 */
class Test_TabbedHeaderWithoutMarketing extends WP_UnitTestCase
{
    public function test_last_updated_tab_renders_without_the_marketing_add_on()
    {
        $this->assertFalse(class_exists('WP_Statistics\Marketing\Services\Auth\AuthHelper', false), 'Marketing must be off for this test.');

        wp_set_current_user(self::factory()->user->create(['role' => 'administrator']));
        Context::update('wps_page', ['slug' => 'referrals', 'tab' => 'google-search']);

        $html = Admin_Template::get_template('layout/tabbed-page-header', [
            'title'      => 'Referrals',
            'active_tab' => 'google-search',
            'filters'    => [],
            'tabs'       => [[
                'id'                 => 'google-search',
                'link'               => admin_url('admin.php?page=wps_referrals_page&tab=google-search'),
                'title'              => 'Google Search',
                'class'              => 'current',
                'lastUpdated'        => true,
                'lastUpdatedTooltip' => 'Search Console data updates once a day.',
                'locked'             => true,
            ]],
        ], true);

        $this->assertStringContainsString('Google Search', $html);
        $this->assertStringNotContainsString('wps-last-updated', $html);
    }
}
