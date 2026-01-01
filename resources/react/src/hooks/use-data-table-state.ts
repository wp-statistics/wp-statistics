/**
 * Shared hook for data table state management
 * Extracts common patterns from visitor insight routes
 */

import type { SortingState, VisibilityState } from '@tanstack/react-table'
import { useCallback, useEffect, useMemo, useRef, useState } from 'react'

import {
  clearCachedColumns,
  type ColumnConfig,
  computeApiColumns,
  getCachedApiColumns,
  getDefaultApiColumns,
  getVisibleColumnsForSave,
  setCachedColumns,
} from '@/lib/column-utils'
import {
  computeFullVisibility,
  parseColumnPreferences,
  resetUserPreferences,
  saveUserPreferences,
} from '@/services/user-preferences'

export interface UseDataTableStateOptions {
  /**
   * Column configuration for API optimization
   */
  columnConfig: ColumnConfig
  /**
   * Default sorting state
   */
  defaultSort: SortingState
  /**
   * Columns hidden by default
   */
  defaultHiddenColumns: string[]
  /**
   * All hideable column IDs from column definitions
   */
  allColumnIds: string[]
  /**
   * Column preferences from API response (meta.preferences.columns)
   */
  columnPreferences?: string[] | null
  /**
   * Whether the API data has loaded
   */
  hasData: boolean
}

export interface UseDataTableStateReturn {
  // State values
  sorting: SortingState
  page: number
  columnOrder: string[]
  apiColumns: string[]
  initialColumnVisibility: VisibilityState

  // Setters
  setSorting: (sorting: SortingState) => void
  setPage: (page: number) => void

  // Handlers
  handleSortingChange: (newSorting: SortingState) => void
  handlePageChange: (newPage: number) => void
  handleColumnVisibilityChange: (visibility: VisibilityState) => void
  handleColumnOrderChange: (order: string[]) => void
  handleColumnPreferencesReset: () => void
}

export function useDataTableState(options: UseDataTableStateOptions): UseDataTableStateReturn {
  const {
    columnConfig,
    defaultSort,
    defaultHiddenColumns,
    allColumnIds,
    columnPreferences,
    hasData,
  } = options

  const context = columnConfig.context

  // Default API columns when no preferences are set
  const DEFAULT_API_COLUMNS = useMemo(() => getDefaultApiColumns(columnConfig), [columnConfig])

  // Core state
  const [sorting, setSorting] = useState<SortingState>(defaultSort)
  const [page, setPage] = useState(1)
  const [columnOrder, setColumnOrder] = useState<string[]>([])

  // Track API columns for query optimization
  const [apiColumns, setApiColumns] = useState<string[]>(() => {
    return getCachedApiColumns(allColumnIds, columnConfig) || DEFAULT_API_COLUMNS
  })

  // Track if preferences have been applied
  const hasAppliedPrefs = useRef(false)
  const computedVisibilityRef = useRef<VisibilityState | null>(null)
  const computedColumnOrderRef = useRef<string[] | null>(null)
  const currentVisibilityRef = useRef<VisibilityState>({})
  const hasInitialPrefSync = useRef(false)
  const emptyVisibilityRef = useRef<VisibilityState>({})

  // Helper to compare arrays
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

    // Wait for API response before computing visibility
    if (!hasData) {
      return emptyVisibilityRef.current
    }

    // If no preferences in API response (new user or reset), use defaults
    if (!columnPreferences || columnPreferences.length === 0) {
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
    const { visibleColumnsSet, columnOrder: newOrder } = parseColumnPreferences(columnPreferences)
    const visibility = computeFullVisibility(visibleColumnsSet, allColumnIds)

    // Mark as applied and cache the result
    hasAppliedPrefs.current = true
    computedVisibilityRef.current = visibility
    currentVisibilityRef.current = visibility
    computedColumnOrderRef.current = newOrder

    return visibility
     
  }, [hasData, columnPreferences, allColumnIds, defaultHiddenColumns])

  // Sync column order when preferences are computed (only once on initial load)
  useEffect(() => {
    if (hasAppliedPrefs.current && computedVisibilityRef.current && !hasInitialPrefSync.current) {
      hasInitialPrefSync.current = true
      if (computedColumnOrderRef.current && computedColumnOrderRef.current.length > 0) {
        setColumnOrder(computedColumnOrderRef.current)
      }
    }
  }, [initialColumnVisibility])

  // Handle sorting changes
  const handleSortingChange = useCallback((newSorting: SortingState) => {
    setSorting(newSorting)
    setPage(1) // Reset to first page when sorting changes
  }, [])

  // Handle page changes
  const handlePageChange = useCallback((newPage: number) => {
    setPage(newPage)
  }, [])

  // Handle column visibility changes
  const handleColumnVisibilityChange = useCallback(
    (visibility: VisibilityState) => {
      currentVisibilityRef.current = visibility
      const visibleColumns = getVisibleColumnsForSave(visibility, columnOrder, allColumnIds)
      saveUserPreferences({ context, columns: visibleColumns })
      setCachedColumns(context, visibleColumns)

      const currentSortColumn = sorting.length > 0 ? sorting[0].id : defaultSort[0]?.id || 'lastVisit'
      const newApiColumns = computeApiColumns(visibility, allColumnIds, columnConfig, currentSortColumn)
      setApiColumns((prev) => (arraysEqual(prev, newApiColumns) ? prev : newApiColumns))
    },
    [columnOrder, sorting, allColumnIds, columnConfig, context, defaultSort, arraysEqual]
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
    const defaultVisibility = defaultHiddenColumns.reduce(
      (acc, col) => ({ ...acc, [col]: false }),
      {} as VisibilityState
    )
    computedVisibilityRef.current = defaultVisibility
    currentVisibilityRef.current = defaultVisibility
    setApiColumns((prev) => (arraysEqual(prev, DEFAULT_API_COLUMNS) ? prev : DEFAULT_API_COLUMNS))
    resetUserPreferences({ context })
    clearCachedColumns(context)
  }, [defaultHiddenColumns, DEFAULT_API_COLUMNS, context, arraysEqual])

  return {
    // State values
    sorting,
    page,
    columnOrder,
    apiColumns,
    initialColumnVisibility,

    // Setters
    setSorting,
    setPage,

    // Handlers
    handleSortingChange,
    handlePageChange,
    handleColumnVisibilityChange,
    handleColumnOrderChange,
    handleColumnPreferencesReset,
  }
}
