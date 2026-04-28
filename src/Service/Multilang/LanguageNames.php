<?php

namespace WP_Statistics\Service\Multilang;

/**
 * Built-in language code → human label table.
 *
 * Used as the fallback when no multi-language plugin is active or the active
 * plugin doesn't provide a label for a stored code (e.g. after the plugin has
 * been uninstalled but historical rows still carry the code).
 *
 * @since 15.x
 */
final class LanguageNames
{
    /**
     * @var array<string, string>
     */
    private const NAMES = [
        'en'    => 'English',
        'fr'    => 'Français',
        'es'    => 'Español',
        'de'    => 'Deutsch',
        'it'    => 'Italiano',
        'pt'    => 'Português',
        'pt-br' => 'Português (Brasil)',
        'pt-pt' => 'Português (Portugal)',
        'nl'    => 'Nederlands',
        'sv'    => 'Svenska',
        'no'    => 'Norsk',
        'da'    => 'Dansk',
        'fi'    => 'Suomi',
        'pl'    => 'Polski',
        'ru'    => 'Русский',
        'uk'    => 'Українська',
        'cs'    => 'Čeština',
        'sk'    => 'Slovenčina',
        'hu'    => 'Magyar',
        'ro'    => 'Română',
        'bg'    => 'Български',
        'el'    => 'Ελληνικά',
        'tr'    => 'Türkçe',
        'he'    => 'עברית',
        'ar'    => 'العربية',
        'fa'    => 'فارسی',
        'hi'    => 'हिन्दी',
        'th'    => 'ไทย',
        'vi'    => 'Tiếng Việt',
        'id'    => 'Bahasa Indonesia',
        'ms'    => 'Bahasa Melayu',
        'ja'    => '日本語',
        'ko'    => '한국어',
        'zh'    => '中文',
        'zh-cn' => '中文 (简体)',
        'zh-tw' => '中文 (繁體)',
    ];

    /**
     * Return the human label for a code, falling back to the code itself.
     */
    public static function lookup(string $code): string
    {
        $code = strtolower($code);
        return self::NAMES[$code] ?? $code;
    }

    /**
     * Return the label without code-fallback (null if unknown).
     */
    public static function find(string $code): ?string
    {
        return self::NAMES[strtolower($code)] ?? null;
    }
}
