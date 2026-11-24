<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\Exclusion;

class Test_ExclusionReasonLabels extends WP_UnitTestCase
{
    public function test_known_reasons_use_the_translatable_labels()
    {
        $this->assertSame('IP Match', Exclusion::get_reason_label('ip match'));
        $this->assertSame('Robot threshold', Exclusion::get_reason_label('robot_threshold'));
        $this->assertSame('XML-RPC', Exclusion::get_reason_label('xmlrpc'));
        $this->assertSame('Broken Link', Exclusion::get_reason_label('brokenfile'));
        $this->assertSame('Cross site Request', Exclusion::get_reason_label('cross site'));
    }

    public function test_labels_follow_translations()
    {
        $filter = function ($translation, $text, $domain) {
            return ($domain === 'wp-statistics' && $text === 'Robot') ? 'Roboter' : $translation;
        };
        add_filter('gettext', $filter, 10, 3);

        $this->assertSame('Roboter', Exclusion::get_reason_label('robot'));

        remove_filter('gettext', $filter, 10);
    }

    public function test_unknown_reasons_fall_back_to_title_case()
    {
        $this->assertSame('Custom Rule', Exclusion::get_reason_label('custom_rule'));
    }
}
