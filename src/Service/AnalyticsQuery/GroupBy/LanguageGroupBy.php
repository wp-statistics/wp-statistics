<?php

namespace WP_Statistics\Service\AnalyticsQuery\GroupBy;

/**
 * Language group by - groups by language.
 *
 * @since 15.0.0
 */
class LanguageGroupBy extends AbstractGroupBy
{
    protected $name         = 'language';
    protected $column       = 'languages.name';
    protected $alias        = 'language_name';
    protected $extraColumns = [
        'languages.ID AS language_id',
        'languages.code AS language_code',
        'languages.region AS language_region',
    ];
    protected $joins        = [
        'table' => 'languages',
        'alias' => 'languages',
        'on'    => 'sessions.language_id = languages.ID',
        'type'  => 'INNER',
    ];
    protected $groupBy      = 'languages.ID';
}
