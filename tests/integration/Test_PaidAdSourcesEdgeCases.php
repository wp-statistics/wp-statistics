<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Service\Analytics\Referrals\SourceDetector;

/**
 * Edge cases for the paid ad source rows (OpenAI Ads, Microsoft Ads, Facebook Ads).
 * All URLs are made up.
 */
class Test_PaidAdSourcesEdgeCases extends WP_UnitTestCase
{
    private function detect($referrer, $page)
    {
        $d = new SourceDetector($referrer, $page);
        return [$d->getName(), $d->getChannel()];
    }

    /**
     * @dataProvider provider
     */
    public function test_classification($referrer, $page, $name, $channel)
    {
        $this->assertSame([$name, $channel], $this->detect($referrer, $page), "{$referrer} {$page}");
    }

    public function provider()
    {
        $ms  = ['Microsoft Ads', 'paid_search'];
        $oa  = ['OpenAI Ads', 'paid_other'];
        $fb  = ['Facebook Ads', 'paid_social'];
        $rows = [
            // msclkid positions and shapes
            ['', '/?msclkid=a', $ms], ['', '/?a=1&msclkid=a', $ms], ['', '/?msclkid=a&a=1', $ms],
            ['', '/?MSCLKID=a', $ms], ['', '/?MsClkId=a', $ms], ['', '/?msclkid=', $ms],
            ['', '/?msclkid=1&msclkid=2', $ms], ['', 'https://example.com/?msclkid=a', $ms],
            ['', '/?msclkid=' . str_repeat('a', 5000), $ms],
            ['', '/?msclkid=%E6%97%A5', $ms],
            ['', '/?%6Dsclkid=a', ['Direct', 'direct']],
            ['', '/?next=%3Fmsclkid%3D1', ['Direct', 'direct']],
            ['', '/?xmsclkid=1', ['Direct', 'direct']],
            ['', '/?msclkidx=1', ['Direct', 'direct']],
            // oppref
            ['', '/?oppref=a', $oa], ['https://chatgpt.com/', '/?oppref=a', $oa], ['', '/?OPPREF=a', $oa],
            ['', '/?x=1&oppref=a', $oa], ['https://www.google.com/', '/?oppref=a', $oa],
            ['', '/?xoppref=1', ['Direct', 'direct']],
            ['', '/?oppref', ['Direct', 'direct']],
            ['https://chatgpt.com/', '/', ['ChatGPT', 'ai_assistants']],
            // combos: first matching ad group wins
            ['', '/?gclid=g&msclkid=a', $ms], ['', '/?msclkid=a&utm_source=google', $ms],
            ['https://www.bing.com/', '/?msclkid=a', $ms], ['https://www.bing.com/', '/', ['Bing', 'search']],
            ['', '/?msclkid=a&oppref=b', $oa],
            ['https://www.google.com/', '/?gclid=a', ['Google Ads', 'paid_search']],
            ['https://www.google.com/', '/', ['Google', 'search']],
            // facebook
            ['https://l.facebook.com/', '/?utm_source=facebook&utm_medium=paid', $fb],
            ['https://m.facebook.com/', '/?utm_source=fb&utm_medium=cpc', $fb],
            ['https://lm.facebook.com/', '/?utm_source=FB&utm_medium=PPC', $fb],
            ['https://www.facebook.com/', '/?utm_source=Facebook&utm_medium=paid_social', $fb],
            ['https://facebook.com/', '/?utm_medium=retargeting&utm_source=facebook', $fb],
            ['https://a.b.facebook.com/', '/?utm_source=facebook&utm_medium=paid-social', $fb],
            ['https://evilfacebook.com/', '/?utm_source=facebook&utm_medium=paid', ['Facebook', 'paid_social']],
            ['https://facebook.com.evil.com/', '/?utm_source=facebook&utm_medium=paid', ['Facebook', 'paid_social']],
            ['https://facebook.com@evil.com/', '/?utm_source=facebook&utm_medium=paid', ['Facebook', 'paid_social']],
            ['https://l.facebook.com/', '/?utm_source=facebook&utm_medium=organic', ['Facebook', 'social']],
            ['https://l.facebook.com/', '/?utm_source=facebook', ['Facebook', 'social']],
            ['https://l.facebook.com/', '/?fbclid=a', ['Facebook', 'social']],
            ['', '/?utm_source=facebook&utm_medium=paid', ['Facebook', 'paid_social']],
            ['https://www.google.com/', '/?fbclid=a&utm_source=facebook&utm_medium=paid', ['Facebook', 'paid_social']],
            ['https://l.facebook.com/', '/?utm_source=facebook-ads&utm_medium=paid', ['facebook-ads', 'paid_other']],
            ['https://l.facebook.com/', '/?utm_source=facebookx&utm_medium=cpc', ['facebookx', 'paid_other']],
            // untouched
            ['', '/', ['Direct', 'direct']],
            ['', '/?utm_source=newsletter&utm_medium=email', ['newsletter', 'email']],
            ['', '/?utm_source=sms&utm_medium=sms', ['sms', 'sms']],
            ['', '/?utm_source=partner&utm_medium=affiliate', ['partner', 'affiliates']],
            ['https://www.linkedin.com/', '/?li_fat_id=x', ['LinkedIn', 'paid_social']],
        ];

        $out = [];
        foreach ($rows as $i => $r) {
            $out["#$i " . substr($r[1], 0, 60)] = [$r[0], $r[1], $r[2][0], $r[2][1]];
        }
        return $out;
    }

    public function test_null_and_malformed_inputs_do_not_fatal()
    {
        foreach ([['', ''], ['ht!tp:///???', '/'], ['javascript:alert(1)', '/?msclkid=1'], ['https://www.google.com/', '/?%%%&&&===']] as $pair) {
            $d = new SourceDetector($pair[0], $pair[1]);
            $this->assertTrue($d->getChannel() === null || is_string($d->getChannel()));
        }
    }

    public function test_getchannelname_for_new_rows()
    {
        $this->assertNotEmpty((new SourceDetector('', '/?msclkid=a'))->getChannelName());
        $this->assertNotEmpty((new SourceDetector('', '/?oppref=a'))->getChannelName());
    }
}
