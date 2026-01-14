/**
 * Custom hook for bidirectional synchronization between URL search params and sorting state.
 * Each page can specify its own default sort, and the URL takes priority when present.
 */

import { useNavigate, useSearch } from '@tanstack/react-router'
import { useCallback, useEffect, useRef, useState } from 'react'
import type { SortingState } from '@tanstack/react-table'

export interface UseUrlSortSyncOptions {
  /**
   * Default sorting state when URL has no sort params
   */
  defaultSort: SortingState
  /**
   * Whether sorting should reset page to 1 when changed
   * @default true
   */
  resetPageOnSort?: boolean
  /**
   * Callback to reset page (typically from useGlobalFilters)
   */
  onPageReset?: () => void
}

export interface UseUrlSortSyncResult {
  /**
   * Current sorting state
   */
  sorting: SortingState
  /**
   * Update sorting state (also syncs to URL)
   */
  setSorting: (sorting: SortingState) => void
  /**
   * Handler for DataTable onSortingChange
   */
  handleSortingChange: (newSorting: SortingState) => void
  /**
   * Computed values for API requests
   */
  orderBy: string
  order: 'asc' | 'desc'
}

/**
 * Hook that manages bidirectional sync between URL search params and sorting state.
 *
 * Features:
 * - Parses sorting from URL on mount
 * - Syncs sorting changes back to URL
 * - Uses page-specific default sort when URL has no sort params
 * - Optionally resets page to 1 when sorting changes
 *
 * @example
 * ```tsx
 * const {
 *   sorting,
 *   handleSortingChange,
 *   orderBy,
 *   order,
 * } = useUrlSortSync({
 *   defaultSort: [{ id: 'lastVisit', desc: true }],
 *   onPageReset: () => setPage(1),
 * })
 *
 * // Use in DataTable
 * <DataTable
 *   sorting={sorting}
 *   onSortingChange={handleSortingChange}
 *   manualSorting={true}
 * />
 *
 * // Use in API query
 * getDataQueryOptions({ order_by: orderBy, order })
 * ```
 */
export function useUrlSortSync({
  defaultSort,
  resetPageOnSort = true,
  onPageReset,
}: UseUrlSortSyncOptions): UseUrlSortSyncResult {
  const navigate = useNavigate()

  // Get URL search params
  let urlParams: { order_by?: string; order?: 'asc' | 'desc' } = {}
  try {
    // eslint-disable-next-line react-hooks/rules-of-hooks
    urlParams = useSearch({ strict: false }) as typeof urlParams
  } catch {
    // Not in a route context yet
  }

  // Track last synced state to prevent infinite loops
  const lastSyncedRef = useRef<string | null>(null)
  const hasInitialized = useRef(false)

  // Initialize sorting state from URL or defaults
  const [sorting, setSortingState] = useState<SortingState>(() => {
    // Check URL params first
    if (urlParams.order_by) {
      return [{ id: urlParams.order_by, desc: urlParams.order === 'desc' }]
    }
    return defaultSort
  })

  // Sync FROM URL on mount and when URL changes
  useEffect(() => {
    // Skip if already initialized and URL hasn't changed
    const currentUrlState = urlParams.order_by
      ? JSON.stringify({ order_by: urlParams.order_by, order: urlParams.order })
      : null

    if (hasInitialized.current && currentUrlState === lastSyncedRef.current) {
      return
    }

    hasInitialized.current = true

    if (urlParams.order_by) {
      const newSorting: SortingState = [{ id: urlParams.order_by, desc: urlParams.order === 'desc' }]
      setSortingState(newSorting)
      lastSyncedRef.current = currentUrlState
    } else {
      // No URL sort params - use default
      setSortingState(defaultSort)
      lastSyncedRef.current = null
    }
  }, [urlParams.order_by, urlParams.order, defaultSort])

  // Sync sorting TO URL when it changes
  const syncToUrl = useCallback(
    (newSorting: SortingState) => {
      const orderBy = newSorting.length > 0 ? newSorting[0].id : defaultSort[0]?.id
      const order = newSorting.length > 0 && newSorting[0].desc ? 'desc' : 'asc'

      // Check if this is the default sort - if so, don't add to URL
      const isDefaultSort =
        defaultSort.length > 0 &&
        orderBy === defaultSort[0].id &&
        (defaultSort[0].desc ? order === 'desc' : order === 'asc')

      const newUrlState = isDefaultSort ? null : JSON.stringify({ order_by: orderBy, order })

      // Only sync if actually changed
      if (newUrlState === lastSyncedRef.current) return

      lastSyncedRef.current = newUrlState

      navigate({
        search: (prev) => {
          const result = { ...prev } as Record<string, unknown>

          if (isDefaultSort) {
            // Remove sort params when using default
            delete result.order_by
            delete result.order
          } else {
            // Add sort params
            result.order_by = orderBy
            result.order = order
          }

          // Reset page to 1 when sorting changes
          if (resetPageOnSort) {
            delete result.page
          }

          return result
        },
        replace: true,
      })
    },
    [navigate, defaultSort, resetPageOnSort]
  )

  // Handler for sorting changes
  const handleSortingChange = useCallback(
    (newSorting: SortingState) => {
      setSortingState(newSorting)
      syncToUrl(newSorting)

      // Reset page if configured
      if (resetPageOnSort && onPageReset) {
        onPageReset()
      }
    },
    [syncToUrl, resetPageOnSort, onPageReset]
  )

  // Set sorting (also syncs to URL)
  const setSorting = useCallback(
    (newSorting: SortingState) => {
      setSortingState(newSorting)
      syncToUrl(newSorting)
    },
    [syncToUrl]
  )

  // Computed values for API requests
  const orderBy = sorting.length > 0 ? sorting[0].id : defaultSort[0]?.id || ''
  const order: 'asc' | 'desc' = sorting.length > 0 && sorting[0].desc ? 'desc' : 'asc'

  return {
    sorting,
    setSorting,
    handleSortingChange,
    orderBy,
    order,
  }
}
