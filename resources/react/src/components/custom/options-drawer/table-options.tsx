import type { Table, VisibilityState } from '@tanstack/react-table'
import { useState } from 'react'

import type { LockedFilter } from '@/components/custom/filter-panel'
import { useGlobalFilters } from '@/hooks/use-global-filters'

import { ColumnsDetailView, ColumnsMenuEntry } from './columns-section'
import { DateRangeDetailView, DateRangeMenuEntry } from './date-range-section'
import { FiltersDetailView, FiltersMenuEntry } from './filters-section'
import { OptionsDrawer } from './options-drawer'

/**
 * Configuration for table pages (visitors, top-pages, referrers, etc.)
 */
export interface TableOptionsConfig<TData> {
  filterGroup: string
  table: Table<TData> | null
  lockedFilters?: LockedFilter[]
  initialColumnOrder?: string[]
  defaultHiddenColumns?: string[]
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

  // Count hidden columns (handle null table)
  const columns = config.table?.getAllColumns().filter((col) => col.getCanHide()) ?? []
  const hiddenColumnCount = columns.filter((col) => !col.getIsVisible()).length
  const appliedFilterCount = filters?.length ?? 0

  const isActive = hiddenColumnCount > 0 || appliedFilterCount > 0

  return {
    isOpen,
    setIsOpen,
    isActive,
    hiddenColumnCount,
    appliedFilterCount,
    triggerProps: {
      onClick: () => setIsOpen(true),
      isActive,
    },
  }
}

export interface TableOptionsDrawerProps<TData> {
  config: TableOptionsConfig<TData>
  isOpen: boolean
  setIsOpen: (open: boolean) => void
}

/**
 * Pre-configured Options drawer for table pages.
 * Includes: DateRange, Filters, Columns
 */
export function TableOptionsDrawer<TData>({
  config,
  isOpen,
  setIsOpen,
}: TableOptionsDrawerProps<TData>) {
  return (
    <OptionsDrawer open={isOpen} onOpenChange={setIsOpen} onReset={config.onReset}>
      {/* Main menu entries */}
      <DateRangeMenuEntry />
      <FiltersMenuEntry filterGroup={config.filterGroup} lockedFilters={config.lockedFilters} />
      <ColumnsMenuEntry table={config.table} />

      {/* Detail views */}
      <DateRangeDetailView />
      <FiltersDetailView filterGroup={config.filterGroup} lockedFilters={config.lockedFilters} />
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
