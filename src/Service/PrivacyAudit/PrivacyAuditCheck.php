<?php

namespace WP_Statistics\Service\PrivacyAudit;

use WP_Statistics\Service\PrivacyAudit\Audits\AbstractAudit;
use WP_Statistics\Service\PrivacyAudit\Audits\AnonymizeIpAddress;
use WP_Statistics\Service\PrivacyAudit\Audits\RecordUserPageVisits;
use WP_Statistics\Service\PrivacyAudit\Audits\HashIpAddress;
use WP_Statistics\Service\PrivacyAudit\Audits\StoreUserAgentString;
use WP_Statistics\Service\PrivacyAudit\Audits\StoredUserAgentStringData;
use WP_Statistics\Service\PrivacyAudit\Audits\UnhashedIpAddress;
use WP_Statistics\Service\PrivacyAudit\Audits\StoredUserIdData;
use WP_Statistics\Service\PrivacyAudit\Faqs\AbstractFaq;
use WP_Statistics\Service\PrivacyAudit\Faqs\RequireMention;
use WP_Statistics\Service\PrivacyAudit\Faqs\RequireConsent;
use WP_Statistics\Service\PrivacyAudit\Faqs\RequireCookieBanner;
use WP_Statistics\Service\PrivacyAudit\Faqs\TransferData;

class PrivacyAuditCheck
{
    /** @var AbstractAudit[] $audits */
    public static $audits = [
        'record_user_page_visits'       => RecordUserPageVisits::class,
        'anonymize_ip_address'          => AnonymizeIpAddress::class,
        'hash_ip_address'               => HashIpAddress::class,
        'store_user_agent_string'       => StoreUserAgentString::class,
        'stored_user_agent_string_data' => StoredUserAgentStringData::class,
        'unhashed_ip_address'           => UnhashedIpAddress::class,
        'stored_user_id_data'           => StoredUserIdData::class,
    ];

    /** @var AbstractFaq[] $faqs */
    public static $faqs = [
        RequireConsent::class,
        // RequireCookieBanner::class,
        // TransferData::class,
        // RequireMention::class
    ];

    public static function auditListStatus()
    {
        $list = [];

        foreach (self::$audits as $key => $audit) {
            $auditState = $audit::getState();

            // If current state data is not available, skip
            if (empty($auditState)) continue;

            $auditItem = [
                'name'      => $key, 
                'title'     => $auditState['title'], 
                'notes'     => $auditState['notes'],
                'status'    => $auditState['status'], 
                'compliance'=> $auditState['compliance'],
            ];

            // If audit has action in the current state, add it to the audit item array.
            if (!empty($auditState['action'])) {
                $auditItem['action'] = $auditState['action'];
            }

            $list[] = $auditItem;
        }

        return $list;
    }

    public static function complianceStatus()
    {
        $rulesMapped    = count(self::$audits);
        $actionRequired = 0;
        $passed         = 0;

        foreach (self::$audits as $audit) {
            $audit::getStatus() == 'passed' ? $passed++ : $actionRequired++;
        }

        return [
            'percentage_ready'  => floor(($passed / $rulesMapped) * 100),
            'rules_mapped'      => $rulesMapped,
            'summary'           => [
                'passed'          => $passed,
                'action_required' => $actionRequired
            ]
        ];
    }

    public static function faqListStatus()
    {
        $list = [];

        foreach (self::$faqs as $key => $faq) {
            $faq = $faq::getState();

            // If current state data is not available, skip
            if (empty($faq)) continue;

            $list[] = [
                'title'     => $faq['title'], 
                'summary'   => $faq['summary'], 
                'notes'     => $faq['notes'],
                'status'    => $faq['status']
            ];
        }

        return $list;
    }

}
