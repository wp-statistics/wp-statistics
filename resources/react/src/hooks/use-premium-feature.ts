import { useMemo } from 'react'

import { WordPress } from '@/lib/wordpress'

/**
 * Hook to check if a premium feature is enabled.
 *
 * Features are enabled when:
 * 1. Premium plugin is active AND
 * 2. User has a valid license with the feature OR
 * 3. Premium is in dev mode
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

      // Check if feature is in the enabled features list
      const features = premium.features || []
      const isEnabled = features.includes(featureSlug)

      return { isEnabled, isLoading: false }
    } catch {
      // WordPress instance not ready
      return { isEnabled: false, isLoading: true }
    }
  }, [featureSlug])

  return result
}

/**
 * Hook to get all enabled premium features.
 *
 * @returns Array of enabled feature slugs
 */
export function usePremiumFeatures() {
  return useMemo(() => {
    try {
      const wp = WordPress.getInstance()
      const premium = wp.getData('premium')
      return premium?.features || []
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
