import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'
import type { Table } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'
import { useMemo, useRef } from 'react'

import { DataTable } from '@/components/custom/data-table'
import { ErrorMessage } from '@/components/custom/error-message'
import { TableOptionsDrawer, useTableOptions } from '@/components/custom/options-drawer'
import { ReportPageHeader } from '@/components/custom/report-page-header'
import {
  createRegionColumns,
  REGION_COLUMN_CONFIG,
  REGION_COMPARABLE_COLUMNS,
  REGION_CONTEXT_US_STATES,
  REGION_DEFAULT_API_COLUMNS,
  REGION_DEFAULT_COMPARISON_COLUMNS,
  REGION_DEFAULT_HIDDEN_COLUMNS,
  type RegionData,
  transformRegionData,
} from '@/components/data-table-columns/region-columns'
import { NoticeContainer } from '@/components/ui/notice-container'
import { PanelSkeleton, TableSkeleton } from '@/components/ui/skeletons'
import { useComparisonDateLabel } from '@/hooks/use-comparison-date-label'
import { useDataTablePreferences } from '@/hooks/use-data-table-preferences'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { useUrlSortSync } from '@/hooks/use-url-sort-sync'
import { extractUSStatesData, getUSStatesQueryOptions } from '@/services/geographic/get-us-states'

const PER_PAGE = 25

export const Route = createLazyFileRoute('/(geographic)/us-states')({
  component: RouteComponent,
})

