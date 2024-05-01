<?php

namespace WP_Statistics\Service\PrivacyAudit;

use WP_Statistics\Service\PrivacyAudit\Audits\AbstractAudit;
use WP_Statistics\Service\PrivacyAudit\Audits\AnonymizeIpAddress;
use WP_Statistics\Service\PrivacyAudit\Audits\RecordUserPageVisits;
use WP_Statistics\Service\PrivacyAudit\Audits\HashIpAddress;
use WP_Statistics\Service\PrivacyAudit\Audits\StoreUserAgentString;
use WP_Statistics\Service\PrivacyAudit\Audits\UnhashedIpAddress;

class PrivacyAuditCheck
{
    /** @var AbstractAudit[] $audits */
    public static $audits = [
        'record_user_page_visits'   => RecordUserPageVisits::class,
        'anonymize_ip_address'      => AnonymizeIpAddress::class,
        'hash_ip_address'           => HashIpAddress::class,
        'store_user_agent_string'   => StoreUserAgentString::class,
        'unhashed_ip_address'       => UnhashedIpAddress::class,
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
        $rulesMapped    = 0;
        $actionRequired = 0;
        $passed         = 0;

        foreach (self::$audits as $audit) {
            // If current state data is not available, skip
            if (empty($audit::getState())) continue;

            $rulesMapped++;
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
