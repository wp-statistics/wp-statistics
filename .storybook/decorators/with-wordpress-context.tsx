import type { Decorator } from '@storybook/react'
import { useEffect } from 'react'

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
          trackLoggedInUsers: true,
          hashIps: false,
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
  }, [])

  return <Story />
}
