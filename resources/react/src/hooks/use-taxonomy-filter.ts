import { useCallback, useMemo } from 'react'
import { useNavigate, useSearch } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'

import type { PageFilterConfig } from '@/components/custom/options-drawer'
import { WordPress } from '@/lib/wordpress'

export interface UseTaxonomyFilterOptions {
  /** Default value when URL param is not set. Default: 'category' */
  defaultValue?: string
  /** Whether to sync the value to URL. Default: true */
  syncToUrl?: boolean
  /** URL parameter name. Default: 'taxonomy_type' */
  urlParamName?: string
  /** Filter custom taxonomies for free users. Default: false */
  premiumOnly?: boolean
}

export interface UseTaxonomyFilterResult {
  /** Current selected value */
  value: string
  /** Handler to change the value */
  onChange: (value: string) => void
  /** Available options for the dropdown */
  options: { value: string; label: string }[]
  /** Display label for the selected taxonomy */
  selectedLabel: string
  /** Ready-to-use config for PageFilters in options drawer */
  pageFilterConfig: PageFilterConfig
  /** Helper to build navigation URLs with taxonomy filter preserved */
  buildNavigationUrl: (basePath: string, extraParams?: string) => string
}

/**
 * Hook for managing taxonomy filter state with URL synchronization.
 *
 * Provides a unified way to handle taxonomy filtering across pages (categories, tags, custom),
 * with automatic URL sync, premium filtering, and options drawer integration.
 *
 * @example
 * ```tsx
 * const {
 *   value: taxonomy,
 *   onChange,
 *   selectedLabel,
 *   pageFilterConfig,
 *   buildNavigationUrl
 * } = useTaxonomyFilter({ premiumOnly: true })
 *
 * // Use in header
 * <h1>{selectedLabel}</h1>
 * <TaxonomySelect value={taxonomy} onValueChange={onChange} />
 *
 * // Build URLs with filter preserved
 * const topCategoriesUrl = buildNavigationUrl('/top-categories', 'order_by=views')
 *
 * // Use in options drawer
 * const pageFilters = useMemo(() => [pageFilterConfig], [pageFilterConfig])
 * ```
 */
export function useTaxonomyFilter({
  defaultValue = 'category',
  syncToUrl = true,
  urlParamName = 'taxonomy_type',
  premiumOnly = false,
}: UseTaxonomyFilterOptions = {}): UseTaxonomyFilterResult {
  const navigate = useNavigate()
  const wp = WordPress.getInstance()
  const isPremium = wp.getIsPremium()

  // Get value from URL or default
  const search = useSearch({ strict: false }) as Record<string, unknown>
  const urlValue = search[urlParamName] as string | undefined
  const value = urlValue || defaultValue

  // Get taxonomy options with premium filtering
  const options = useMemo(() => {
    const allTaxonomies = wp.getTaxonomies()
    if (isPremium || !premiumOnly) {
      return allTaxonomies
    }
    // Free: Only category and post_tag
    return allTaxonomies.filter((t) => t.value === 'category' || t.value === 'post_tag')
  }, [wp, isPremium, premiumOnly])

  // Get selected taxonomy label
  const selectedLabel = useMemo(() => {
    const taxonomy = options.find((t) => t.value === value)
    return taxonomy?.label || __('Categories', 'wp-statistics')
  }, [options, value])

  // Handle value change with optional URL sync
  const onChange = useCallback(
    (newValue: string) => {
      if (syncToUrl) {
        navigate({
          search: (prev) => ({
            ...prev,
            [urlParamName]: newValue,
          }),
          replace: true,
        })
      }
    },
    [navigate, syncToUrl, urlParamName]
  )

  // Build navigation URL with taxonomy filter preserved
  const buildNavigationUrl = useCallback(
    (basePath: string, extraParams?: string) => {
      const taxonomyParam = `${urlParamName}=${value}`
      if (extraParams) {
        return `${basePath}?${extraParams}&${taxonomyParam}`
      }
      return `${basePath}?${taxonomyParam}`
    },
    [urlParamName, value]
  )

  // Build PageFilterConfig for options drawer
  const pageFilterConfig = useMemo<PageFilterConfig>(
    () => ({
      id: urlParamName,
      label: __('Taxonomy Type', 'wp-statistics'),
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
    pageFilterConfig,
    buildNavigationUrl,
  }
}
