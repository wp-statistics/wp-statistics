<?php

namespace WP_Statistics\Tests\Utils;

use WP_STATISTICS\IP;
use WP_UnitTestCase;

class Test_Utils extends WP_UnitTestCase
{
    /**
     * Test the check_sanitize_ip method with various inputs.
     *
     * @dataProvider ipProvider
     */
    public function test_checkSanitizeIp($input, $expected)
    {
        $this->assertEquals($expected, IP::check_sanitize_ip($input));
    }

    public function ipProvider()
    {
        return [
            ['192.168.1.1', true],              //Valid IPv4
            ['2001:db8::ff00:42:8329', true],   //Valid IPv6
            ['192.168.1.1 ', false],            //Trailing space
            [' 192.168.1.1', false],            //Leading space
            ['192.168.1.1!', false],            //Special char
            ['@192.168.1.1', false],            //Special char
            ['2001:db8::ff00:42:8329!', false], //Invalid IPv6
            ['invalid-ip', false],              //Completely wrong IP
            ['127.0.0.1', true],                //Valid IPv4 localhost
            ['::1', true],                      //Valid IPv6 localhost
        ];
    }
}
