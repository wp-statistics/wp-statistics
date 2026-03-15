import type { Plugin } from 'vite'

export function wpI18n(options: { textDomain: string }): Plugin {
  return {
    name: 'vite-plugin-wp-i18n',
    closeBundle() {
      // Note: Translation extraction for React should be done separately
      // You can use WP-CLI: wp i18n make-pot . resources/languages/wp-statistics.pot --include="resources/react/src"
      // Or use gettext tools directly on the source files
      console.log(`\n✓ Build complete. To extract translations, run:`)
      console.log(`  wp i18n make-pot . resources/languages/${options.textDomain}.pot --include="resources/react/src"\n`)
    },
  }
}
