import type { Decorator } from '@storybook/react'

// Mock filter operators
const mockOperators = {
  is: { label: 'is', type: 'single' as const },
  is_not: { label: 'is not', type: 'single' as const },
  in: { label: 'is any of', type: 'multiple' as const },
  not_in: { label: 'is none of', type: 'multiple' as const },
  gt: { label: 'greater than', type: 'single' as const },
  gte: { label: 'greater than or equal', type: 'single' as const },
  lt: { label: 'less than', type: 'single' as const },
  lte: { label: 'less than or equal', type: 'single' as const },
  between: { label: 'is between', type: 'range' as const },
  contains: { label: 'contains', type: 'single' as const },
  not_contains: { label: 'does not contain', type: 'single' as const },
}

// Set up mock WordPress context synchronously so it's available before first render
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
      storeIp: false,
    },
    layout: {
      sidebar: {
        items: [],
      },
    },
    filters: {
      operators: mockOperators,
      fields: {},
    },
    config: {},
  }
}

/**
 * Decorator that provides mock WordPress context for Storybook
 * The mock is set up at module load time (above) so it's available before first render.
 */
export const withWordPressContext: Decorator = (Story) => {
  return <Story />
}
