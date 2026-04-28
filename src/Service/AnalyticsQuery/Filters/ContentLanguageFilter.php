<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

use WP_Statistics\Service\Multilang\MultilangService;

/**
 * Content language filter — slices analytics by the language of the content
 * being viewed (as opposed to the visitor's browser language, which is
 * handled by LanguageFilter).
 *
 * Options come from whichever multi-language plugin is currently active
 * (Polylang, WPML, TRP, qTranslate-X, WeGlot). When no plugin is active the
 * options list is empty — the React frontend hides the dropdown.
 *
 * @since 15.x
 */
class ContentLanguageFilter extends AbstractFilter
{
    protected $name = 'content_language';

    protected $column = 'resources.language';

    protected $type = 'string';

    protected $requirement = 'views';

    protected $joins = [
        [
            'table' => 'resource_uris',
            'alias' => 'resource_uris',
            'on'    => 'views.resource_uri_id = resource_uris.ID',
        ],
        [
            'table' => 'resources',
            'alias' => 'resources',
            'on'    => 'resource_uris.resource_id = resources.ID AND resources.is_deleted = 0',
        ],
    ];

    protected $inputType = 'multi-select';

    protected $supportedOperators = ['is', 'is_not', 'in', 'not_in'];

    protected $groups = [
        'views',
        'content',
    ];

    public function getLabel(): string
    {
        return \esc_html__('Content Language', 'wp-statistics');
    }

    /**
     * Options are the languages the active multi-language plugin reports.
     */
    public function getOptions(): ?array
    {
        $available = MultilangService::getInstance()->getAvailableLanguages();

        $options = [];
        foreach ($available as $code => $label) {
            $options[] = [
                'value' => $code,
                'label' => $label,
            ];
        }

        return $options;
    }
}
