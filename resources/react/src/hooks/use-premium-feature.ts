import { useMemo } from 'react'

import { WordPress } from '@/lib/wordpress'

/**
 * Hook to check if a premium feature is enabled.
 *
 * Features are unlocked when premium plugin hooks into the
 * 'wp_statistics_premium_unlocked_features' PHP filter.
 * This is secure because the data comes from PHP, not client-side manipulation.
 *
 * @param featureSlug - The feature slug to check (e.g., 'widget-customization')
 * @returns Object with isEnabled boolean and isLoading state
 */
export function usePremiumFeature(featureSlug: string) {
  const result = useMemo(() => {
    try {
      const wp = WordPress.getInstance()
      const premium = wp.getData('premium')

      // Premium data not available
      if (!premium) {
        return { isEnabled: false, isLoading: false }
      }

      // Check unlockedFeatures (from PHP filter) - secure, can't be client-side manipulated
      const unlockedFeatures = premium.unlockedFeatures || {}
      const isEnabled = unlockedFeatures[featureSlug] === true

      return { isEnabled, isLoading: false }
    } catch {
      // WordPress instance not ready
      return { isEnabled: false, isLoading: true }
    }
  }, [featureSlug])

  return result
}

/**
 * Hook to get all unlocked premium features.
 *
 * @returns Array of unlocked feature slugs
 */
export function usePremiumFeatures() {
  return useMemo(() => {
    try {
      const wp = WordPress.getInstance()
      const premium = wp.getData('premium')
      const unlockedFeatures = premium?.unlockedFeatures || {}
      // Return array of feature slugs that are unlocked (value === true)
      return Object.entries(unlockedFeatures)
        .filter(([, value]) => value === true)
        .map(([key]) => key)
    } catch {
      return []
    }
  }, [])
}

/**
 * Hook to check if premium plugin is active.
 *
 * @returns Boolean indicating if premium is active
 */
export function useIsPremiumActive() {
  return useMemo(() => {
    try {
      const wp = WordPress.getInstance()
      const premium = wp.getData('premium')
      return premium?.active === true
    } catch {
      return false
    }
  }, [])
}
