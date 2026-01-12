/**
 * Hook for managing DataTable column preferences (visibility, order, API optimization).
 * Extracts common preference logic from report pages to reduce duplication.
 */

import type { ColumnDef, SortingState, VisibilityState } from '@tanstack/react-table'
import { useCallback, useEffect, useMemo, useRef, useState } from 'react'

import {
  clearCachedColumns,
  computeApiColumns,
  type ColumnConfig,
  getCachedApiColumns,
  getCachedVisibility,
  getCachedVisibleColumns,
  getVisibleColumnsForSave,
  setCachedColumns,
} from '@/lib/column-utils'
import {
  computeFullVisibility,
  parseColumnPreferences,
  resetUserPreferences,
  saveUserPreferences,
} from '@/services/user-preferences'

interface UseDataTablePreferencesOptions<TData> {
  /** Unique context identifier for this table's preferences */
  context: string
  /** Column definitions from the table */
  columns: ColumnDef<TData, unknown>[]
  /** Columns hidden by default (when no preferences exist) */
  defaultHiddenColumns: string[]
  /** Default API columns to fetch when no preferences exist */
  defaultApiColumns: string[]
  /** Column configuration for API optimization */
  columnConfig: ColumnConfig
  /** Current sorting state (used for API column optimization) */
  sorting: SortingState
  /** Default sort column ID */
  defaultSortColumn: string
  /** API response containing preferences (response.data.meta.preferences.columns) */
  preferencesFromApi?: string[]
  /** Whether the API response is available */
  hasApiResponse: boolean
}

interface UseDataTablePreferencesReturn {
  /** Current column order */
  columnOrder: string[]
  /** Current API columns for query optimization */
  apiColumns: string[]
  /** Initial column visibility state for DataTable */
  initialColumnVisibility: VisibilityState
  /** Handler for column visibility changes */
  handleColumnVisibilityChange: (visibility: VisibilityState) => void
  /** Handler for column order changes */
  handleColumnOrderChange: (order: string[]) => void
  /** Handler for resetting preferences to defaults */
  handleColumnPreferencesReset: () => void
}

/**
 * Hook for managing DataTable column preferences.
 * Handles visibility, order, caching, API optimization, and persistence.
 */
