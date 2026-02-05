<?php

namespace WP_Statistics\Service\Admin\PrivacyAudit\Audits;

use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\PrivacyAudit\Audits\Abstracts\ResolvableAudit;

class HashIpAddress extends ResolvableAudit
{
    public static $optionKey = 'store_ip';

    /**
     * Inverted logic: privacy is "passed" when store_ip is false (not storing raw IPs).
     */
    public static function isOptionPassed()
    {
        return !self::isOptionEnabled();
    }

    public static function getPassedStateInfo()
    {
        return [
            'title' => esc_html__('IP addresses are not being stored. Only anonymous hashes are used for visitor identification.', 'wp-statistics'),
            'notes' => View::load('components/privacy-audit/hash-ips', [], true),
        ];
    }

    public static function getUnpassedStateInfo()
    {
        return [
            'title'      => esc_html__('The "Store IP Addresses" feature is currently enabled. Raw IP addresses are being recorded.', 'wp-statistics'),
            'notes' => View::load('components/privacy-audit/hash-ips-unpassed', [], true),
        ];
    }
}
