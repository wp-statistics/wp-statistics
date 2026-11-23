<?php

namespace WP_Statistics\Tests\Unit;

use WP_Statistics\Decorators\ReferralDecorator;
use WP_Statistics\Service\Analytics\Referrals\SourceChannels;
use WP_UnitTestCase;

/**
 * Older or imported visitor rows can hold an empty string in source_channel instead of NULL.
 * Those, and unknown keys, must resolve to the unassigned label without PHP warnings.
 */
class Test_SourceChannels extends WP_UnitTestCase
{
    public function test_known_key_returns_its_name()
    {
        $this->assertSame('Organic Search', SourceChannels::getName('search'));
        $this->assertSame('Direct Traffic', SourceChannels::getName('direct'));
    }

    public function test_empty_string_is_treated_as_unassigned()
    {
        $this->assertSame('Unassigned Traffic', SourceChannels::getName(''));
    }

    public function test_null_is_treated_as_unassigned()
    {
        $this->assertSame('Unassigned Traffic', SourceChannels::getName(null));
    }

    public function test_unknown_key_is_treated_as_unassigned()
    {
        $this->assertSame('Unassigned Traffic', SourceChannels::getName('not_a_channel'));
    }

    public function test_falls_back_when_unassigned_is_filtered_out()
    {
        $filter = function ($channels) {
            unset($channels['unassigned']);
            return $channels;
        };
        add_filter('wp_statistics_source_channels_list', $filter);

        try {
            $this->assertSame('Unassigned Traffic', SourceChannels::getName(''));
        } finally {
            remove_filter('wp_statistics_source_channels_list', $filter);
        }
    }

    public function test_decorator_with_empty_source_channel_does_not_warn()
    {
        $decorator = new ReferralDecorator((object)['source_channel' => '', 'referred' => '']);

        $this->assertSame('Unassigned Traffic', $decorator->getSourceChannel());
    }
}
