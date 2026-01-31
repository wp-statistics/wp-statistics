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
  OS_COMPARABLE_COLUMNS,
  OS_CONTEXT,
  OS_DEFAULT_COMPARISON_COLUMNS,
  type OsData,
  createOsColumns,
  transformOsData,
} from '@/components/data-table-columns/operating-systems-columns'
import { NoticeContainer } from '@/components/ui/notice-container'
import { PanelSkeleton, TableSkeleton } from '@/components/ui/skeletons'
import { useComparisonDateLabel } from '@/hooks/use-comparison-date-label'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { useUrlSortSync } from '@/hooks/use-url-sort-sync'
import {
  extractOsData,
  getOsQueryOptions,
} from '@/services/devices/get-operating-systems'

const PER_PAGE = 25

export const Route = createLazyFileRoute('/(devices)/operating-systems')({
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

  const tableRef = useRef<Table<OsData> | null>(null)
  const { label: comparisonLabel } = useComparisonDateLabel()

  const {
    data: response,
    isLoading,
    isFetching,
    isError,
    error,
  } = useQuery({
    ...getOsQueryOptions({
      page,
      per_page: PER_PAGE,
      order_by: orderBy,
      order: order as 'asc' | 'desc',
      date_from: apiDateParams.date_from,
      date_to: apiDateParams.date_to,
      previous_date_from: apiDateParams.previous_date_from,
      previous_date_to: apiDateParams.previous_date_to,
      filters: appliedFilters,
      context: OS_CONTEXT,
    }),
    placeholderData: keepPreviousData,
    enabled: isInitialized,
  })

  const { rows, totals, meta } = useMemo(() => extractOsData(response), [response])

  const comparisonColumns = OS_DEFAULT_COMPARISON_COLUMNS

  const options = useTableOptions({
    filterGroup: 'visitors',
    table: tableRef.current,
    defaultHiddenColumns: [],
    comparableColumns: OS_COMPARABLE_COLUMNS,
    comparisonColumns,
    defaultComparisonColumns: OS_DEFAULT_COMPARISON_COLUMNS,
  })

  const columns = useMemo(
    () => createOsColumns({ comparisonLabel, comparisonColumns }),
    [comparisonLabel, comparisonColumns]
  )

  const totalVisitors = totals.visitors
  const tableData = useMemo(() => {
    return rows.map((record) => transformOsData(record, totalVisitors))
  }, [rows, totalVisitors])

  const totalRows = meta?.totalRows ?? 0
  const totalPages = meta?.totalPages ?? 1

  const showSkeleton = isLoading && !response

  return (
    <div className="min-w-0">
      <ReportPageHeader
        title={__('Operating Systems', 'wp-statistics')}
        filterGroup="visitors"
        optionsTriggerProps={options.triggerProps}
      />

      <TableOptionsDrawer {...options} />

      <div className="p-3">
        <NoticeContainer className="mb-2" currentRoute="operating-systems" />

        {isError ? (
          <div className="p-2 text-center">
            <ErrorMessage message={__('Failed to load operating systems', 'wp-statistics')} />
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
            showColumnManagement={false}
            showPagination={true}
            isFetching={isFetching}
            emptyStateMessage={__('No operating systems found for the selected period', 'wp-statistics')}
            stickyHeader={true}
            borderless
            tableRef={tableRef}
          />
        )}
      </div>
    </div>
  )
}
