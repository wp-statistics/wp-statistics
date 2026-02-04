/* eslint-disable react-refresh/only-export-components */

import type { Table, VisibilityState } from '@tanstack/react-table'
import { useState } from 'react'

import type { LockedFilter } from '@/components/custom/filter-panel'
import { useGlobalFilters } from '@/hooks/use-global-filters'

import { ColumnsDetailView, ColumnsMenuEntry } from './columns-section'
import { DateRangeDetailView, DateRangeMenuEntry } from './date-range-section'
import { FiltersDetailView, FiltersMenuEntry } from './filters-section'
import { OptionsDrawer } from './options-drawer'
import { type PageFilterConfig,PageFiltersDetailView, PageFiltersMenuEntry } from './page-filters-section'

/**
 * Configuration for table pages (visitors, top-pages, referrers, etc.)
 */
export interface TableOptionsConfig<TData> {
  filterGroup: string
  table: Table<TData> | null
  lockedFilters?: LockedFilter[]
  /** Hide the filters section (for pages that don't use filtering) */
  hideFilters?: boolean
  /** Page-specific filter dropdowns (shown in Options drawer) */
  pageFilters?: PageFilterConfig[]
  /** The default column order (derived from column definitions) */
  initialColumnOrder?: string[]
  /** The current persisted column order (from useDataTablePreferences) */
  columnOrder?: string[]
  defaultHiddenColumns?: string[]
  /** Persisted visibility state from useDataTablePreferences - used for reliable change detection after navigation */
  initialColumnVisibility?: VisibilityState
  comparableColumns?: string[]
  comparisonColumns?: string[]
  defaultComparisonColumns?: string[]
  onColumnVisibilityChange?: (visibility: VisibilityState) => void
  onColumnOrderChange?: (order: string[]) => void
  onComparisonColumnsChange?: (columns: string[]) => void
  onReset?: () => void
}

/**
 * Hook that provides everything needed for the Options button and drawer on table pages.
 * Handles the case where table may be null (before DataTable renders).
 */
export function useTableOptions<TData>(config: TableOptionsConfig<TData>) {
  const [isOpen, setIsOpen] = useState(false)
  const { filters } = useGlobalFilters()

  const defaultHiddenColumns = config.defaultHiddenColumns ?? []

  // Check if current hidden columns differ from default
  // Use persisted visibility if available (reliable after navigation), otherwise fall back to table state
  const hasNonDefaultHiddenColumns = (() => {
    if (config.initialColumnVisibility && Object.keys(config.initialColumnVisibility).length > 0) {
      // Use persisted visibility (reliable after navigation)
      const hiddenFromPrefs = Object.entries(config.initialColumnVisibility)
        .filter(([_, visible]) => !visible)
        .map(([id]) => id)

      return (
        hiddenFromPrefs.length !== defaultHiddenColumns.length ||
        hiddenFromPrefs.some((id) => !defaultHiddenColumns.includes(id)) ||
        defaultHiddenColumns.some((id) => !hiddenFromPrefs.includes(id))
      )
    }

    // Fallback to table state (before preferences load)
    if (!config.table) return false
    const columns = config.table.getAllColumns().filter((col) => col.getCanHide())
    const hiddenColumns = columns.filter((col) => !col.getIsVisible()).map((col) => col.id)

    return (
      hiddenColumns.length !== defaultHiddenColumns.length ||
      hiddenColumns.some((id) => !defaultHiddenColumns.includes(id)) ||
      defaultHiddenColumns.some((id) => !hiddenColumns.includes(id))
    )
  })()

  // Check if current column order differs from initial order
  // Use persisted columnOrder prop (not table state) to reliably detect changes after navigation
  // Note: columnOrder only contains visible columns, so we compare relative order, not absolute positions
  const currentColumnOrder = config.columnOrder ?? []
  const hasNonDefaultColumnOrder =
    config.initialColumnOrder && currentColumnOrder.length > 0
      ? (() => {
          // Filter initialColumnOrder to only include columns present in currentColumnOrder
          // This allows us to compare relative ordering without false positives from hidden columns
          const expectedOrder = config.initialColumnOrder.filter((id) => currentColumnOrder.includes(id))
          // If lengths differ after filtering, a column was added that wasn't in initial order (edge case)
          if (expectedOrder.length !== currentColumnOrder.length) return true
          // Compare the filtered expected order with current order
          return expectedOrder.some((id, index) => currentColumnOrder[index] !== id)
        })()
      : false

  const appliedFilterCount = filters?.length ?? 0
  const isActive = hasNonDefaultHiddenColumns || hasNonDefaultColumnOrder || appliedFilterCount > 0

  // Compute hidden column count from persisted visibility or table state
  const hiddenColumnCount = (() => {
    if (config.initialColumnVisibility && Object.keys(config.initialColumnVisibility).length > 0) {
      return Object.values(config.initialColumnVisibility).filter((visible) => !visible).length
    }
    if (!config.table) return 0
    const columns = config.table.getAllColumns().filter((col) => col.getCanHide())
    return columns.filter((col) => !col.getIsVisible()).length
  })()

  return {
    isOpen,
    setIsOpen,
    config, // Return config for drawer - single source of truth
    isActive,
    hiddenColumnCount,
    hasNonDefaultHiddenColumns,
    appliedFilterCount,
    triggerProps: {
      onClick: () => setIsOpen(true),
      isActive,
    },
  }
}

