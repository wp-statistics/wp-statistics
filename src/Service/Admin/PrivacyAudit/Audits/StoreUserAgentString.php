<?php

namespace WP_Statistics\Service\Admin\PrivacyAudit\Audits;

use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\PrivacyAudit\Audits\Abstracts\ResolvableAudit;

class StoreUserAgentString extends ResolvableAudit
{
    public static $optionKey = 'store_ua';

    public static function isOptionPassed()
    {
        // If option is disabled, consider it passed.
        return !self::isOptionEnabled();
    }

    public static function getPassedStateInfo()
    {
        return [
            'title' => esc_html__('The “Store Entire User Agent String” feature is currently disabled on your website.', 'wp-statistics'),
            'notes' => View::load('components/privacy-audit/stored-user-agent', [], true),
        ];
    }

    public static function getUnpassedStateInfo()
    {
        return [
            'title' => esc_html__('The “Store Entire User Agent String” feature is currently enabled on your website.', 'wp-statistics'),
            'notes' => View::load('components/privacy-audit/stored-user-agent-unpassed', [], true),
        ];
    }

}