<?php

namespace WP_Statistics\Service\Admin\PrivacyAudit;

use InvalidArgumentException;
use WP_STATISTICS\Menus;
use WP_Statistics\Service\Admin\PrivacyAudit\Audits\Abstracts\BaseAudit;
use WP_Statistics\Service\Admin\PrivacyAudit\Audits\Abstracts\ResolvableAudit;
use WP_Statistics\Service\Admin\PrivacyAudit\Audits\AnonymizeIpAddress;
use WP_Statistics\Service\Admin\PrivacyAudit\Audits\HashIpAddress;
use WP_Statistics\Service\Admin\PrivacyAudit\Audits\RecordUserPageVisits;
use WP_Statistics\Service\Admin\PrivacyAudit\Audits\StoredUserAgentStringData;
use WP_Statistics\Service\Admin\PrivacyAudit\Audits\StoredUserIdData;
use WP_Statistics\Service\Admin\PrivacyAudit\Audits\StoreUserAgentString;
use WP_Statistics\Service\Admin\PrivacyAudit\Audits\UnhashedIpAddress;
use WP_Statistics\Service\Admin\PrivacyAudit\Faqs\AbstractFaq;
use WP_Statistics\Service\Admin\PrivacyAudit\Faqs\RequireConsent;
use WP_Statistics\Service\Admin\PrivacyAudit\Faqs\RequireCookieBanner;
use WP_Statistics\Service\Admin\PrivacyAudit\Faqs\RequireMention;
use WP_Statistics\Service\Admin\PrivacyAudit\Faqs\TransferData;

class PrivacyAuditDataProvider
{
    /**
     * Get list of privacy faq items
     * 
     * @return AbstractFaq[] $faqs
     */
    public function getFaqs()
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
     * @return BaseAudit[] $audits
     */
    public function getAudits()
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
     * @return BaseAudit $auditClass
     * @throws InvalidArgumentException if audit class is not found.
     */
    public function getAudit($auditName)
    {
        $audits = $this->getAudits();
        
        if (!isset($audits[$auditName])) {
            throw new InvalidArgumentException(esc_html__(sprintf("%s is not a valid audit item.", $auditName), 'wp-statistics'));
        }

        return $audits[$auditName];
    }


    /**
     * Get privacy audits status
     * 
     * @return array $audits
     */
    public function getAuditsStatus()
    {
        $audits = $this->getAudits();
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
    public function getFaqsStatus()
    {
        $faqs = $this->getFaqs();
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
    public function getComplianceStatus()
    {
        $audits         = $this->getAudits();
        $rulesMapped    = 0;
        $actionRequired = 0;
        $passed         = 0;

        foreach ($audits as $audit) {
            // If audit is not resolvable, skip showing it in the status
            if (!is_subclass_of($audit, ResolvableAudit::class)) continue;

            $rulesMapped++;
            in_array($audit::getStatus(), ['passed', 'resolved'])  ? $passed++ : $actionRequired++;
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