/**
 * Return type of useTableOptions hook for use with TableOptionsDrawer
 */
export type TableOptionsReturn<TData> = ReturnType<typeof useTableOptions<TData>>

export interface TableOptionsDrawerProps<TData> {
  config: TableOptionsConfig<TData>
  isOpen: boolean
  setIsOpen: (open: boolean) => void
}

/**
 * Props for TableOptionsDrawer when spreading from useTableOptions return
 */
export type TableOptionsDrawerSpreadProps<TData> = Pick<
  TableOptionsReturn<TData>,
  'config' | 'isOpen' | 'setIsOpen'
>

/**
 * Pre-configured Options drawer for table pages.
 * Includes: DateRange, Filters, Columns
 *
 * Can be used with explicit props or by spreading useTableOptions return:
 * @example
 * const options = useTableOptions(config)
 * <TableOptionsDrawer {...options} />
 */
export function TableOptionsDrawer<TData>({
  config,
  isOpen,
  setIsOpen,
}: TableOptionsDrawerProps<TData> | TableOptionsDrawerSpreadProps<TData>) {
  return (
    <OptionsDrawer open={isOpen} onOpenChange={setIsOpen} onReset={config.onReset}>
      {/* Main menu entries */}
      <DateRangeMenuEntry />
      {config.pageFilters && config.pageFilters.length > 0 && (
        <PageFiltersMenuEntry filters={config.pageFilters} />
      )}
      {!config.hideFilters && (
        <FiltersMenuEntry filterGroup={config.filterGroup} lockedFilters={config.lockedFilters} />
      )}
      <ColumnsMenuEntry
        table={config.table}
        defaultHiddenColumns={config.defaultHiddenColumns}
        initialColumnVisibility={config.initialColumnVisibility}
      />

      {/* Detail views */}
      <DateRangeDetailView />
      {config.pageFilters && config.pageFilters.length > 0 && (
        <PageFiltersDetailView filters={config.pageFilters} />
      )}
      {!config.hideFilters && (
        <FiltersDetailView filterGroup={config.filterGroup} lockedFilters={config.lockedFilters} />
      )}
      <ColumnsDetailView
        table={config.table}
        initialColumnOrder={config.initialColumnOrder}
        defaultHiddenColumns={config.defaultHiddenColumns}
        comparableColumns={config.comparableColumns}
        comparisonColumns={config.comparisonColumns}
        defaultComparisonColumns={config.defaultComparisonColumns}
        onColumnVisibilityChange={config.onColumnVisibilityChange}
        onColumnOrderChange={config.onColumnOrderChange}
        onComparisonColumnsChange={config.onComparisonColumnsChange}
        onReset={config.onReset}
      />
    </OptionsDrawer>
  )
}
