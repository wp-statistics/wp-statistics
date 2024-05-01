<?php

namespace WP_Statistics\Service\PrivacyAudit;

use WP_Statistics\Service\PrivacyAudit\Audits\AbstractAudit;
use WP_Statistics\Service\PrivacyAudit\Audits\AnonymizeIpAddress;
use WP_Statistics\Service\PrivacyAudit\Audits\RecordUserPageVisits;
use WP_Statistics\Service\PrivacyAudit\Audits\HashIpAddress;
use WP_Statistics\Service\PrivacyAudit\Audits\StoreUserAgentString;

class PrivacyAuditCheck
{
    /** @var AbstractAudit[] $audits */
    public static $audits = [
        'record_user_page_visits'   => RecordUserPageVisits::class,
        'anonymize_ip_address'      => AnonymizeIpAddress::class,
        'hash_ip_address'           => HashIpAddress::class,
        'store_user_agent_string'   => StoreUserAgentString::class,
    ];

    public static function auditListStatus()
    {
        $list = [];

        foreach (self::$audits as $key => $audit) {
            $auditInfo = $audit::getState();

            $auditItem = [
                'name'      => $key, 
                'title'     => $auditInfo['title'], 
                'notes'     => $auditInfo['notes'],
                'status'    => $auditInfo['status'], 
                'compliance'=> $auditInfo['compliance'],
            ];

            // If audit has action in the current state, add it to the audit item array.
            if (!empty($auditInfo['status'])) {
                $auditItem['action'] = $auditInfo['action'];
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