export function useDataTablePreferences<TData>({
  context,
  columns,
  defaultHiddenColumns,
  defaultApiColumns,
  columnConfig,
  sorting,
  defaultSortColumn,
  preferencesFromApi,
  hasApiResponse,
}: UseDataTablePreferencesOptions<TData>): UseDataTablePreferencesReturn {
  // Get all hideable column IDs from the columns definition
  const allColumnIds = useMemo(() => {
    return columns.filter((col) => col.enableHiding !== false).map((col) => (col as { accessorKey?: string }).accessorKey as string)
  }, [columns])

  // Get cached column order from localStorage
  const getCachedColumnOrder = useCallback((): string[] => {
    return getCachedVisibleColumns(context) || []
  }, [context])

  // Track column order state
  const [columnOrder, setColumnOrder] = useState<string[]>(() => getCachedColumnOrder())

  // Track API columns for query optimization (state so changes trigger refetch)
  const [apiColumns, setApiColumns] = useState<string[]>(() => {
    return getCachedApiColumns(allColumnIds, columnConfig) || defaultApiColumns
  })

  // Track if preferences have been applied (to prevent re-computation on subsequent API responses)
  const hasAppliedPrefs = useRef(false)
  const computedVisibilityRef = useRef<VisibilityState | null>(null)
  const computedColumnOrderRef = useRef<string[] | null>(null)

  // Track current visibility for save operations (updated via callback)
  const currentVisibilityRef = useRef<VisibilityState>({})

  // Track if initial preference sync has been done (to prevent unnecessary refetches)
  const hasInitialPrefSync = useRef(false)

  // Stable empty visibility state to avoid creating new objects on each render
  const emptyVisibilityRef = useRef<VisibilityState>({})

  // Helper to compare two arrays for equality (same elements, same order)
  const arraysEqual = useCallback((a: string[], b: string[]): boolean => {
    if (a.length !== b.length) return false
    return a.every((val, idx) => val === b[idx])
  }, [])

  // Compute initial visibility only once when API returns preferences
  const initialColumnVisibility = useMemo(() => {
    // If we've already computed visibility, return the cached value
    if (hasAppliedPrefs.current && computedVisibilityRef.current) {
      return computedVisibilityRef.current
    }

    // Always prefer localStorage cache - it's updated immediately on changes
    // This prevents race conditions where API returns stale data
    const cachedVisibility = getCachedVisibility(context, allColumnIds)
    if (cachedVisibility) {
      hasAppliedPrefs.current = true
      computedVisibilityRef.current = cachedVisibility
      currentVisibilityRef.current = cachedVisibility
      // Also restore column order from cache
      const cachedColumns = getCachedVisibleColumns(context)
      computedColumnOrderRef.current = cachedColumns || []
      return cachedVisibility
    }

    // No localStorage cache - wait for API or use defaults
    if (!hasApiResponse) {
      return emptyVisibilityRef.current
    }

    // If no preferences in API response (new user or reset), use defaults
    if (!preferencesFromApi || preferencesFromApi.length === 0) {
      const defaultVisibility = defaultHiddenColumns.reduce(
        (acc, col) => ({ ...acc, [col]: false }),
        {} as VisibilityState
      )
      hasAppliedPrefs.current = true
      computedVisibilityRef.current = defaultVisibility
      currentVisibilityRef.current = defaultVisibility
      computedColumnOrderRef.current = []
      return defaultVisibility
    }

    // Parse preferences and compute full visibility
    const { visibleColumnsSet, columnOrder: newOrder } = parseColumnPreferences(preferencesFromApi)
    const visibility = computeFullVisibility(visibleColumnsSet, allColumnIds)

    // Mark as applied and cache the result
    hasAppliedPrefs.current = true
    computedVisibilityRef.current = visibility
    currentVisibilityRef.current = visibility
    computedColumnOrderRef.current = newOrder

    return visibility
  }, [hasApiResponse, preferencesFromApi, allColumnIds, context, defaultHiddenColumns])

  // Sync column order when preferences are computed (only once on initial load)
  useEffect(() => {
    if (hasAppliedPrefs.current && computedVisibilityRef.current && !hasInitialPrefSync.current) {
      hasInitialPrefSync.current = true
      // Sync column order from preferences
      if (computedColumnOrderRef.current && computedColumnOrderRef.current.length > 0) {
        setColumnOrder(computedColumnOrderRef.current)
      }
    }
  }, [initialColumnVisibility])

  // Handle column visibility changes (for persistence and query optimization)
  const handleColumnVisibilityChange = useCallback(
    (visibility: VisibilityState) => {
      currentVisibilityRef.current = visibility
      computedVisibilityRef.current = visibility
      const visibleColumns = getVisibleColumnsForSave(visibility, columnOrder, allColumnIds)
      saveUserPreferences({ context, columns: visibleColumns })
      setCachedColumns(context, visibleColumns)
      // Update API columns for optimized queries (include sort column)
      const currentSortColumn = sorting.length > 0 ? sorting[0].id : defaultSortColumn
      const newApiColumns = computeApiColumns(visibility, allColumnIds, columnConfig, currentSortColumn)
      setApiColumns((prev) => (arraysEqual(prev, newApiColumns) ? prev : newApiColumns))
    },
    [columnOrder, sorting, allColumnIds, context, columnConfig, defaultSortColumn, arraysEqual]
  )

  // Handle column order changes
  const handleColumnOrderChange = useCallback(
    (order: string[]) => {
      setColumnOrder(order)
      const visibleColumns = getVisibleColumnsForSave(currentVisibilityRef.current, order, allColumnIds)
      saveUserPreferences({ context, columns: visibleColumns })
      setCachedColumns(context, visibleColumns)
    },
    [allColumnIds, context]
  )

  // Handle reset to default
  const handleColumnPreferencesReset = useCallback(() => {
    setColumnOrder([])
    // Reset visibility to defaults
    const defaultVisibility = defaultHiddenColumns.reduce(
      (acc, col) => ({ ...acc, [col]: false }),
      {} as VisibilityState
    )
    computedVisibilityRef.current = defaultVisibility
    currentVisibilityRef.current = defaultVisibility
    // Reset API columns to default
    setApiColumns((prev) => (arraysEqual(prev, defaultApiColumns) ? prev : defaultApiColumns))
    // Reset preferences on backend
    resetUserPreferences({ context })
    // Clear localStorage cache
    clearCachedColumns(context)
  }, [context, defaultHiddenColumns, defaultApiColumns, arraysEqual])

  return {
    columnOrder,
    apiColumns,
    initialColumnVisibility,
    handleColumnVisibilityChange,
    handleColumnOrderChange,
    handleColumnPreferencesReset,
  }
}
