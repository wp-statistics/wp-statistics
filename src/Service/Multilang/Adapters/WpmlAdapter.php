<?php

namespace WP_Statistics\Service\Multilang\Adapters;

/**
 * WPML adapter.
 *
 * WPML is per-post and exposes everything through filters
 * (wpml_post_language_details, wpml_current_language, …) — including the
 * "current language" — so detection always goes through apply_filters().
 *
 * @since 15.x
 */
class WpmlAdapter extends AbstractAdapter
{
    public function getSlug(): string
    {
        return 'wpml';
    }

    public function getName(): string
    {
        return 'WPML';
    }

    public function getMode(): string
    {
        return self::MODE_PER_POST;
    }

    public function isActive(): bool
    {
        return defined('ICL_SITEPRESS_VERSION') || function_exists('wpml_get_active_languages_filter');
    }

    public function detectLanguage(string $resourceType, int $resourceId, string $uri): ?string
    {
        $detected = null;

        if ($resourceId > 0) {
            $details = apply_filters('wpml_post_language_details', null, $resourceId);

            if (is_array($details) && !empty($details['language_code'])) {
                $detected = $this->normalize($details['language_code']);
            }
        }

        if ($detected === null) {
            $current  = apply_filters('wpml_current_language', null);
            $detected = $this->normalize($current);
        }

        return $detected;
    }

    public function getDefaultLanguage(): ?string
    {
        return $this->normalize(apply_filters('wpml_default_language', null));
    }

    public function getAvailableLanguages(): array
    {
        $active = apply_filters('wpml_active_languages', null, []);

        if (!is_array($active)) {
            return [];
        }

        $result = [];

        foreach ($active as $key => $entry) {
            $code = null;
            $name = null;

            if (is_array($entry)) {
                $code = $entry['code'] ?? $entry['language_code'] ?? $key;
                $name = $entry['translated_name'] ?? $entry['native_name'] ?? $entry['english_name'] ?? null;
            } elseif (is_string($entry)) {
                $code = $entry;
            }

            $code = $this->normalize($code);
            if ($code === null) {
                continue;
            }

            if (!is_string($name) || $name === '') {
                $name = $this->commonLabel($code);
            }

            $result[$code] = $name;
        }

        return $result;
    }
}
