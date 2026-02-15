import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'
import type { Table } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'
import { useMemo, useRef } from 'react'

import { DataTable } from '@/components/custom/data-table'
import { DateRangePicker } from '@/components/custom/date-range-picker'
import { ErrorMessage } from '@/components/custom/error-message'
import { LineChart } from '@/components/custom/line-chart'
import { OptionsDrawerTrigger, TableOptionsDrawer, useTableOptions } from '@/components/custom/options-drawer'
import {
  createSourceCategoriesColumns,
  SOURCE_CATEGORIES_COLUMN_CONFIG,
  SOURCE_CATEGORIES_COMPARABLE_COLUMNS,
  SOURCE_CATEGORIES_CONTEXT,
  SOURCE_CATEGORIES_DEFAULT_API_COLUMNS,
  SOURCE_CATEGORIES_DEFAULT_COMPARISON_COLUMNS,
  SOURCE_CATEGORIES_DEFAULT_HIDDEN_COLUMNS,
  type SourceCategory,
  transformSourceCategoryData,
} from '@/components/data-table-columns/source-categories-columns'
import { NoticeContainer } from '@/components/ui/notice-container'
import { ChartSkeleton, PanelSkeleton, TableSkeleton } from '@/components/ui/skeletons'
import { useComparisonDateLabel } from '@/hooks/use-comparison-date-label'
import { useDataTablePreferences } from '@/hooks/use-data-table-preferences'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { useUrlSortSync } from '@/hooks/use-url-sort-sync'
import { formatChartValue, transformChartResponse } from '@/lib/chart-utils'
import {
  getSourceCategoriesOverviewQueryOptions,
  type SourceCategoriesChartResponse,
  type SourceCategoriesTableResponse,
} from '@/services/referrals/get-source-categories-overview'
import type { LineChartDataPoint, LineChartMetric } from '@/types/chart'

const PER_PAGE = 25

export const Route = createLazyFileRoute('/(referrals)/source-categories')({
  component: RouteComponent,
})

