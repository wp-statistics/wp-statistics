import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'
import type { Row, Table } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'
import { Loader2 } from 'lucide-react'
import { useMemo, useRef } from 'react'

import type { Filter } from '@/components/custom/filter-bar'
import { DataTable } from '@/components/custom/data-table'
import { ErrorMessage } from '@/components/custom/error-message'
import { TableOptionsDrawer, useTableOptions } from '@/components/custom/options-drawer'
import { ReportPageHeader } from '@/components/custom/report-page-header'
import {
  BROWSERS_COMPARABLE_COLUMNS,
  BROWSERS_CONTEXT,
  BROWSERS_DEFAULT_COMPARISON_COLUMNS,
  type BrowserData,
  createBrowsersColumns,
  transformBrowserData,
} from '@/components/data-table-columns/browsers-columns'
import { NumericCell } from '@/components/data-table-columns'
import { NoticeContainer } from '@/components/ui/notice-container'
import { PanelSkeleton, TableSkeleton } from '@/components/ui/skeletons'
import { useComparisonDateLabel } from '@/hooks/use-comparison-date-label'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { useUrlSortSync } from '@/hooks/use-url-sort-sync'
import {
  extractBrowsersData,
  extractBrowserVersionsData,
  getBrowsersQueryOptions,
  getBrowserVersionsQueryOptions,
} from '@/services/devices/get-browsers'

const PER_PAGE = 25

export const Route = createLazyFileRoute('/(devices)/browsers')({
  component: RouteComponent,
})

/**
 * Sub-component rendered when a browser row is expanded.
 * Fetches and displays browser version breakdown.
 * Renders as table rows aligned with parent columns.
 */
function BrowserVersionsSubRow({ row, apiDateParams, filters }: { row: Row<BrowserData>; apiDateParams: { date_from: string; date_to: string; previous_date_from?: string; previous_date_to?: string }; filters?: Filter[] }) {
  const browserId = row.original.browserId

  const { data: response, isLoading } = useQuery({
    ...getBrowserVersionsQueryOptions({
      browserId,
      date_from: apiDateParams.date_from,
      date_to: apiDateParams.date_to,
      previous_date_from: apiDateParams.previous_date_from,
      previous_date_to: apiDateParams.previous_date_to,
      filters,
    }),
    placeholderData: keepPreviousData,
  })

  const versions = useMemo(() => extractBrowserVersionsData(response), [response])

  if (isLoading) {
    return (
      <div className="flex items-center justify-center py-4">
        <Loader2 className="h-4 w-4 animate-spin text-neutral-400" />
      </div>
    )
  }

  if (versions.length === 0) {
    return (
      <div className="pl-14 py-3 text-xs text-neutral-500">
        {__('No version data available', 'wp-statistics')}
      </div>
    )
  }

  return (
    <div className="border-t border-neutral-200">
      <table className="w-full">
        <thead>
          <tr className="bg-neutral-100/60">
            <td className="pl-14 py-1.5 text-xs font-medium text-neutral-500">
              {__('Version', 'wp-statistics')}
            </td>
            <td className="py-1.5 text-right text-xs font-medium text-neutral-500 pr-4" style={{ width: 120 }}>
              {__('Visitors', 'wp-statistics')}
            </td>
          </tr>
        </thead>
        <tbody>
          {versions.map((v) => (
            <tr
              key={v.browser_version}
              className="border-t border-neutral-100 bg-neutral-50 hover:bg-neutral-100/50"
            >
              <td className="pl-14 py-1.5 text-xs font-medium text-neutral-700">
                {v.browser_version || __('Unknown', 'wp-statistics')}
              </td>
              <td className="py-1.5 pr-4" style={{ width: 120 }}>
                <NumericCell value={Number(v.visitors) || 0} />
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  )
}

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

  const tableRef = useRef<Table<BrowserData> | null>(null)
  const { label: comparisonLabel } = useComparisonDateLabel()

  const {
    data: response,
    isLoading,
    isFetching,
    isError,
    error,
  } = useQuery({
    ...getBrowsersQueryOptions({
      page,
      per_page: PER_PAGE,
      order_by: orderBy,
      order: order as 'asc' | 'desc',
      date_from: apiDateParams.date_from,
      date_to: apiDateParams.date_to,
      previous_date_from: apiDateParams.previous_date_from,
      previous_date_to: apiDateParams.previous_date_to,
      filters: appliedFilters,
      context: BROWSERS_CONTEXT,
    }),
    placeholderData: keepPreviousData,
    enabled: isInitialized,
  })

  const { rows, totals, meta } = useMemo(() => extractBrowsersData(response), [response])

  const comparisonColumns = BROWSERS_DEFAULT_COMPARISON_COLUMNS

  const options = useTableOptions({
    filterGroup: 'visitors',
    table: tableRef.current,
    defaultHiddenColumns: [],
    comparableColumns: BROWSERS_COMPARABLE_COLUMNS,
    comparisonColumns,
    defaultComparisonColumns: BROWSERS_DEFAULT_COMPARISON_COLUMNS,
  })

  const columns = useMemo(
    () => createBrowsersColumns({ comparisonLabel, comparisonColumns }),
    [comparisonLabel, comparisonColumns]
  )

  const totalVisitors = totals.visitors
  const tableData = useMemo(() => {
    return rows.map((record) => transformBrowserData(record, totalVisitors))
  }, [rows, totalVisitors])

  const totalRows = meta?.totalRows ?? 0
  const totalPages = meta?.totalPages ?? 1

  const showSkeleton = isLoading && !response

  return (
    <div className="min-w-0">
      <ReportPageHeader
        title={__('Browsers', 'wp-statistics')}
        filterGroup="visitors"
        optionsTriggerProps={options.triggerProps}
      />

      <TableOptionsDrawer {...options} />

      <div className="p-3">
        <NoticeContainer className="mb-2" currentRoute="browsers" />

        {isError ? (
          <div className="p-2 text-center">
            <ErrorMessage message={__('Failed to load browsers', 'wp-statistics')} />
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
            emptyStateMessage={__('No browsers found for the selected period', 'wp-statistics')}
            stickyHeader={true}
            borderless
            tableRef={tableRef}
            getRowCanExpand={() => true}
            renderSubComponent={({ row }) => <BrowserVersionsSubRow row={row} apiDateParams={apiDateParams} filters={appliedFilters} />}
          />
        )}
      </div>
    </div>
  )
}
