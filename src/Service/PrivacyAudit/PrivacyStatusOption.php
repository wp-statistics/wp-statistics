<?php

namespace WP_Statistics\Service\PrivacyAudit;

class PrivacyStatusOption
{
    CONST KEY = 'wp_statistics_privacy_status';

    private static function defaultOptions()
    {
        $defaultOptions = [];
        $audits         = PrivacyAuditCheck::getAudits();

        foreach ($audits as $audit) {
            // If audit has no action, no need to store the status in database
            if (!$audit::hasAction()) continue;

            $defaultOptions[$audit::$optionKey] = $audit::getStatus();
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
