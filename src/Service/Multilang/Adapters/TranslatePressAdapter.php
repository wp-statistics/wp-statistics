<?php

namespace WP_Statistics\Service\Multilang\Adapters;

/**
 * TranslatePress adapter.
 *
 * TRP serves all translations from a single post_id and switches the rendered
 * content based on the request — so language is request-context, not post-context.
 *
 * Detection reads from TRP's well-known filters (trp_current_language /
 * trp_published_languages*).
 *
 * @since 15.x
 */
class TranslatePressAdapter extends AbstractAdapter
{
    public function getSlug(): string
    {
        return 'translatepress';
    }

    public function getName(): string
    {
        return 'TranslatePress';
    }

    public function getMode(): string
    {
        return self::MODE_PER_REQUEST;
    }

    public function isActive(): bool
    {
        return function_exists('trp_install') || class_exists('\TRP_Translate_Press');
    }

    public function detectLanguage(string $resourceType, int $resourceId, string $uri): ?string
    {
        return $this->normalize(apply_filters('trp_current_language', null));
    }

    public function getDefaultLanguage(): ?string
    {
        return $this->normalize(apply_filters('trp_default_language', null));
    }

    public function getAvailableLanguages(): array
    {
        $codes  = (array) apply_filters('trp_published_languages', []);
        $labels = (array) apply_filters('trp_published_languages_labels', []);

        $result = [];

        foreach ($codes as $code) {
            $rawCode    = (string) $code;
            $normalized = $this->normalize($rawCode);
            if ($normalized === null) {
                continue;
            }

            // TRP keys its labels by the original (non-normalized) code, so look
            // up the label by raw code first; fall back to the built-in table.
            $label = $labels[$rawCode] ?? $this->commonLabel($normalized);

            $result[$normalized] = $label;
        }

        return $result;
    }
}
