<?php

namespace WP_Statistics\Service\Multilang\Adapters;

/**
 * qTranslate-X / qTranslate-XT adapter.
 *
 * qTranslate stores all translations inside a single post body and toggles
 * which language is rendered at request time — so we report per-request mode.
 *
 * @since 15.x
 */
class QTranslateAdapter extends AbstractAdapter
{
    public function getSlug(): string
    {
        return 'qtranslate';
    }

    public function getName(): string
    {
        return 'qTranslate-X';
    }

    public function getMode(): string
    {
        return self::MODE_PER_REQUEST;
    }

    public function isActive(): bool
    {
        return function_exists('qtranxf_getLanguage');
    }

    public function detectLanguage(string $resourceType, int $resourceId, string $uri): ?string
    {
        if (!function_exists('qtranxf_getLanguage')) {
            return null;
        }

        return $this->normalize(qtranxf_getLanguage());
    }

    public function getDefaultLanguage(): ?string
    {
        if (!function_exists('qtranxf_getLanguageDefault')) {
            return null;
        }

        return $this->normalize(qtranxf_getLanguageDefault());
    }

    public function getAvailableLanguages(): array
    {
        if (!function_exists('qtranxf_getEnabledLanguages')) {
            return [];
        }

        $enabled = (array) qtranxf_getEnabledLanguages();
        $result  = [];

        foreach ($enabled as $code) {
            $normalized = $this->normalize($code);
            if ($normalized === null) {
                continue;
            }

            $name = function_exists('qtranxf_getLanguageName')
                ? qtranxf_getLanguageName($code)
                : null;

            if (!is_string($name) || $name === '') {
                $name = $this->commonLabel($normalized);
            }

            $result[$normalized] = $name;
        }

        return $result;
    }
}
