/**
 * Collect visitor locale information: timezone, language, screen dimensions.
 * Intl objects are cached since they don't change during a page session.
 */

var cachedTimezone = null;
var cachedLanguageFullName = null;

export function collectLocaleInfo() {
    if (!cachedTimezone) {
        cachedTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
    }

    if (!cachedLanguageFullName) {
        var languageCode = navigator.language.split('-')[0];
        cachedLanguageFullName = languageCode;
        try {
            cachedLanguageFullName = (new Intl.DisplayNames(['en'], { type: 'language' })).of(languageCode);
        } catch (e) {
            // Fallback to language code if Intl.DisplayNames is not supported
        }
    }

    return {
        timezone: cachedTimezone,
        language: navigator.language || navigator.userLanguage,
        languageFullName: cachedLanguageFullName,
        screenWidth: window.screen.width,
        screenHeight: window.screen.height,
    };
}
