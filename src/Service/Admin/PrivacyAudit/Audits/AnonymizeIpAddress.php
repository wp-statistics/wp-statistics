<?php

namespace WP_Statistics\Service\Admin\PrivacyAudit\Audits;

use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\PrivacyAudit\Audits\Abstracts\ResolvableAudit;

class AnonymizeIpAddress extends ResolvableAudit
{
    public static $optionKey = 'anonymize_ips';

    public static function getPassedStateInfo()
    {
        return [
            'title' => esc_html__('The “Anonymize IP Addresses” feature is currently enabled on your website.', 'wp-statistics'),
            'notes' => View::load('components/privacy-audit/anonymize-ips', [], true),
        ];
    }

    public static function getUnpassedStateInfo()
    {
        return [
            'title' => esc_html__('The “Anonymize IP Addresses” feature is currently disabled on your website.', 'wp-statistics'),
            'notes' => View::load('components/privacy-audit/anonymize-ips-unpassed', [], true),
        ];
    }


}