<?php

namespace WP_Statistics\Service\Multilang\Adapters;

use WP_Statistics\Service\Multilang\LanguageNames;

/**
 * Shared scaffolding for adapters.
 *
 * Adapters only need to implement what differs from one plugin to another;
 * defaults here cover the boring cases (no default language, no enumerable
 * language list).
 *
 * @since 15.x
 */
abstract class AbstractAdapter implements AdapterInterface
{
    public function getDefaultLanguage(): ?string
    {
        return null;
    }

    public function getAvailableLanguages(): array
    {
        return [];
    }

    /**
     * Normalize a raw plugin value to a canonical code stored in `resources.language`.
     *
     * Adapters return slightly different formats (Polylang: 'en', WPML: 'en',
     * TranslatePress: 'en_US'). We lowercase, trim, and convert WP-style locale
     * separators to the BCP-47-ish hyphen form ('pt_BR' → 'pt-br').
     */
    protected function normalize($raw): ?string
    {
        if ($raw === null || $raw === false || $raw === '') {
            return null;
        }

        $code = strtolower(trim((string) $raw));
        $code = str_replace('_', '-', $code);

        return $code !== '' ? $code : null;
    }

    /**
     * Built-in label for a code. Subclasses use this when the plugin doesn't
     * supply a human name for an enabled language.
     */
    protected function commonLabel(string $code): string
    {
        return LanguageNames::find($code) ?? strtoupper($code);
    }
}
