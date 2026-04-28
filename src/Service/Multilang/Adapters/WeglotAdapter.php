<?php

namespace WP_Statistics\Service\Multilang\Adapters;

/**
 * WeGlot adapter.
 *
 * WeGlot translates URLs at the edge and only one post_id exists per
 * resource — language is request-context. We report per-request mode and
 * read the active language from weglot_get_current_language().
 *
 * @since 15.x
 */
class WeglotAdapter extends AbstractAdapter
{
    public function getSlug(): string
    {
        return 'weglot';
    }

    public function getName(): string
    {
        return 'Weglot';
    }

    public function getMode(): string
    {
        return self::MODE_PER_REQUEST;
    }

    public function isActive(): bool
    {
        return function_exists('weglot_get_current_language');
    }

    public function detectLanguage(string $resourceType, int $resourceId, string $uri): ?string
    {
        if (!function_exists('weglot_get_current_language')) {
            return null;
        }

        return $this->normalize(weglot_get_current_language());
    }

    public function getDefaultLanguage(): ?string
    {
        if (!function_exists('weglot_get_original_language')) {
            return null;
        }

        return $this->normalize(weglot_get_original_language());
    }

    public function getAvailableLanguages(): array
    {
        $result = [];

        if (function_exists('weglot_get_original_language')) {
            $original = $this->normalize(weglot_get_original_language());
            if ($original !== null) {
                $result[$original] = $this->commonLabel($original);
            }
        }

        if (function_exists('weglot_get_destination_language')) {
            $destinations = (array) weglot_get_destination_language();
            foreach ($destinations as $code) {
                $normalized = $this->normalize($code);
                if ($normalized === null) {
                    continue;
                }
                $result[$normalized] = $this->commonLabel($normalized);
            }
        }

        return $result;
    }
}
