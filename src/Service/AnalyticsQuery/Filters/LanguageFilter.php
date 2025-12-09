<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Language filter - filters by language code.
 *
 * @since 15.0.0
 */
class LanguageFilter extends AbstractFilter
{
    protected $name   = 'language';
    protected $column = 'languages.code';
    protected $type   = 'string';
    protected $joins  = [
        'table' => 'languages',
        'alias' => 'languages',
        'on'    => 'sessions.language_id = languages.ID',
    ];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Language', 'wp-statistics');
    }
}