function RouteComponent() {
  const {
    filters: appliedFilters,
    page,
    setPage,
    handlePageChange,
    isInitialized,
    apiDateParams,
  } = useGlobalFilters()

  const { sorting, handleSortingChange, orderBy, order } = useUrlSortSync({
    defaultSort: [{ id: 'visitors', desc: true }],
    onPageReset: () => setPage(1),
  })

  // Table ref for Options drawer column management
  const tableRef = useRef<Table<RegionData> | null>(null)

  // Get comparison date label for tooltip display
  const { label: comparisonLabel } = useComparisonDateLabel()

  // Base columns for preferences hook (stable definition for column IDs)
  const baseColumns = useMemo(
    () => createRegionColumns({ regionTitle: __('State', 'wp-statistics'), comparisonLabel }),
    [comparisonLabel]
  )

  // Fetch data from API
  const {
    data: response,
    isLoading,
    isFetching,
    isError,
    error,
  } = useQuery({
    ...getUSStatesQueryOptions({
      page,
      per_page: PER_PAGE,
      order_by: orderBy,
      order: order as 'asc' | 'desc',
      date_from: apiDateParams.date_from,
      date_to: apiDateParams.date_to,
      previous_date_from: apiDateParams.previous_date_from,
      previous_date_to: apiDateParams.previous_date_to,
      filters: appliedFilters,
      context: REGION_CONTEXT_US_STATES,
      columns: REGION_DEFAULT_API_COLUMNS,
    }),
    placeholderData: keepPreviousData,
    enabled: isInitialized,
  })

  // Extract data using the helper function (handles batch response format)
  const { rows, totals, meta } = useMemo(() => extractUSStatesData(response), [response])

  // Get preferences from the batch response
  const preferencesFromApi = response?.data?.items?.us_states?.meta?.preferences?.columns
  const comparisonColumnsFromApi = response?.data?.items?.us_states?.meta?.preferences?.comparison_columns

  // Use the preferences hook for column management
  const {
    defaultColumnOrder,
    columnOrder,
    initialColumnVisibility,
    comparisonColumns,
    handleColumnVisibilityChange,
    handleColumnOrderChange,
    handleComparisonColumnsChange,
    handleColumnPreferencesReset,
  } = useDataTablePreferences({
    context: REGION_CONTEXT_US_STATES,
    columns: baseColumns,
    defaultHiddenColumns: REGION_DEFAULT_HIDDEN_COLUMNS,
    defaultApiColumns: REGION_DEFAULT_API_COLUMNS,
    columnConfig: REGION_COLUMN_CONFIG,
    sorting,
    defaultSortColumn: 'visitors',
    preferencesFromApi,
    hasApiResponse: !!response?.data,
    defaultComparisonColumns: REGION_DEFAULT_COMPARISON_COLUMNS,
    comparisonColumnsFromApi,
  })

  // Options drawer with column management
  const options = useTableOptions({
    filterGroup: 'visitors',
    table: tableRef.current,
    initialColumnOrder: defaultColumnOrder,
    columnOrder,
    defaultHiddenColumns: REGION_DEFAULT_HIDDEN_COLUMNS,
    initialColumnVisibility,
    comparableColumns: REGION_COMPARABLE_COLUMNS,
    comparisonColumns,
    defaultComparisonColumns: REGION_DEFAULT_COMPARISON_COLUMNS,
    onColumnVisibilityChange: handleColumnVisibilityChange,
    onColumnOrderChange: handleColumnOrderChange,
    onComparisonColumnsChange: handleComparisonColumnsChange,
    onReset: handleColumnPreferencesReset,
  })

  // Final columns with comparison settings applied
  const columns = useMemo(
    () => createRegionColumns({ regionTitle: __('State', 'wp-statistics'), comparisonLabel, comparisonColumns }),
    [comparisonLabel, comparisonColumns]
  )

  // Transform API data to component interface with totals for % of Total
  const totalVisitors = totals.visitors
  const tableData = useMemo(() => {
    return rows.map((record) => transformRegionData(record, totalVisitors))
  }, [rows, totalVisitors])

  const totalRows = meta?.totalRows ?? 0
  const totalPages = meta?.totalPages ?? 1

  const showSkeleton = isLoading && !response

  return (
    <div className="min-w-0">
      <ReportPageHeader
        title={__('US States', 'wp-statistics')}
        filterGroup="visitors"
        optionsTriggerProps={options.triggerProps}
      />

      {/* Options Drawer with Column Management */}
      <TableOptionsDrawer {...options} />

      <div className="p-3">
        <NoticeContainer className="mb-2" currentRoute="us-states" />

        {isError ? (
          <div className="p-2 text-center">
            <ErrorMessage message={__('Failed to load US states data', 'wp-statistics')} />
            <p className="text-sm text-muted-foreground">{error?.message}</p>
          </div>
        ) : showSkeleton ? (
          <PanelSkeleton titleWidth="w-24">
            <TableSkeleton rows={10} columns={4} />
          </PanelSkeleton>
        ) : (
          <DataTable
            columns={columns}
            data={tableData}
            sorting={sorting}
            onSortingChange={handleSortingChange}
            manualSorting={true}
            manualPagination={true}
            pageCount={totalPages}
            page={page}
            onPageChange={handlePageChange}
            totalRows={totalRows}
            rowLimit={PER_PAGE}
            showPagination={true}
            isFetching={isFetching}
            hiddenColumns={REGION_DEFAULT_HIDDEN_COLUMNS}
            initialColumnVisibility={initialColumnVisibility}
            columnOrder={columnOrder.length > 0 ? columnOrder : undefined}
            onColumnVisibilityChange={handleColumnVisibilityChange}
            onColumnOrderChange={handleColumnOrderChange}
            onColumnPreferencesReset={handleColumnPreferencesReset}
            comparableColumns={REGION_COMPARABLE_COLUMNS}
            comparisonColumns={comparisonColumns}
            defaultComparisonColumns={REGION_DEFAULT_COMPARISON_COLUMNS}
            onComparisonColumnsChange={handleComparisonColumnsChange}
            emptyStateMessage={__('No US states data found for the selected period', 'wp-statistics')}
            stickyHeader={true}
            borderless
            tableRef={tableRef}
          />
        )}
      </div>
    </div>
  )
}
