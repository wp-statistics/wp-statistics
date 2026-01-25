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
  createTopAuthorsColumns,
  type TopAuthor,
  TOP_AUTHORS_COLUMN_CONFIG,
  TOP_AUTHORS_COMPARABLE_COLUMNS,
  TOP_AUTHORS_CONTEXT,
  TOP_AUTHORS_DEFAULT_API_COLUMNS,
  TOP_AUTHORS_DEFAULT_COMPARISON_COLUMNS,
  TOP_AUTHORS_DEFAULT_HIDDEN_COLUMNS,
  transformTopAuthorData,
} from '@/components/data-table-columns/top-authors-columns'
import { NoticeContainer } from '@/components/ui/notice-container'
import { PanelSkeleton, TableSkeleton } from '@/components/ui/skeletons'
import { useComparisonDateLabel } from '@/hooks/use-comparison-date-label'
import { useDataTablePreferences } from '@/hooks/use-data-table-preferences'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { usePremiumFeature } from '@/hooks/use-premium-feature'
import { useUrlSortSync } from '@/hooks/use-url-sort-sync'
import { getApiSortField } from '@/lib/column-utils'
import { extractMeta, extractRows } from '@/lib/response-helpers'
import { WordPress } from '@/lib/wordpress'
import { getTopAuthorsQueryOptions } from '@/services/content-analytics/get-top-authors'

const PER_PAGE = 20

export const Route = createLazyFileRoute('/(content-analytics)/top-authors')({
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

  // Map column ID to API field name for sorting
  const apiOrderBy = getApiSortField(orderBy, TOP_AUTHORS_COLUMN_CONFIG)

  // Table ref for Options drawer column management
  const tableRef = useRef<Table<TopAuthor> | null>(null)

  // Get comparison date label for tooltip display
  const { label: comparisonLabel } = useComparisonDateLabel()

  // Check premium feature for post_type filter
  const { isEnabled: hasCustomPostTypeSupport } = usePremiumFeature('custom-post-type-support')

  const wp = WordPress.getInstance()

  // Base columns for preferences hook (stable definition for column IDs)
  const baseColumns = useMemo(() => createTopAuthorsColumns({ comparisonLabel }), [comparisonLabel])

  // Filters for Top Authors page (post_type only with premium)
  // Note: author filter is not included since this page lists all authors
  const customFilterFields = useMemo<FilterField[]>(() => {
    if (!hasCustomPostTypeSupport) {
      return []
    }
    return wp.getFilterFieldsByGroup('content').filter((field) => field.name === 'post_type') as FilterField[]
  }, [wp, hasCustomPostTypeSupport])

  // Fetch data from API
  const {
    data: response,
    isLoading,
    isFetching,
    isError,
    error,
  } = useQuery({
    ...getTopAuthorsQueryOptions({
      page,
      per_page: PER_PAGE,
      order_by: apiOrderBy,
      order: order as 'asc' | 'desc',
      date_from: apiDateParams.date_from,
      date_to: apiDateParams.date_to,
      previous_date_from: apiDateParams.previous_date_from,
      previous_date_to: apiDateParams.previous_date_to,
      filters: appliedFilters || [],
      context: TOP_AUTHORS_CONTEXT,
      columns: TOP_AUTHORS_DEFAULT_API_COLUMNS,
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
    context: TOP_AUTHORS_CONTEXT,
    columns: baseColumns,
    defaultHiddenColumns: TOP_AUTHORS_DEFAULT_HIDDEN_COLUMNS,
    defaultApiColumns: TOP_AUTHORS_DEFAULT_API_COLUMNS,
    columnConfig: TOP_AUTHORS_COLUMN_CONFIG,
    sorting,
    defaultSortColumn: 'views',
    preferencesFromApi: response?.data?.meta?.preferences?.columns,
    hasApiResponse: !!response?.data,
    defaultComparisonColumns: TOP_AUTHORS_DEFAULT_COMPARISON_COLUMNS,
    comparisonColumnsFromApi: (response?.data?.meta?.preferences as { comparison_columns?: string[] } | undefined)?.comparison_columns,
  })

  // Options drawer with column management - config is passed once and returned for drawer
  const options = useTableOptions({
    filterGroup: 'content',
    table: tableRef.current,
    initialColumnOrder: columnOrder,
    defaultHiddenColumns: TOP_AUTHORS_DEFAULT_HIDDEN_COLUMNS,
    comparableColumns: TOP_AUTHORS_COMPARABLE_COLUMNS,
    comparisonColumns,
    defaultComparisonColumns: TOP_AUTHORS_DEFAULT_COMPARISON_COLUMNS,
    onColumnVisibilityChange: handleColumnVisibilityChange,
    onColumnOrderChange: handleColumnOrderChange,
    onComparisonColumnsChange: handleComparisonColumnsChange,
    onReset: handleColumnPreferencesReset,
  })

  // Final columns with comparison settings applied
  const columns = useMemo(
    () => createTopAuthorsColumns({ comparisonLabel, comparisonColumns }),
    [comparisonLabel, comparisonColumns]
  )

  // Transform API data to component interface
  const tableData = useMemo(() => {
    return extractRows(response).map(transformTopAuthorData)
  }, [response])

  const meta = extractMeta(response)
  const totalRows = meta?.totalRows ?? 0
  const totalPages = meta?.totalPages ?? 1

  const showSkeleton = isLoading && !response

  return (
    <div className="min-w-0">
      <ReportPageHeader
        title={__('Top Authors', 'wp-statistics')}
        filterGroup="content"
        optionsTriggerProps={options.triggerProps}
        customFilterFields={customFilterFields}
      />

      {/* Options Drawer with Column Management */}
      <TableOptionsDrawer {...options} />

      <div className="p-3">
        <NoticeContainer className="mb-2" currentRoute="top-authors" />

        {isError ? (
          <div className="p-2 text-center">
            <ErrorMessage message={__('Failed to load top authors', 'wp-statistics')} />
            <p className="text-sm text-muted-foreground">{error?.message}</p>
          </div>
        ) : showSkeleton ? (
          <PanelSkeleton titleWidth="w-24">
            <TableSkeleton rows={10} columns={7} />
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
            hiddenColumns={TOP_AUTHORS_DEFAULT_HIDDEN_COLUMNS}
            initialColumnVisibility={initialColumnVisibility}
            columnOrder={columnOrder.length > 0 ? columnOrder : undefined}
            onColumnVisibilityChange={handleColumnVisibilityChange}
            onColumnOrderChange={handleColumnOrderChange}
            onColumnPreferencesReset={handleColumnPreferencesReset}
            comparableColumns={TOP_AUTHORS_COMPARABLE_COLUMNS}
            comparisonColumns={comparisonColumns}
            defaultComparisonColumns={TOP_AUTHORS_DEFAULT_COMPARISON_COLUMNS}
            onComparisonColumnsChange={handleComparisonColumnsChange}
            emptyStateMessage={__('No authors found for the selected period', 'wp-statistics')}
            stickyHeader={true}
            borderless
            tableRef={tableRef}
          />
        )}
      </div>
    </div>
  )
}
