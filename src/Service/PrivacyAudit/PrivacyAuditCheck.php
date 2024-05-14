<?php

namespace WP_Statistics\Service\PrivacyAudit;

use WP_Statistics\Service\PrivacyAudit\Audits\Abstracts\BaseAudit;
use WP_Statistics\Service\PrivacyAudit\Audits\Abstracts\ResolvableAudit;
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
use WP_STATISTICS\Menus;

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
     * @return BaseAudit[] $audits
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
     * @return BaseAudit $auditClass
     * @throws InvalidArgumentException if audit class is not found.
     */
    public static function getAudit($auditName)
    {
        $audits = self::getAudits();
        
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

    /**
     * Privacy compliance test result for WordPress site health.
     * 
     * @return array $result
     */
    public static function privacyComplianceTest()
    {
        $complianceStatus   = self::complianceStatus();
        $isPrivacyCompliant = $complianceStatus['percentage_ready'] == 100;

		$result = [
			'label'       => esc_html__( 'Your WP Statistics settings are privacy-compliant.', 'wp-statistics' ),
			'status'      => 'good',
			'badge'       => [
				'label' => esc_html__('Privacy', 'wp-statistics'),
				'color' => 'blue'
			],
			'description' => sprintf(
                __('<p>The settings in your WP Statistics account comply with the privacy regulations. Visit the <a target="_blank" href="%s">%s<span aria-hidden="true" class="dashicons dashicons-external"></span></a> to learn more about best practices.</p>', 'wp-statistics'),
                esc_url(Menus::admin_url(Menus::get_page_slug('privacy-audit'))),
                esc_html__('Privacy Audit page', 'wp-statistics')
            ),
            'test'        => 'wp_statistics_privacy_compliance_status'
		];

		if ($isPrivacyCompliant == false) {
			$result['label']          = esc_html__('Your WP Statistics settings are not privacy-compliant. Please update your settings.', 'wp-statistics');
			$result['description']    = sprintf(
                __('<p>Your WP Statistics settings do not meet the necessary privacy standards. Immediate adjustments are required to ensure compliance and protect user data. Please review and update your settings as recommended on the <a target="_blank" href="%s">%s<span aria-hidden="true" class="dashicons dashicons-external"></span></a>.</p>', 'wp-statistics'),
                esc_url(Menus::admin_url(Menus::get_page_slug('privacy-audit'))),
                esc_html__('Privacy Audit page', 'wp-statistics')
            );
			$result['status']         = 'recommended';
			$result['badge']['color'] = 'orange';
		}

		return $result;
    }
}
