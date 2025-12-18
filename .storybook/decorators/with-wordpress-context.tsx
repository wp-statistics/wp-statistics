import type { Decorator } from '@storybook/react'
import { useEffect } from 'react'

/**
 * Decorator that provides mock WordPress context for Storybook
 * This prevents the "wps_react not available" error
 */
export const withWordPressContext: Decorator = (Story) => {
  useEffect(() => {
    // Mock the WordPress global object that the plugin expects
    if (typeof window !== 'undefined' && !window.wps_react) {
      window.wps_react = {
        globals: {
          nonce: 'storybook-mock-nonce',
          ajaxUrl: 'https://wordpress.test/wp-admin/admin-ajax.php',
          pluginUrl: 'https://wordpress.test/wp-content/plugins/wp-statistics/',
          isPremium: false,
          restUrl: 'https://wordpress.test/wp-json/',
          restNonce: 'storybook-mock-rest-nonce',
        },
        layout: {
          sidebar: {
            items: [],
          },
        },
        filters: {},
        config: {},
      }
    }
  }, [])

  return <Story />
}
