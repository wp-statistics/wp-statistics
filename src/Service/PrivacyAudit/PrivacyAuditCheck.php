<?php

namespace WP_Statistics\Service\PrivacyAudit;

use WP_Statistics\Service\PrivacyAudit\Audits\AbstractAudit;
use WP_Statistics\Service\PrivacyAudit\Audits\AnonymizeIpAddress;
use WP_Statistics\Service\PrivacyAudit\Audits\RecordUserPageVisits;
use WP_Statistics\Service\PrivacyAudit\Audits\HashIpAddress;

class PrivacyAuditCheck
{
    /** @var AbstractAudit[] $audits */
    public static $audits = [
        'record_user_page_visits'   => RecordUserPageVisits::class,
        'anonymize_ip_address'      => AnonymizeIpAddress::class,
        'hash_ip_address'           => HashIpAddress::class,
    ];

    public static function auditListStatus()
    {
        $list = [];

        foreach (self::$audits as $key => $audit) {
            $auditInfo = $audit::getState();

            $list[] = [
                'name'      => $key, 
                'title'     => $auditInfo['title'], 
                'notes'     => $auditInfo['notes'],
                'status'    => $auditInfo['status'], 
                'action'    => $auditInfo['action'],
                'compliance'=> $auditInfo['compliance'],
            ];
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
