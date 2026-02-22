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
  createTimezonesColumns,
  type TimezoneData,
  TIMEZONES_COLUMN_CONFIG,
  TIMEZONES_COMPARABLE_COLUMNS,
  TIMEZONES_CONTEXT,
  TIMEZONES_DEFAULT_API_COLUMNS,
  TIMEZONES_DEFAULT_COMPARISON_COLUMNS,
  TIMEZONES_DEFAULT_HIDDEN_COLUMNS,
  transformTimezoneData,
} from '@/components/data-table-columns/timezones-columns'
import { NoticeContainer } from '@/components/ui/notice-container'
import { PanelSkeleton, TableSkeleton } from '@/components/ui/skeletons'
import { useComparisonDateLabel } from '@/hooks/use-comparison-date-label'
import { useDataTablePreferences } from '@/hooks/use-data-table-preferences'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { useUrlSortSync } from '@/hooks/use-url-sort-sync'
import { extractTimezonesData, getTimezonesQueryOptions } from '@/services/geographic/get-timezones'

const PER_PAGE = 25

export const Route = createLazyFileRoute('/(geographic)/timezones')({
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

  const tableRef = useRef<Table<TimezoneData> | null>(null)

  const { label: comparisonLabel } = useComparisonDateLabel()

  const baseColumns = useMemo(
    () => createTimezonesColumns({ comparisonLabel }),
    [comparisonLabel]
  )

  const {
    data: response,
    isLoading,
    isFetching,
    isError,
    error,
  } = useQuery({
    ...getTimezonesQueryOptions({
      page,
      per_page: PER_PAGE,
      order_by: orderBy,
      order: order as 'asc' | 'desc',
      date_from: apiDateParams.date_from,
      date_to: apiDateParams.date_to,
      previous_date_from: apiDateParams.previous_date_from,
      previous_date_to: apiDateParams.previous_date_to,
      filters: appliedFilters,
      context: TIMEZONES_CONTEXT,
      columns: TIMEZONES_DEFAULT_API_COLUMNS,
    }),
    placeholderData: keepPreviousData,
    enabled: isInitialized,
  })

  const { rows, meta } = useMemo(() => extractTimezonesData(response), [response])

  const preferencesFromApi = response?.data?.items?.timezones?.meta?.preferences?.columns
  const comparisonColumnsFromApi = response?.data?.items?.timezones?.meta?.preferences?.comparison_columns

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
    context: TIMEZONES_CONTEXT,
    columns: baseColumns,
    defaultHiddenColumns: TIMEZONES_DEFAULT_HIDDEN_COLUMNS,
    defaultApiColumns: TIMEZONES_DEFAULT_API_COLUMNS,
    columnConfig: TIMEZONES_COLUMN_CONFIG,
    sorting,
    defaultSortColumn: 'visitors',
    preferencesFromApi,
    hasApiResponse: !!response?.data,
    defaultComparisonColumns: TIMEZONES_DEFAULT_COMPARISON_COLUMNS,
    comparisonColumnsFromApi,
  })

  const options = useTableOptions({
    filterGroup: 'visitors',
    table: tableRef.current,
    initialColumnOrder: defaultColumnOrder,
    columnOrder,
    defaultHiddenColumns: TIMEZONES_DEFAULT_HIDDEN_COLUMNS,
    initialColumnVisibility,
    comparableColumns: TIMEZONES_COMPARABLE_COLUMNS,
    comparisonColumns,
    defaultComparisonColumns: TIMEZONES_DEFAULT_COMPARISON_COLUMNS,
    onColumnVisibilityChange: handleColumnVisibilityChange,
    onColumnOrderChange: handleColumnOrderChange,
    onComparisonColumnsChange: handleComparisonColumnsChange,
    onReset: handleColumnPreferencesReset,
  })

  const columns = useMemo(
    () => createTimezonesColumns({ comparisonLabel, comparisonColumns }),
    [comparisonLabel, comparisonColumns]
  )

  const tableData = useMemo(() => {
    return rows.map((record) => transformTimezoneData(record))
  }, [rows])

  const totalRows = meta?.totalRows ?? 0
  const totalPages = meta?.totalPages ?? 1

  const showSkeleton = isLoading && !response

  return (
    <div className="min-w-0">
      <ReportPageHeader
        title={__('Timezones', 'wp-statistics')}
        filterGroup="visitors"
        optionsTriggerProps={options.triggerProps}
      />

      <TableOptionsDrawer {...options} />

      <div className="p-3">
        <NoticeContainer className="mb-2" currentRoute="timezones" />

        {isError ? (
          <div className="p-2 text-center">
            <ErrorMessage message={__('Failed to load timezones', 'wp-statistics')} />
            <p className="text-sm text-muted-foreground">{error?.message}</p>
          </div>
        ) : showSkeleton ? (
          <PanelSkeleton titleWidth="w-24">
            <TableSkeleton rows={10} columns={3} />
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
            hiddenColumns={TIMEZONES_DEFAULT_HIDDEN_COLUMNS}
            initialColumnVisibility={initialColumnVisibility}
            columnOrder={columnOrder.length > 0 ? columnOrder : undefined}
            onColumnVisibilityChange={handleColumnVisibilityChange}
            onColumnOrderChange={handleColumnOrderChange}
            onColumnPreferencesReset={handleColumnPreferencesReset}
            comparableColumns={TIMEZONES_COMPARABLE_COLUMNS}
            comparisonColumns={comparisonColumns}
            defaultComparisonColumns={TIMEZONES_DEFAULT_COMPARISON_COLUMNS}
            onComparisonColumnsChange={handleComparisonColumnsChange}
            emptyStateMessage={__('No timezones found for the selected period', 'wp-statistics')}
            stickyHeader={true}
            borderless
            tableRef={tableRef}
          />
        )}
      </div>
    </div>
  )
}
