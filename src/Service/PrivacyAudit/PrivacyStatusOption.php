<?php

namespace WP_Statistics\Service\PrivacyAudit;

use WP_Statistics\Service\PrivacyAudit\Audits\Abstracts\ResolvableAudit;

class PrivacyStatusOption
{
    CONST KEY = 'wp_statistics_privacy_status';

    private static function defaultOptions()
    {
        $defaultOptions = [];
        $audits         = PrivacyAuditCheck::getAudits();

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
        add_option(self::KEY, self::defaultOptions());
    }

    public static function getAll()
    {
        return get_option(self::KEY);
    }

    public static function get($key, $default = null)
    {
        $options = self::getAll();
        return isset($options[$key]) ? $options[$key] : $default;
    }

    public static function update($key, $value)
    {
        $options = self::getAll();
        $options[$key] = $value;

        return update_option(self::KEY, $options);
    }
}
