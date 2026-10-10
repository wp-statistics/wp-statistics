<?php

namespace WP_Statistics\Tests\Exclusion;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Exclusion;
use WP_UnitTestCase;

class Test_CrossSiteExclusion extends WP_UnitTestCase
{
    public function tearDown(): void
    {
        unset($_SERVER['HTTP_SEC_FETCH_SITE'], $_SERVER['HTTP_SEC_FETCH_MODE']);
        parent::tearDown();
    }

    /**
     * A visitor arriving from a link on another site (Google, Facebook, an ad)
     * sends Sec-Fetch-Site: cross-site with Sec-Fetch-Mode: navigate. That is a real visit.
     */
    public function test_cross_site_navigation_is_not_excluded()
    {
        $_SERVER['HTTP_SEC_FETCH_SITE'] = 'cross-site';
        $_SERVER['HTTP_SEC_FETCH_MODE'] = 'navigate';

        $this->assertFalse(Exclusion::exclusion_cross_site());
    }

    public function test_cross_site_background_request_is_excluded()
    {
        $_SERVER['HTTP_SEC_FETCH_SITE'] = 'cross-site';

        foreach (['cors', 'no-cors', 'same-origin'] as $mode) {
            $_SERVER['HTTP_SEC_FETCH_MODE'] = $mode;
            $this->assertTrue(Exclusion::exclusion_cross_site(), $mode);
        }

        unset($_SERVER['HTTP_SEC_FETCH_MODE']);
        $this->assertTrue(Exclusion::exclusion_cross_site());
    }

    public function test_same_origin_and_missing_header_are_not_excluded()
    {
        $this->assertFalse(Exclusion::exclusion_cross_site());

        foreach (['same-origin', 'same-site', 'none'] as $site) {
            $_SERVER['HTTP_SEC_FETCH_SITE'] = $site;
            $_SERVER['HTTP_SEC_FETCH_MODE'] = 'cors';
            $this->assertFalse(Exclusion::exclusion_cross_site(), $site);
        }
    }
}
