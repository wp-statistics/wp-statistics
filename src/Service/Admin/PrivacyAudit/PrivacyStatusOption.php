<?php

namespace WP_Statistics\Service\Admin\PrivacyAudit;

use WP_STATISTICS\Option;
use WP_Statistics\Service\Admin\PrivacyAudit\Audits\Abstracts\ResolvableAudit;

class PrivacyStatusOption
{
    CONST KEY = 'privacy_status';

    private static function defaultOptions()
    {
        $privacyAuditDataProvider = new PrivacyAuditDataProvider();

        $audits         = $privacyAuditDataProvider->getAudits();
        $defaultOptions = [];

        /** @var ResolvableAudit $audit */
        foreach ($audits as $audit) {
            // Only resolvable audits state needs to be stored in options
            if (!is_subclass_of($audit, ResolvableAudit::class)) continue;

            // By default, all audits should be action_required
            $defaultOptions[$audit::$optionKey] = 'action_required';
        }

        return $defaultOptions;
    }

    public static function init()
    {
        $defaultOptions = self::defaultOptions();

        foreach ($defaultOptions as $key => $value) {
            Option::saveOptionGroup($key, $value, 'privacy_status');
        }
    }
}
