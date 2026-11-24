<?php

namespace WP_Statistics\Tests\Views;

use WP_Statistics\Components\View;
use WP_Statistics\Decorators\ReferralDecorator;
use WP_UnitTestCase;

/**
 * The referrer cell in the visitor tables (Latest Visitors and the like) shows the source
 * name for a paid click that came without a referrer (Microsoft Ads, OpenAI Ads), and is
 * unchanged for rows with a referrer and for direct traffic. All values are made up.
 */
class Test_ReferrerLinkView extends WP_UnitTestCase
{
    private function render($referred, $channel, $name)
    {
        $referral = new ReferralDecorator((object)[
            'referred'       => $referred,
            'source_channel' => $channel,
            'source_name'    => $name,
        ]);

        return View::load('components/objects/referrer-link', [
            'label'       => $referral->getSourceChannel(),
            'url'         => $referral->getReferrer(),
            'title'       => $referral->getRawReferrer(),
            'source_name' => $referral->getSourceNameWithoutReferrer(),
        ], true);
    }

    public function test_paid_click_without_referrer_shows_source_name()
    {
        $html = $this->render('', 'paid_search', 'Microsoft Ads');

        $this->assertStringContainsString('<span title="Microsoft Ads">Microsoft Ads</span>', $html);
        $this->assertStringNotContainsString('<a ', $html);
        $this->assertStringContainsString('wps-referral-label', $html);

        $html = $this->render(null, 'paid_other', 'OpenAI Ads');
        $this->assertStringContainsString('>OpenAI Ads</span>', $html);
    }

    public function test_row_with_referrer_keeps_the_link()
    {
        $html = $this->render('bing.com', 'paid_search', 'Microsoft Ads');

        $this->assertStringContainsString('<a ', $html);
        $this->assertStringContainsString('>bing.com</span>', $html);
        $this->assertStringNotContainsString('Microsoft Ads', $html);
    }

    public function test_direct_traffic_shows_only_the_channel()
    {
        $html = $this->render('', 'direct', 'Direct');

        $this->assertStringNotContainsString('<a ', $html);
        $this->assertSame(1, substr_count($html, '<span'));
    }

    public function test_source_name_is_escaped()
    {
        $html = $this->render('', 'paid_other', '<script>x</script>');

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }
}
