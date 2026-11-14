<?php

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class Test_Pages_WooCommerce extends WP_UnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        // Declared here, not at file scope: setUp() runs only in the separate test process, so the
        // PHPUnit parent process and other tests never see these stubs. The namespaced functions win
        // over real WooCommerce functions when WooCommerce is loaded.
        if (!class_exists('WooCommerce')) {
            eval('class WooCommerce {}'); // phpcs:ignore Squiz.PHP.Eval.Discouraged
        }

        if (!function_exists('WP_STATISTICS\\is_product')) {
            eval('namespace WP_STATISTICS;
                function is_product() { return !empty($GLOBALS["wp_statistics_test_is_product"]); }
                function is_shop() { return !empty($GLOBALS["wp_statistics_test_is_shop"]); }
                function wc_get_page_id($page) { return $page === "shop" ? $GLOBALS["wp_statistics_test_shop_page_id"] : -1; }'); // phpcs:ignore Squiz.PHP.Eval.Discouraged
        }

        $GLOBALS['wp_statistics_test_is_product']   = false;
        $GLOBALS['wp_statistics_test_is_shop']      = false;
        $GLOBALS['wp_statistics_test_shop_page_id'] = 0;
    }

    public function tearDown(): void
    {
        unset(
            $GLOBALS['wp_statistics_test_is_product'],
            $GLOBALS['wp_statistics_test_is_shop'],
            $GLOBALS['wp_statistics_test_shop_page_id']
        );

        parent::tearDown();
    }

    public function test_get_page_type_records_woocommerce_product_search()
    {
        $GLOBALS['wp_statistics_test_is_shop'] = true;

        $this->go_to('/?post_type=product&s=night+cream');

        $this->assertTrue(is_search());
        $this->assertSame(
            ['type' => 'search', 'id' => 0, 'search_query' => 'night cream'],
            WP_STATISTICS\Pages::get_page_type()
        );
    }

    public function test_get_page_type_keeps_empty_woocommerce_search_as_shop_page()
    {
        $GLOBALS['wp_statistics_test_is_shop']      = true;
        $GLOBALS['wp_statistics_test_shop_page_id'] = 123;

        $this->go_to('/?post_type=product&s=');

        $this->assertTrue(is_search());
        $this->assertSame(
            ['type' => 'page', 'id' => 123, 'search_query' => ''],
            WP_STATISTICS\Pages::get_page_type()
        );
    }
}
