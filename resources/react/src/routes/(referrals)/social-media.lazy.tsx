import type { Table } from '@tanstack/react-table'
import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { useMemo, useRef } from 'react'

import { DataTable } from '@/components/custom/data-table'
import { DateRangePicker } from '@/components/custom/date-range-picker'
import { ErrorMessage } from '@/components/custom/error-message'
import { LineChart } from '@/components/custom/line-chart'
import { OptionsDrawerTrigger, TableOptionsDrawer, useTableOptions } from '@/components/custom/options-drawer'
import { SocialTypeSelect } from '@/components/custom/social-type-select'
import {
  createReferrersColumns,
  transformReferrerData,
  type Referrer,
  REFERRERS_COLUMN_CONFIG,
  REFERRERS_COMPARABLE_COLUMNS,
  REFERRERS_DEFAULT_API_COLUMNS,
  REFERRERS_DEFAULT_COMPARISON_COLUMNS,
  REFERRERS_DEFAULT_HIDDEN_COLUMNS,
} from '@/components/data-table-columns/referrers-columns'
import { NoticeContainer } from '@/components/ui/notice-container'
import { PanelSkeleton, ChartSkeleton, TableSkeleton } from '@/components/ui/skeletons'
import { useComparisonDateLabel } from '@/hooks/use-comparison-date-label'
import { useDataTablePreferences } from '@/hooks/use-data-table-preferences'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { useSocialTypeFilter } from '@/hooks/use-social-type-filter'
import { useUrlSortSync } from '@/hooks/use-url-sort-sync'
import { formatChartValue, transformChartResponse } from '@/lib/chart-utils'
import type { LineChartDataPoint, LineChartMetric } from '@/types/chart'
import {
  getSocialMediaOverviewQueryOptions,
  type SocialMediaChartResponse,
  type SocialMediaTableResponse,
} from '@/services/referrals/get-social-media-overview'

const PER_PAGE = 25
const SOCIAL_MEDIA_CONTEXT = 'social-media'

