import type { Table } from '@tanstack/react-table'
import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { useMemo, useRef } from 'react'

import { DataTable } from '@/components/custom/data-table'
import { ErrorMessage } from '@/components/custom/error-message'
import type { FilterField } from '@/components/custom/filter-button'
import { TableOptionsDrawer, useTableOptions } from '@/components/custom/options-drawer'
import { ReportPageHeader } from '@/components/custom/report-page-header'
import {
  createTopPagesColumns,
  type TopPage,
  TOP_PAGES_COLUMN_CONFIG,
  TOP_PAGES_COMPARABLE_COLUMNS,
  TOP_PAGES_CONTEXT,
  TOP_PAGES_DEFAULT_API_COLUMNS,
  TOP_PAGES_DEFAULT_COMPARISON_COLUMNS,
  TOP_PAGES_DEFAULT_HIDDEN_COLUMNS,
  transformTopPageData,
} from '@/components/data-table-columns/top-pages-columns'
import { NoticeContainer } from '@/components/ui/notice-container'
import { PanelSkeleton, TableSkeleton } from '@/components/ui/skeletons'
import { useComparisonDateLabel } from '@/hooks/use-comparison-date-label'
import { useDataTablePreferences } from '@/hooks/use-data-table-preferences'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { useUrlSortSync } from '@/hooks/use-url-sort-sync'
import { extractMeta, extractRows } from '@/lib/response-helpers'
import { WordPress } from '@/lib/wordpress'
import { getTopPagesQueryOptions } from '@/services/page-insight/get-top-pages'

const PER_PAGE = 20

export const Route = createLazyFileRoute('/(page-insights)/top-pages')({
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
    defaultSort: [{ id: 'views', desc: true }],
    onPageReset: () => setPage(1),
  })

  // Table ref for Options drawer column management
  const tableRef = useRef<Table<TopPage> | null>(null)

  // Get comparison date label for tooltip display
  const { label: comparisonLabel } = useComparisonDateLabel()

  const wp = WordPress.getInstance()
  // Base columns for preferences hook (stable definition for column IDs)
  const baseColumns = useMemo(() => createTopPagesColumns({ comparisonLabel }), [comparisonLabel])

  // Content-specific filters only
  const CONTENT_FILTERS = ['page', 'resource_id', 'post_type', 'author']

  const customFilterFields = useMemo<FilterField[]>(() => {
    return wp.getFilterFieldsByGroup('views').filter((field) => CONTENT_FILTERS.includes(field.name)) as FilterField[]
  }, [wp])

  // Fetch data from API
  const {
    data: response,
    isLoading,
    isFetching,
    isError,
    error,
  } = useQuery({
    ...getTopPagesQueryOptions({
      page,
      per_page: PER_PAGE,
      order_by: orderBy,
      order: order as 'asc' | 'desc',
      date_from: apiDateParams.date_from,
      date_to: apiDateParams.date_to,
      previous_date_from: apiDateParams.previous_date_from,
      previous_date_to: apiDateParams.previous_date_to,
      filters: appliedFilters || [],
      context: TOP_PAGES_CONTEXT,
      columns: TOP_PAGES_DEFAULT_API_COLUMNS,
    }),
    placeholderData: keepPreviousData,
    enabled: isInitialized,
  })

  // Use the preferences hook for column management
  const {
    columnOrder,
    initialColumnVisibility,
    comparisonColumns,
    handleColumnVisibilityChange,
    handleColumnOrderChange,
    handleComparisonColumnsChange,
    handleColumnPreferencesReset,
  } = useDataTablePreferences({
    context: TOP_PAGES_CONTEXT,
    columns: baseColumns,
    defaultHiddenColumns: TOP_PAGES_DEFAULT_HIDDEN_COLUMNS,
    defaultApiColumns: TOP_PAGES_DEFAULT_API_COLUMNS,
    columnConfig: TOP_PAGES_COLUMN_CONFIG,
    sorting,
    defaultSortColumn: 'views',
    preferencesFromApi: response?.data?.meta?.preferences?.columns,
    hasApiResponse: !!response?.data,
    defaultComparisonColumns: TOP_PAGES_DEFAULT_COMPARISON_COLUMNS,
    comparisonColumnsFromApi: (response?.data?.meta?.preferences as { comparison_columns?: string[] } | undefined)?.comparison_columns,
  })

  // Options drawer with column management - config is passed once and returned for drawer
  const options = useTableOptions({
    filterGroup: 'views',
    table: tableRef.current,
    initialColumnOrder: columnOrder,
    defaultHiddenColumns: TOP_PAGES_DEFAULT_HIDDEN_COLUMNS,
    comparableColumns: TOP_PAGES_COMPARABLE_COLUMNS,
    comparisonColumns,
    defaultComparisonColumns: TOP_PAGES_DEFAULT_COMPARISON_COLUMNS,
    onColumnVisibilityChange: handleColumnVisibilityChange,
    onColumnOrderChange: handleColumnOrderChange,
    onComparisonColumnsChange: handleComparisonColumnsChange,
    onReset: handleColumnPreferencesReset,
  })

  // Final columns with comparison settings applied
  const columns = useMemo(
    () => createTopPagesColumns({ comparisonLabel, comparisonColumns }),
    [comparisonLabel, comparisonColumns]
  )

  // Transform API data to component interface
  const tableData = useMemo(() => {
    return extractRows(response).map(transformTopPageData)
  }, [response])

  const meta = extractMeta(response)
  const totalRows = meta?.totalRows ?? 0
  const totalPages = meta?.totalPages ?? 1

  const showSkeleton = isLoading && !response

  return (
    <div className="min-w-0">
      <ReportPageHeader
        title={__('Top Pages', 'wp-statistics')}
        filterGroup="views"
        optionsTriggerProps={options.triggerProps}
        customFilterFields={customFilterFields}
      />

      {/* Options Drawer with Column Management */}
      <TableOptionsDrawer {...options} />

      <div className="p-3">
        <NoticeContainer className="mb-2" currentRoute="top-pages" />

        {isError ? (
          <div className="p-2 text-center">
            <ErrorMessage message={__('Failed to load top pages', 'wp-statistics')} />
            <p className="text-sm text-muted-foreground">{error?.message}</p>
          </div>
        ) : showSkeleton ? (
          <PanelSkeleton titleWidth="w-24">
            <TableSkeleton rows={10} columns={6} />
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
            hiddenColumns={TOP_PAGES_DEFAULT_HIDDEN_COLUMNS}
            initialColumnVisibility={initialColumnVisibility}
            columnOrder={columnOrder.length > 0 ? columnOrder : undefined}
            onColumnVisibilityChange={handleColumnVisibilityChange}
            onColumnOrderChange={handleColumnOrderChange}
            onColumnPreferencesReset={handleColumnPreferencesReset}
            comparableColumns={TOP_PAGES_COMPARABLE_COLUMNS}
            comparisonColumns={comparisonColumns}
            defaultComparisonColumns={TOP_PAGES_DEFAULT_COMPARISON_COLUMNS}
            onComparisonColumnsChange={handleComparisonColumnsChange}
            emptyStateMessage={__('No pages found for the selected period', 'wp-statistics')}
            stickyHeader={true}
            borderless
            tableRef={tableRef}
          />
        )}
      </div>
    </div>
  )
}