function RouteComponent() {
  const {
    filters: appliedFilters,
    page,
    setPage,
    handlePageChange,
    isInitialized,
    isCompareEnabled,
    apiDateParams,
    dateFrom,
    dateTo,
    compareDateFrom,
    compareDateTo,
    period,
    handleDateRangeUpdate,
  } = useGlobalFilters()

  const { sorting, handleSortingChange, orderBy, order } = useUrlSortSync({
    defaultSort: [{ id: 'visitors', desc: true }],
    onPageReset: () => setPage(1),
  })

  // Table ref for Options drawer column management
  const tableRef = useRef<Table<SourceCategory> | null>(null)

  // Get comparison date label for tooltip display
  const { label: comparisonLabel } = useComparisonDateLabel()

  // Base columns for preferences hook
  const baseColumns = useMemo(() => createSourceCategoriesColumns({ comparisonLabel }), [comparisonLabel])

  // Fetch data from batch API
  const {
    data: response,
    isLoading,
    isFetching,
    isError,
    error,
  } = useQuery({
    ...getSourceCategoriesOverviewQueryOptions({
      dateFrom: apiDateParams.date_from,
      dateTo: apiDateParams.date_to,
      compareDateFrom: apiDateParams.previous_date_from,
      compareDateTo: apiDateParams.previous_date_to,
      filters: appliedFilters,
      page,
      perPage: PER_PAGE,
      orderBy,
      order: order as 'asc' | 'desc',
      context: SOURCE_CATEGORIES_CONTEXT,
    }),
    placeholderData: keepPreviousData,
    enabled: isInitialized,
  })

  // Extract chart and table data from batch response
  const chartResponse = response?.data?.items?.chart as SourceCategoriesChartResponse | undefined
  const tableResponse = response?.data?.items?.table as SourceCategoriesTableResponse | undefined

  // Build dynamic chart metrics from datasets (top 3 categories + Total)
  // Only Total gets previousValue when comparison is enabled
  const { chartData, chartMetrics } = useMemo(() => {
    if (!chartResponse?.datasets) {
      return { chartData: [] as LineChartDataPoint[], chartMetrics: [] as LineChartMetric[] }
    }

    // Define chart colors for dynamic source categories + total
    const chartColors = [
      'var(--chart-1)',
      'var(--chart-2)',
      'var(--chart-3)',
      'var(--chart-4)',
    ]

    // Get current period datasets (non-comparison)
    const currentDatasets = chartResponse.datasets.filter((ds) => !ds.comparison)
    // Get previous period datasets (comparison) - only for Total
    const previousDatasets = chartResponse.datasets.filter((ds) => ds.comparison)

    // Build metrics from current datasets
    const metrics: LineChartMetric[] = currentDatasets.map((ds, index) => {
      // Calculate total from data array
      const total = ds.data.reduce((sum, v) => sum + (v !== null ? Number(v) : 0), 0)

      const metric: LineChartMetric = {
        key: ds.key,
        label: ds.label,
        color: chartColors[index % chartColors.length],
        enabled: true,
        value: formatChartValue(total),
      }

      // Only add previousValue for 'total' metric when comparison is enabled
      if (ds.key === 'total' && isCompareEnabled) {
        const prevDataset = previousDatasets.find((p) => p.key === 'total_previous')
        if (prevDataset?.data) {
          const prevTotal = prevDataset.data.reduce((sum, v) => sum + (v !== null ? Number(v) : 0), 0)
          metric.previousValue = formatChartValue(prevTotal)
        }
      }

      return metric
    })

    // Transform to LineChart data format
    const data = transformChartResponse(chartResponse, { preserveNull: true })

    return { chartData: data, chartMetrics: metrics }
  }, [chartResponse, isCompareEnabled])

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
    context: SOURCE_CATEGORIES_CONTEXT,
    columns: baseColumns,
    defaultHiddenColumns: SOURCE_CATEGORIES_DEFAULT_HIDDEN_COLUMNS,
    defaultApiColumns: SOURCE_CATEGORIES_DEFAULT_API_COLUMNS,
    columnConfig: SOURCE_CATEGORIES_COLUMN_CONFIG,
    sorting,
    defaultSortColumn: 'visitors',
    preferencesFromApi: tableResponse?.meta?.preferences?.columns,
    hasApiResponse: !!tableResponse,
    defaultComparisonColumns: SOURCE_CATEGORIES_DEFAULT_COMPARISON_COLUMNS,
    comparisonColumnsFromApi: tableResponse?.meta?.preferences?.comparison_columns,
  })

  // Options drawer with column management (no filters for source categories)
  const options = useTableOptions({
    filterGroup: 'referrals',
    table: tableRef.current,
    hideFilters: true,
    initialColumnOrder: defaultColumnOrder,
    columnOrder,
    defaultHiddenColumns: SOURCE_CATEGORIES_DEFAULT_HIDDEN_COLUMNS,
    initialColumnVisibility,
    comparableColumns: SOURCE_CATEGORIES_COMPARABLE_COLUMNS,
    comparisonColumns,
    defaultComparisonColumns: SOURCE_CATEGORIES_DEFAULT_COMPARISON_COLUMNS,
    onColumnVisibilityChange: handleColumnVisibilityChange,
    onColumnOrderChange: handleColumnOrderChange,
    onComparisonColumnsChange: handleComparisonColumnsChange,
    onReset: handleColumnPreferencesReset,
  })

  // Final columns with comparison settings applied
  const columns = useMemo(
    () => createSourceCategoriesColumns({ comparisonLabel, comparisonColumns }),
    [comparisonLabel, comparisonColumns]
  )

  // Transform API data to component interface
  const tableData = useMemo(() => {
    if (!tableResponse?.data?.rows) return []
    return tableResponse.data.rows.map(transformSourceCategoryData)
  }, [tableResponse])

  const tableMeta = tableResponse?.meta
  const totalRows = tableMeta?.total_rows ?? 0
  const totalPages = tableMeta?.total_pages ?? 1

  const showSkeleton = isLoading && !response
  const showFullPageLoading = isFetching && !isLoading

  return (
    <div className="min-w-0">
      {/* Header */}
      <div className="flex items-center justify-between px-4 py-3">
        <h1 className="text-2xl font-semibold text-neutral-800">{__('Source Categories', 'wp-statistics')}</h1>
        <div className="flex items-center gap-3">
          <DateRangePicker
            initialDateFrom={dateFrom}
            initialDateTo={dateTo}
            initialCompareFrom={compareDateFrom}
            initialCompareTo={compareDateTo}
            initialPeriod={period}
            showCompare={true}
            onUpdate={handleDateRangeUpdate}
            align="end"
          />
          <OptionsDrawerTrigger {...options.triggerProps} />
        </div>
      </div>

      {/* Options Drawer with Column Management */}
      <TableOptionsDrawer {...options} />

      <div className="p-3">
        <NoticeContainer className="mb-2" currentRoute="source-categories" />

        {isError ? (
          <div className="p-2 text-center">
            <ErrorMessage message={__('Failed to load source categories data', 'wp-statistics')} />
            <p className="text-sm text-muted-foreground">{error?.message}</p>
          </div>
        ) : showSkeleton || showFullPageLoading ? (
          <div className="space-y-3">
            {/* Chart skeleton */}
            <PanelSkeleton titleWidth="w-32">
              <ChartSkeleton height={256} showTitle={false} />
            </PanelSkeleton>
            {/* Table skeleton */}
            <PanelSkeleton titleWidth="w-24">
              <TableSkeleton rows={10} columns={4} />
            </PanelSkeleton>
          </div>
        ) : (
          <div className="space-y-3">
            {/* Source Categories Chart - shows top 3 categories + total */}
            <LineChart
              title={__('Source Categories', 'wp-statistics')}
              data={chartData}
              metrics={chartMetrics}
              showPreviousPeriod={isCompareEnabled}
              compareDateTo={apiDateParams.previous_date_to}
              dateTo={apiDateParams.date_to}
              borderless
            />

            {/* Source Categories Table */}
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
              hiddenColumns={SOURCE_CATEGORIES_DEFAULT_HIDDEN_COLUMNS}
              initialColumnVisibility={initialColumnVisibility}
              columnOrder={columnOrder.length > 0 ? columnOrder : undefined}
              onColumnVisibilityChange={handleColumnVisibilityChange}
              onColumnOrderChange={handleColumnOrderChange}
              onColumnPreferencesReset={handleColumnPreferencesReset}
              comparableColumns={SOURCE_CATEGORIES_COMPARABLE_COLUMNS}
              comparisonColumns={comparisonColumns}
              defaultComparisonColumns={SOURCE_CATEGORIES_DEFAULT_COMPARISON_COLUMNS}
              onComparisonColumnsChange={handleComparisonColumnsChange}
              emptyStateMessage={__('No source categories found for the selected period', 'wp-statistics')}
              stickyHeader={true}
              borderless
              tableRef={tableRef}
            />
          </div>
        )}
      </div>
    </div>
  )
}
