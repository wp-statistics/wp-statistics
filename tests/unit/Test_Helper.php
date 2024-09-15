<?php

namespace WP_Statistics\Tests;

use WP_STATISTICS\Helper;
use WP_UnitTestCase;

class Test_Helper extends WP_UnitTestCase
{
    /**
     * Test that makeAnonymousVersion works with a standard version number.
     */
    public function test_make_anonymous_version_standard()
    {
        $version  = '106.2.124.0';
        $expected = '106.0.0.0';

        $this->assertEquals($expected, Helper::makeAnonymousVersion($version), 'Failed to anonymize version with standard format.');
    }

    /**
     * Test that makeAnonymousVersion works with a version number with fewer sections.
     */
    public function test_make_anonymous_version_fewer_sections()
    {
        $version  = '106.2';
        $expected = '106.0';

        $this->assertEquals($expected, Helper::makeAnonymousVersion($version), 'Failed to anonymize version with fewer sections.');
    }
}
