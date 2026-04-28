<?php
/**
 * Stubs for third-party multi-language plugin functions.
 *
 * Loaded by adapter unit tests so the SUT's function_exists() checks see the
 * functions and exercise the "plugin active" code paths. Each stub reads from
 * $GLOBALS['_wpstats_test_multilang'] which tests populate in setUp().
 *
 * Stubs are guarded by function_exists() so loading this file alongside a real
 * plugin is a no-op.
 */

if (!isset($GLOBALS['_wpstats_test_multilang'])) {
    $GLOBALS['_wpstats_test_multilang'] = [];
}

// ---------- Polylang ----------
if (!function_exists('pll_get_post_language')) {
    function pll_get_post_language($postId, $field = 'slug')
    {
        $stash = $GLOBALS['_wpstats_test_multilang']['polylang']['posts'] ?? [];
        return $stash[$postId] ?? false;
    }
}

if (!function_exists('pll_get_term_language')) {
    function pll_get_term_language($termId, $field = 'slug')
    {
        $stash = $GLOBALS['_wpstats_test_multilang']['polylang']['terms'] ?? [];
        return $stash[$termId] ?? false;
    }
}

if (!function_exists('pll_current_language')) {
    function pll_current_language($field = 'slug')
    {
        return $GLOBALS['_wpstats_test_multilang']['polylang']['current'] ?? false;
    }
}

if (!function_exists('pll_default_language')) {
    function pll_default_language($field = 'slug')
    {
        return $GLOBALS['_wpstats_test_multilang']['polylang']['default'] ?? false;
    }
}

if (!function_exists('pll_languages_list')) {
    function pll_languages_list($args = [])
    {
        return $GLOBALS['_wpstats_test_multilang']['polylang']['languages'] ?? [];
    }
}

if (!function_exists('pll_the_languages')) {
    function pll_the_languages($args = [])
    {
        return $GLOBALS['_wpstats_test_multilang']['polylang']['the_languages'] ?? [];
    }
}

// ---------- WPML ----------
// WPML primarily exposes data through filters (wpml_post_language_details,
// wpml_current_language, wpml_active_languages). It also defines ICL_LANGUAGE_CODE
// in some setups. We rely on a well-known function to indicate "loaded".
if (!function_exists('wpml_get_active_languages_filter')) {
    // WPML registers this internally; the existence indicates WPML is loaded.
    function wpml_get_active_languages_filter($empty = '', $args = [])
    {
        return $GLOBALS['_wpstats_test_multilang']['wpml']['active_languages'] ?? [];
    }
}

// ---------- TranslatePress ----------
if (!function_exists('trp_install')) {
    // Sentinel function present only when TRP is loaded.
    function trp_install()
    {
        return true;
    }
}

// ---------- qTranslate-X / qTranslate-XT ----------
if (!function_exists('qtranxf_getLanguage')) {
    function qtranxf_getLanguage()
    {
        return $GLOBALS['_wpstats_test_multilang']['qtranslate']['current'] ?? '';
    }
}

if (!function_exists('qtranxf_getLanguageDefault')) {
    function qtranxf_getLanguageDefault()
    {
        return $GLOBALS['_wpstats_test_multilang']['qtranslate']['default'] ?? '';
    }
}

if (!function_exists('qtranxf_getEnabledLanguages')) {
    function qtranxf_getEnabledLanguages()
    {
        return $GLOBALS['_wpstats_test_multilang']['qtranslate']['enabled'] ?? [];
    }
}

if (!function_exists('qtranxf_getLanguageName')) {
    function qtranxf_getLanguageName($code)
    {
        $names = $GLOBALS['_wpstats_test_multilang']['qtranslate']['names'] ?? [];
        return $names[$code] ?? $code;
    }
}

// ---------- WeGlot ----------
if (!function_exists('weglot_get_current_language')) {
    function weglot_get_current_language()
    {
        return $GLOBALS['_wpstats_test_multilang']['weglot']['current'] ?? '';
    }
}

if (!function_exists('weglot_get_original_language')) {
    function weglot_get_original_language()
    {
        return $GLOBALS['_wpstats_test_multilang']['weglot']['original'] ?? '';
    }
}

if (!function_exists('weglot_get_destination_language')) {
    function weglot_get_destination_language()
    {
        return $GLOBALS['_wpstats_test_multilang']['weglot']['destinations'] ?? [];
    }
}
