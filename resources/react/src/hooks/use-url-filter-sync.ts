/**
 * Custom hook for bidirectional synchronization between URL search params and filter state.
 * Consolidates duplicated filter sync logic from route components.
 */

import { useNavigate } from '@tanstack/react-router'
import { useCallback, useEffect, useRef, useState } from 'react'

import type { Filter } from '@/components/custom/filter-bar'
import type { FilterField } from '@/components/custom/filter-row'
import {
  filtersToUrlFilters,
  serializeFiltersToBracketParams,
  type UrlFilter,
  urlFiltersToFilters,
} from '@/lib/filter-utils'

export interface UseUrlFilterSyncOptions {
  /**
   * URL filters from validated search params
   */
  urlFilters: UrlFilter[] | undefined
  /**
   * URL page from validated search params
   */
  urlPage: number | undefined
  /**
   * Filter field definitions for label/value lookup
   */
  filterFields: FilterField[]
  /**
   * Default filters to apply when URL has no filters (optional)
   */
  defaultFilters?: Filter[]
}

export interface UseUrlFilterSyncResult {
  /**
   * Current applied filters (null until URL sync is complete)
   */
  appliedFilters: Filter[] | null
  /**
   * Update applied filters
   */
  setAppliedFilters: React.Dispatch<React.SetStateAction<Filter[] | null>>
  /**
   * Current page number
   */
  page: number
  /**
   * Update page number
   */
  setPage: React.Dispatch<React.SetStateAction<number>>
  /**
   * Remove a single filter by ID and reset to page 1
   */
  handleRemoveFilter: (filterId: string) => void
  /**
   * Apply new filters and reset to page 1
   */
  handleApplyFilters: (filters: Filter[]) => void
  /**
   * Whether filter sync is initialized (URL has been synced)
   */
  isInitialized: boolean
}

/**
 * Hook that manages bidirectional sync between URL search params and filter/page state.
 *
 * Features:
 * - Parses filters from URL on mount
 * - Syncs filter/page changes back to URL
 * - Prevents unnecessary URL updates
 * - Handles page reset on filter changes
 *
 * @example
 * ```tsx
 * const { filters: urlFilters, page: urlPage } = routeApi.useSearch()
 * const filterFields = wp.getFilterFieldsByGroup('visitors')
 *
 * const {
 *   appliedFilters,
 *   page,
 *   handleRemoveFilter,
 *   handleApplyFilters,
 *   isInitialized,
 * } = useUrlFilterSync({ urlFilters, urlPage, filterFields })
 *
 * // Use isInitialized to conditionally render or enable queries
 * const { data } = useQuery({
 *   ...queryOptions,
 *   enabled: isInitialized,
 * })
 * ```
 */
export function useUrlFilterSync({
  urlFilters,
  urlPage,
  filterFields,
  defaultFilters = [],
}: UseUrlFilterSyncOptions): UseUrlFilterSyncResult {
  const navigate = useNavigate()

  // Track last synced state to prevent infinite loops
  const lastSyncedFiltersRef = useRef<string | null>(null)

  // Initialize filters state - null until URL sync is complete
  const [appliedFilters, setAppliedFilters] = useState<Filter[] | null>(null)

  // Initialize page state
  const [page, setPage] = useState(1)

  // Sync filters FROM URL on mount (only once)
  useEffect(() => {
    // Already initialized
    if (lastSyncedFiltersRef.current !== null) return

    // Parse URL filters
    let filtersFromUrl = urlFiltersToFilters(urlFilters, filterFields)

    // Apply default filters if URL has no filters
    if (filtersFromUrl.length === 0 && defaultFilters.length > 0) {
      filtersFromUrl = defaultFilters
    }

    setAppliedFilters(filtersFromUrl)

    if (urlPage && urlPage > 1) {
      setPage(urlPage)
    }

    // Mark as initialized with the current filter state
    lastSyncedFiltersRef.current = JSON.stringify(filtersToUrlFilters(filtersFromUrl))
  }, [urlFilters, urlPage, filterFields, defaultFilters])

  // Sync filters TO URL when they change (only after initialization and actual change)
  useEffect(() => {
    // Not initialized yet
    if (lastSyncedFiltersRef.current === null || appliedFilters === null) return

    const urlFilterData = filtersToUrlFilters(appliedFilters)
    const bracketParams = serializeFiltersToBracketParams(urlFilterData)
    const serialized = JSON.stringify(urlFilterData)

    // Only sync if actually changed
    if (serialized === lastSyncedFiltersRef.current && page === (urlPage || 1)) return

    lastSyncedFiltersRef.current = serialized

    navigate({
      search: (prev) => {
        // Remove old filter keys (both bracket notation and legacy JSON format)
        const cleaned = Object.fromEntries(
          Object.entries(prev as Record<string, unknown>).filter(
            ([key]) => !key.startsWith('filter[') && key !== 'filters'
          )
        )

        // Build result object without 'filters' key - TanStack Router may serialize undefined values
        const result: Record<string, unknown> = {
          ...cleaned,
          ...bracketParams,
        }

        // Only add page if > 1
        if (page > 1) {
          result.page = page
        }

        // Ensure legacy 'filters' key is removed (in case it was in prev)
        delete result.filters

        return result
      },
      replace: true,
    })
  }, [appliedFilters, page, navigate, urlPage])

  // Remove a single filter by ID
  const handleRemoveFilter = useCallback((filterId: string) => {
    setAppliedFilters((prev) => (prev ? prev.filter((f) => f.id !== filterId) : []))
    setPage(1) // Reset to first page when filters change
  }, [])

  // Apply new filters
  const handleApplyFilters = useCallback((filters: Filter[]) => {
    setAppliedFilters(filters)
    setPage(1) // Reset to first page when filters change
  }, [])

  return {
    appliedFilters,
    setAppliedFilters,
    page,
    setPage,
    handleRemoveFilter,
    handleApplyFilters,
    isInitialized: appliedFilters !== null,
  }
}
