<?php

namespace WP_Statistics\Service\PrivacyAudit;

use WP_Statistics\Service\PrivacyAudit\Actions\AbstractPrivacyAction;
use WP_Statistics\Service\PrivacyAudit\Actions\RecordUserPageVisits;

class PrivacyAuditCheck
{
    /** @var AbstractPrivacyAction[] $actions */
    public static $list = [
        'record_user_page_visits' => RecordUserPageVisits::class,
    ];

    public static function auditListStatus()
    {
        $list = [];

        foreach (self::$list as $key => $action) {
            $item = $action::getState();

            $list[] = [
                'name'      => $key, 
                'title'     => $item['title'], 
                'notes'     => $item['notes'],
                'status'    => $item['status'], 
                'action'    => $item['action'],
                'compliance'=> $item['compliance'],
            ];
        }

        return $list;
    }

    public static function complianceStatus()
    {
        $rulesMapped    = 0;
        $actionRequired = 0;
        $passed         = 0;

        foreach (self::$list as $action) {
            $rulesMapped++;
            $action::getStatus() == 'passed' ? $passed++ : $actionRequired++;
        }

        return [
            'percentage_ready'  => ($passed / $rulesMapped) * 100,
            'rules_mapped'      => $rulesMapped,
            'summary'           => [
                'passed'          => $passed,
                'action_required' => $actionRequired
            ]
        ];
    }

}
