import { useNavigate, useSearch } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { useCallback, useMemo } from 'react'

import type { PageFilterConfig } from '@/components/custom/options-drawer'
import type { ApiFilters } from '@/lib/api-filter-transform'

export type SocialType = 'all' | 'organic' | 'paid'

export interface UseSocialTypeFilterOptions {
  /** Default value when URL param is not set. Default: 'all' */
  defaultValue?: SocialType
  /** Whether to sync the value to URL. Default: true */
  syncToUrl?: boolean
  /** URL parameter name. Default: 'social_type' */
  urlParamName?: string
}

export interface UseSocialTypeFilterResult {
  /** Current selected value */
  value: SocialType
  /** Handler to change the value */
  onChange: (value: SocialType) => void
  /** Available options for the dropdown */
  options: { value: SocialType; label: string }[]
  /** Display label for the selected type */
  selectedLabel: string
  /** Get API filter based on current selection */
  getApiFilter: () => ApiFilters
  /** Ready-to-use config for PageFilters in options drawer */
  pageFilterConfig: PageFilterConfig
}

/**
 * Hook for managing social type filter state with URL synchronization.
 *
 * Provides a unified way to filter social media traffic by type (All/Organic/Paid),
 * with automatic URL sync and API filter generation.
 *
 * @example
 * ```tsx
 * const { value, onChange, options, getApiFilter } = useSocialTypeFilter()
 *
 * // Use in header
 * <SocialTypeSelect value={value} onValueChange={onChange} options={options} />
 *
 * // Get API filter for query
 * const apiFilter = getApiFilter()
 * // Returns: { referrer_channel: { in: ['social', 'paid_social'] } } for 'all'
 * // Returns: { referrer_channel: { is: 'social' } } for 'organic'
 * // Returns: { referrer_channel: { is: 'paid_social' } } for 'paid'
 * ```
 */
export function useSocialTypeFilter({
  defaultValue = 'all',
  syncToUrl = true,
  urlParamName = 'social_type',
}: UseSocialTypeFilterOptions = {}): UseSocialTypeFilterResult {
  const navigate = useNavigate()

  // Get value from URL or default
  const search = useSearch({ strict: false }) as Record<string, unknown>
  const urlValue = search[urlParamName] as SocialType | undefined
  const value = urlValue || defaultValue

  // Define social type options
  const options = useMemo<{ value: SocialType; label: string }[]>(
    () => [
      { value: 'all', label: __('All', 'wp-statistics') },
      { value: 'organic', label: __('Organic', 'wp-statistics') },
      { value: 'paid', label: __('Paid', 'wp-statistics') },
    ],
    []
  )

  // Get selected type label
  const selectedLabel = useMemo(() => {
    const option = options.find((o) => o.value === value)
    return option?.label || __('All', 'wp-statistics')
  }, [options, value])

  // Handle value change with optional URL sync
  const onChange = useCallback(
    (newValue: SocialType) => {
      if (syncToUrl) {
        navigate({
          search: (prev) => {
            // Build clean result object
            const cleaned = Object.fromEntries(
              Object.entries(prev as Record<string, unknown>).filter(
                ([key]) => key !== urlParamName
              )
            )

            const result: Record<string, unknown> = { ...cleaned }

            // Only add param if not default value
            if (newValue !== defaultValue) {
              result[urlParamName] = newValue
            }

            return result
          },
          replace: true,
        })
      }
    },
    [navigate, syncToUrl, urlParamName, defaultValue]
  )

  // Get API filter based on current selection
  const getApiFilter = useCallback((): ApiFilters => {
    switch (value) {
      case 'organic':
        return { referrer_channel: { is: 'social' } }
      case 'paid':
        return { referrer_channel: { is: 'paid_social' } }
      case 'all':
      default:
        return { referrer_channel: { in: ['social', 'paid_social'] } }
    }
  }, [value])

  // Build PageFilterConfig for options drawer
  const pageFilterConfig = useMemo<PageFilterConfig>(
    () => ({
      id: urlParamName,
      label: __('Social Type', 'wp-statistics'),
      value,
      options,
      onChange,
    }),
    [urlParamName, value, options, onChange]
  )

  return {
    value,
    onChange,
    options,
    selectedLabel,
    getApiFilter,
    pageFilterConfig,
  }
}