export const Route = createLazyFileRoute('/(referrals)/social-media')({
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

  // Social type filter (All/Organic/Paid) - shown in header AND Options drawer
  const {
    value: socialType,
    onChange: onSocialTypeChange,
    options: socialTypeOptions,
    getApiFilter,
    pageFilterConfig: socialTypeFilterConfig,
  } = useSocialTypeFilter()

  const { sorting, handleSortingChange, orderBy, order } = useUrlSortSync({
    defaultSort: [{ id: 'visitors', desc: true }],
    onPageReset: () => setPage(1),
  })

  // Page filters config for Options drawer
  const pageFilters = useMemo(
    () => [socialTypeFilterConfig],
    [socialTypeFilterConfig]
  )

  // Table ref for Options drawer column management
  const tableRef = useRef<Table<Referrer> | null>(null)

  // Get comparison date label for tooltip display
  const { label: comparisonLabel } = useComparisonDateLabel()

  // Base columns for preferences hook
  const baseColumns = useMemo(() => createReferrersColumns({ comparisonLabel }), [comparisonLabel])

  // Get the social type API filter
  const socialTypeApiFilter = useMemo(() => getApiFilter(), [getApiFilter])

  // Fetch data from batch API
  const {
    data: response,
    isLoading,
    isFetching,
    isError,
    error,
  } = useQuery({
    ...getSocialMediaOverviewQueryOptions({
      dateFrom: apiDateParams.date_from,
      dateTo: apiDateParams.date_to,
      compareDateFrom: apiDateParams.previous_date_from,
      compareDateTo: apiDateParams.previous_date_to,
      filters: appliedFilters,
      apiFilters: socialTypeApiFilter,
      page,
      perPage: PER_PAGE,
      orderBy,
      order: order as 'asc' | 'desc',
      context: SOCIAL_MEDIA_CONTEXT,
    }),
    placeholderData: keepPreviousData,
    enabled: isInitialized,
  })

  // Extract chart and table data from batch response
  const chartResponse = response?.data?.items?.chart as SocialMediaChartResponse | undefined
  const tableResponse = response?.data?.items?.table as SocialMediaTableResponse | undefined

  // Build dynamic chart metrics from datasets (Facebook, Twitter, Instagram, Total)
  // Only Total gets previousValue when comparison is enabled
  const { chartData, chartMetrics } = useMemo(() => {
    if (!chartResponse?.datasets) {
      return { chartData: [] as LineChartDataPoint[], chartMetrics: [] as LineChartMetric[] }
    }

    // Define chart colors for dynamic social platforms + total
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
    columnOrder,
    initialColumnVisibility,
    comparisonColumns,
    handleColumnVisibilityChange,
    handleColumnOrderChange,
    handleComparisonColumnsChange,
    handleColumnPreferencesReset,
  } = useDataTablePreferences({
    context: SOCIAL_MEDIA_CONTEXT,
    columns: baseColumns,
    defaultHiddenColumns: REFERRERS_DEFAULT_HIDDEN_COLUMNS,
    defaultApiColumns: REFERRERS_DEFAULT_API_COLUMNS,
    columnConfig: REFERRERS_COLUMN_CONFIG,
    sorting,
    defaultSortColumn: 'visitors',
    preferencesFromApi: tableResponse?.meta?.preferences?.columns,
    hasApiResponse: !!tableResponse,
    defaultComparisonColumns: REFERRERS_DEFAULT_COMPARISON_COLUMNS,
    comparisonColumnsFromApi: tableResponse?.meta?.preferences?.comparison_columns,
  })

  // Options drawer with column management and page filters
  const options = useTableOptions({
    filterGroup: 'referrals',
    table: tableRef.current,
    pageFilters,
    defaultHiddenColumns: REFERRERS_DEFAULT_HIDDEN_COLUMNS,
    comparableColumns: REFERRERS_COMPARABLE_COLUMNS,
    comparisonColumns,
    defaultComparisonColumns: REFERRERS_DEFAULT_COMPARISON_COLUMNS,
    onColumnVisibilityChange: handleColumnVisibilityChange,
    onColumnOrderChange: handleColumnOrderChange,
    onComparisonColumnsChange: handleComparisonColumnsChange,
    onReset: handleColumnPreferencesReset,
  })

  // Final columns with comparison settings applied
  const columns = useMemo(
    () => createReferrersColumns({ comparisonLabel, comparisonColumns }),
    [comparisonLabel, comparisonColumns]
  )

  // Transform API data to component interface
  const tableData = useMemo(() => {
    if (!tableResponse?.data?.rows) return []
    return tableResponse.data.rows.map(transformReferrerData)
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
        <h1 className="text-2xl font-semibold text-neutral-800">{__('Social Media', 'wp-statistics')}</h1>
        <div className="flex items-center gap-3">
          <div className="hidden lg:flex">
            <SocialTypeSelect
              value={socialType}
              onValueChange={onSocialTypeChange}
              options={socialTypeOptions}
            />
          </div>
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
        <NoticeContainer className="mb-2" currentRoute="social-media" />

        {isError ? (
          <div className="p-2 text-center">
            <ErrorMessage message={__('Failed to load social media data', 'wp-statistics')} />
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
            {/* Social Media Chart - shows top 3 platforms + total */}
            <LineChart
              title={__('Social Media', 'wp-statistics')}
              data={chartData}
              metrics={chartMetrics}
              showPreviousPeriod={isCompareEnabled}
              compareDateTo={apiDateParams.previous_date_to}
              dateTo={apiDateParams.date_to}
              borderless
            />

            {/* Referrers Table */}
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
              hiddenColumns={REFERRERS_DEFAULT_HIDDEN_COLUMNS}
              initialColumnVisibility={initialColumnVisibility}
              columnOrder={columnOrder.length > 0 ? columnOrder : undefined}
              onColumnVisibilityChange={handleColumnVisibilityChange}
              onColumnOrderChange={handleColumnOrderChange}
              onColumnPreferencesReset={handleColumnPreferencesReset}
              comparableColumns={REFERRERS_COMPARABLE_COLUMNS}
              comparisonColumns={comparisonColumns}
              defaultComparisonColumns={REFERRERS_DEFAULT_COMPARISON_COLUMNS}
              onComparisonColumnsChange={handleComparisonColumnsChange}
              emptyStateMessage={__('No social media referrers found for the selected period', 'wp-statistics')}
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
