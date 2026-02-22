import { useNavigate, useSearch } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { useCallback, useMemo } from 'react'

import type { PageFilterConfig } from '@/components/custom/options-drawer'
import type { ApiFilters } from '@/lib/api-filter-transform'

export type SearchType = 'all' | 'organic' | 'paid'

export interface UseSearchTypeFilterOptions {
  /** Default value when URL param is not set. Default: 'all' */
  defaultValue?: SearchType
  /** Whether to sync the value to URL. Default: true */
  syncToUrl?: boolean
  /** URL parameter name. Default: 'search_type' */
  urlParamName?: string
}

export interface UseSearchTypeFilterResult {
  /** Current selected value */
  value: SearchType
  /** Handler to change the value */
  onChange: (value: SearchType) => void
  /** Available options for the dropdown */
  options: { value: SearchType; label: string }[]
  /** Display label for the selected type */
  selectedLabel: string
  /** Get API filter based on current selection */
  getApiFilter: () => ApiFilters
  /** Ready-to-use config for PageFilters in options drawer */
  pageFilterConfig: PageFilterConfig
}

/**
 * Hook for managing search type filter state with URL synchronization.
 *
 * Provides a unified way to filter search engine traffic by type (All/Organic/Paid),
 * with automatic URL sync and API filter generation.
 *
 * @example
 * ```tsx
 * const { value, onChange, options, getApiFilter } = useSearchTypeFilter()
 *
 * // Use in header
 * <SearchTypeSelect value={value} onValueChange={onChange} options={options} />
 *
 * // Get API filter for query
 * const apiFilter = getApiFilter()
 * // Returns: { referrer_channel: { in: ['search', 'paid'] } } for 'all'
 * // Returns: { referrer_channel: { is: 'search' } } for 'organic'
 * // Returns: { referrer_channel: { is: 'paid' } } for 'paid'
 * ```
 */
export function useSearchTypeFilter({
  defaultValue = 'all',
  syncToUrl = true,
  urlParamName = 'search_type',
}: UseSearchTypeFilterOptions = {}): UseSearchTypeFilterResult {
  const navigate = useNavigate()

  // Get value from URL or default
  const search = useSearch({ strict: false }) as Record<string, unknown>
  const urlValue = search[urlParamName] as SearchType | undefined
  const value = urlValue || defaultValue

  // Define search type options
  const options = useMemo<{ value: SearchType; label: string }[]>(
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
    (newValue: SearchType) => {
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
        return { referrer_channel: { is: 'search' } }
      case 'paid':
        return { referrer_channel: { is: 'paid' } }
      case 'all':
      default:
        return { referrer_channel: { in: ['search', 'paid'] } }
    }
  }, [value])

  // Build PageFilterConfig for options drawer
  const pageFilterConfig = useMemo<PageFilterConfig>(
    () => ({
      id: urlParamName,
      label: __('Search Type', 'wp-statistics'),
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
