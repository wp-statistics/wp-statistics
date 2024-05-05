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
use InvalidArgumentException;

class PrivacyAuditCheck
{
    /**
     * Get list of privacy faq items
     * 
     * @return AbstractFaq[] $faqs
     */
    public static function getFaqs()
    {
        $faqs = [
            RequireConsent::class,
            RequireCookieBanner::class,
            TransferData::class,
            RequireMention::class
        ];

        return apply_filters('wp_statistics_privacy_faqs_list', $faqs);
    }

    
    /**
     * Get list of all privacy audit items
     * 
     * @return AbstractAudit[] $audits
     */
    public static function getAudits()
    {
        $audits = [
            'record_user_page_visits'       => RecordUserPageVisits::class,
            'anonymize_ip_address'          => AnonymizeIpAddress::class,
            'hash_ip_address'               => HashIpAddress::class,
            'store_user_agent_string'       => StoreUserAgentString::class,
            'stored_user_agent_string_data' => StoredUserAgentStringData::class,
            'unhashed_ip_address'           => UnhashedIpAddress::class,
            'stored_user_id_data'           => StoredUserIdData::class,
        ];

        return apply_filters('wp_statistics_privacy_audits_list', $audits);
    }


    /**
     * Find privacy audit class by name
     * 
     * @param string $auditName
     * @return AbstractAudit $auditClass
     * @throws InvalidArgumentException if audit class is not found.
     */
    public static function getAudit($auditName)
    {
        $audits = self::getAudits();
        
        if (!isset($audits[$auditName])) {
            throw new InvalidArgumentException(esc_html__("$auditName is not a valid audit item.", 'wp-statistics'));
        }

        return $audits[$auditName];
    }


    /**
     * Get privacy audits status
     * 
     * @return array $audits
     */
    public static function auditListStatus()
    {
        $audits = self::getAudits();
        $list   = [];

        foreach ($audits as $key => $audit) {
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


    /**
     * Get privacy faqs status
     * 
     * @return array $faqs
     */
    public static function faqListStatus()
    {
        $faqs = self::getFaqs();
        $list = [];

        foreach ($faqs as $faq) {
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


    /**
     * Get privacy compliance status
     * 
     * @return array $complianceStatus
     */
    public static function complianceStatus()
    {
        $audits         = self::getAudits();
        $rulesMapped    = count($audits);
        $actionRequired = 0;
        $passed         = 0;

        foreach ($audits as $audit) {
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

}
