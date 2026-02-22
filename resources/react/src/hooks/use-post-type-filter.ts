import { useNavigate, useSearch } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { useCallback, useMemo } from 'react'

import type { PageFilterConfig } from '@/components/custom/options-drawer'
import { WordPress } from '@/lib/wordpress'

export interface UsePostTypeFilterOptions {
  /** Default value when URL param is not set */
  defaultValue?: string
  /** Whether to sync the value to URL. Default: true */
  syncToUrl?: boolean
  /** URL parameter name. Default: 'post_type' */
  urlParamName?: string
}

export interface UsePostTypeFilterResult {
  /** Current selected value */
  value: string
  /** Handler to change the value */
  onChange: (value: string) => void
  /** Available options for the dropdown */
  options: { value: string; label: string }[]
  /** Ready-to-use config for PageFilters in options drawer */
  pageFilterConfig: PageFilterConfig
}

/**
 * Hook for managing post type filter state with URL synchronization.
 *
 * Provides a unified way to handle post type filtering across pages,
 * with automatic URL sync and options drawer integration.
 *
 * @example
 * ```tsx
 * const { value: postType, onChange, pageFilterConfig } = usePostTypeFilter()
 *
 * // Use in header
 * <PostTypeSelect value={postType} onValueChange={onChange} />
 *
 * // Use in options drawer
 * const pageFilters = useMemo(() => [pageFilterConfig], [pageFilterConfig])
 * const optionsConfig = { ...BASE_CONFIG, pageFilters }
 * ```
 */
export function usePostTypeFilter({
  defaultValue = 'post',
  syncToUrl = true,
  urlParamName = 'post_type',
}: UsePostTypeFilterOptions = {}): UsePostTypeFilterResult {
  const navigate = useNavigate()

  // Get value from URL or default
  // Note: Route must include post_type in validateSearch
  const search = useSearch({ strict: false }) as Record<string, unknown>
  const urlValue = search[urlParamName] as string | undefined
  const value = urlValue || defaultValue

  // Get post type options from WordPress
  const options = useMemo(() => {
    const wp = WordPress.getInstance()
    const postTypeField = wp.getFilterFields()?.post_type
    return (
      postTypeField?.options?.map((opt) => ({
        value: String(opt.value),
        label: opt.label,
      })) ?? [
        { value: 'post', label: __('Post', 'wp-statistics') },
        { value: 'page', label: __('Page', 'wp-statistics') },
      ]
    )
  }, [])

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

  // Build PageFilterConfig for options drawer
  const pageFilterConfig = useMemo<PageFilterConfig>(
    () => ({
      id: urlParamName,
      label: __('Post Type', 'wp-statistics'),
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
    pageFilterConfig,
  }
}
