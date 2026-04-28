<?php

namespace WP_Statistics\Service\Multilang\Adapters;

/**
 * Polylang adapter.
 *
 * Polylang assigns language at the post/term level — each translation is its
 * own post_id — so we report per-post mode.
 *
 * @since 15.x
 */
class PolylangAdapter extends AbstractAdapter
{
    public function getSlug(): string
    {
        return 'polylang';
    }

    public function getName(): string
    {
        return 'Polylang';
    }

    public function getMode(): string
    {
        return self::MODE_PER_POST;
    }

    public function isActive(): bool
    {
        return function_exists('pll_get_post_language') && function_exists('pll_current_language');
    }

    public function detectLanguage(string $resourceType, int $resourceId, string $uri): ?string
    {
        $detected = null;

        if ($resourceId > 0 && $this->isTermLike($resourceType) && function_exists('pll_get_term_language')) {
            $detected = $this->normalize(pll_get_term_language($resourceId, 'slug'));
        }

        if ($detected === null && $resourceId > 0 && !$this->isTermLike($resourceType) && $resourceType !== 'home' && function_exists('pll_get_post_language')) {
            // Polylang returns false for posts it doesn't know — that's fine,
            // normalize() turns it into null and we fall through to the
            // request-context language below.
            $detected = $this->normalize(pll_get_post_language($resourceId, 'slug'));
        }

        if ($detected === null && function_exists('pll_current_language')) {
            $detected = $this->normalize(pll_current_language('slug'));
        }

        return $detected;
    }

    public function getDefaultLanguage(): ?string
    {
        if (!function_exists('pll_default_language')) {
            return null;
        }

        return $this->normalize(pll_default_language('slug'));
    }

    public function getAvailableLanguages(): array
    {
        if (!function_exists('pll_languages_list')) {
            return [];
        }

        $codes  = pll_languages_list(['fields' => 'slug']);
        $result = [];

        foreach ((array) $codes as $code) {
            $code = $this->normalize($code);
            if ($code === null) {
                continue;
            }
            $result[$code] = $this->commonLabel($code);
        }

        return $result;
    }

    private function isTermLike(string $type): bool
    {
        return in_array($type, ['category', 'post_tag', 'tag', 'taxonomy', 'term'], true);
    }
}
