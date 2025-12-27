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
          analyticsAction: 'wp_statistics_analytics',
          filterAction: 'wp_statistics_get_filter_options',
          userPreferencesAction: 'wp_statistics_user_preferences',
          trackLoggedInUsers: true,
          hashIps: false,
        },
        layout: {
          sidebar: {
            items: [],
          },
        },
        filters: {
          fields: {},
          operators: {
            is: { label: '=', type: 'single' },
            is_not: { label: '≠', type: 'single' },
            gt: { label: '>', type: 'single' },
            gte: { label: '≥', type: 'single' },
            lt: { label: '<', type: 'single' },
            lte: { label: '≤', type: 'single' },
            in: { label: 'in', type: 'multiple' },
            not_in: { label: 'not in', type: 'multiple' },
            between: { label: 'between', type: 'range' },
            contains: { label: 'contains', type: 'single' },
            starts_with: { label: 'starts with', type: 'single' },
            ends_with: { label: 'ends with', type: 'single' },
          },
        },
        config: {},
      }
    }
  }, [])

  return <Story />
}
