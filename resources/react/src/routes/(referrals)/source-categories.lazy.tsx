import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'
import type { ColumnDef, SortingState } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'
import { useCallback, useMemo, useState } from 'react'

import { DataTable } from '@/components/custom/data-table'
import { type DateRange, DateRangePicker } from '@/components/custom/date-range-picker'
import { ErrorMessage } from '@/components/custom/error-message'
import { FilterBar } from '@/components/custom/filter-bar'
import { FilterButton, type FilterField } from '@/components/custom/filter-button'
import { LineChart } from '@/components/custom/line-chart'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { usePercentageCalc } from '@/hooks/use-percentage-calc'
import { formatCompactNumber, formatDecimal, formatDuration } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'
import {
  getSourceCategoriesQueryOptions,
  type SourceCategoryRow,
} from '@/services/referral/get-source-categories'

const PER_PAGE = 20

export const Route = createLazyFileRoute('/(referrals)/source-categories')({
  component: RouteComponent,
})

function RouteComponent() {
  const {
    dateFrom,
    dateTo,
    compareDateFrom,
    compareDateTo,
    filters: appliedFilters,
    page,
    setPage,
    setDateRange,
    applyFilters: handleApplyFilters,
    removeFilter: handleRemoveFilter,
    isInitialized,
    apiDateParams,
  } = useGlobalFilters()

  const [timeframe, setTimeframe] = useState<'daily' | 'weekly' | 'monthly'>('daily')
  const [sorting, setSorting] = useState<SortingState>([{ id: 'visitors', desc: true }])

  const wp = WordPress.getInstance()
  const calcPercentage = usePercentageCalc()

  // Get filter fields for 'referrals' group
  const filterFields = useMemo<FilterField[]>(() => {
    return wp.getFilterFieldsByGroup('referrals') as FilterField[]
  }, [wp])

  // Handle date range updates
  const handleDateRangeUpdate = useCallback(
    (values: { range: DateRange; rangeCompare?: DateRange }) => {
      setDateRange(values.range, values.rangeCompare)
    },
    [setDateRange]
  )

  // Determine sort parameters
  const orderBy = sorting.length > 0 ? sorting[0].id : 'visitors'
  const order = sorting.length > 0 && sorting[0].desc ? 'DESC' : 'ASC'

  // Fetch data
  const {
    data: response,
    isFetching,
    isError,
    error,
  } = useQuery({
    ...getSourceCategoriesQueryOptions({
      dateFrom: apiDateParams.date_from,
      dateTo: apiDateParams.date_to,
      compareDateFrom: apiDateParams.previous_date_from,
      compareDateTo: apiDateParams.previous_date_to,
      timeframe,
      page,
      perPage: PER_PAGE,
      orderBy,
      order: order as 'ASC' | 'DESC',
      filters: appliedFilters || [],
    }),
    placeholderData: keepPreviousData,
    enabled: isInitialized,
  })

  // Define columns
  const columns = useMemo<ColumnDef<SourceCategoryRow>[]>(
    () => [
      {
        accessorKey: 'referrer_channel',
        header: __('Channel', 'wp-statistics'),
        enableSorting: true,
        cell: ({ row }) => {
          const channel = row.original.referrer_channel || __('Direct', 'wp-statistics')
          return <span className="font-medium capitalize">{channel}</span>
        },
      },
      {
        accessorKey: 'visitors',
        header: __('Visitors', 'wp-statistics'),
        enableSorting: true,
        meta: { align: 'right' },
        cell: ({ row }) => {
          const current = Number(row.original.visitors)
          const previous = row.original.previous?.visitors
            ? Number(row.original.previous.visitors)
            : undefined

          const { percentage, isNegative } = previous !== undefined ? calcPercentage(current, previous) : { percentage: '', isNegative: false }

          return (
            <div className="text-right">
              <span className="font-medium">{formatCompactNumber(current)}</span>
              {previous !== undefined && (
                <span className={`ml-2 text-xs ${isNegative ? 'text-red-500' : 'text-green-500'}`}>
                  {isNegative ? '↓' : '↑'}{percentage}%
                </span>
              )}
            </div>
          )
        },
      },
      {
        accessorKey: 'views',
        header: __('Views', 'wp-statistics'),
        enableSorting: true,
        meta: { align: 'right' },
        cell: ({ row }) => {
          const current = Number(row.original.views)
          const previous = row.original.previous?.views
            ? Number(row.original.previous.views)
            : undefined

          const { percentage, isNegative } = previous !== undefined ? calcPercentage(current, previous) : { percentage: '', isNegative: false }

          return (
            <div className="text-right">
              <span className="font-medium">{formatCompactNumber(current)}</span>
              {previous !== undefined && (
                <span className={`ml-2 text-xs ${isNegative ? 'text-red-500' : 'text-green-500'}`}>
                  {isNegative ? '↓' : '↑'}{percentage}%
                </span>
              )}
            </div>
          )
        },
      },
      {
        accessorKey: 'avg_session_duration',
        header: __('Avg. Duration', 'wp-statistics'),
        enableSorting: true,
        meta: { align: 'right' },
        cell: ({ row }) => {
          const current = Number(row.original.avg_session_duration)
          return (
            <div className="text-right">
              <span className="font-medium">{formatDuration(current)}</span>
            </div>
          )
        },
      },
      {
        accessorKey: 'bounce_rate',
        header: __('Bounce Rate', 'wp-statistics'),
        enableSorting: true,
        meta: { align: 'right' },
        cell: ({ row }) => {
          const current = Number(row.original.bounce_rate)
          return (
            <div className="text-right">
              <span className="font-medium">{formatDecimal(current)}%</span>
            </div>
          )
        },
      },
      {
        accessorKey: 'pages_per_session',
        header: __('Pages/Session', 'wp-statistics'),
        enableSorting: true,
        meta: { align: 'right' },
        cell: ({ row }) => {
          const current = Number(row.original.pages_per_session)
          return (
            <div className="text-right">
              <span className="font-medium">{formatDecimal(current)}</span>
            </div>
          )
        },
      },
    ],
    [calcPercentage]
  )

  // Transform chart data from API format to LineChart format
  const chartData = useMemo(() => {
    const chart = response?.data?.items?.chart
    if (!chart?.labels || !chart?.datasets) return []

    const labels = chart.labels
    const datasets = chart.datasets

    // Separate current and previous datasets
    const currentDatasets = datasets.filter((d) => !d.comparison)
    const previousDatasets = datasets.filter((d) => d.comparison)

    return labels.map((label, index) => {
      const point: Record<string, string | number> = { date: label }

      // Add current period data
      currentDatasets.forEach((dataset) => {
        point[dataset.key] = Number(dataset.data[index]) || 0
      })

      // Add previous period data
      previousDatasets.forEach((dataset) => {
        const baseKey = dataset.key.replace('_previous', '')
        point[`${baseKey}Previous`] = Number(dataset.data[index]) || 0
      })

      return point
    })
  }, [response])

  // Calculate totals for chart metrics
  const chartTotals = useMemo(() => {
    const chart = response?.data?.items?.chart
    if (!chart?.datasets) {
      return { visitors: 0, visitorsPrevious: 0 }
    }

    const datasets = chart.datasets
    const visitorsDataset = datasets.find((d) => d.key === 'visitors' && !d.comparison)
    const visitorsPrevDataset = datasets.find((d) => d.key === 'visitors_previous' && d.comparison)

    return {
      visitors: visitorsDataset?.data?.reduce((sum, v) => sum + Number(v), 0) || 0,
      visitorsPrevious: visitorsPrevDataset?.data?.reduce((sum, v) => sum + Number(v), 0) || 0,
    }
  }, [response])

  // Define chart metrics
  const chartMetrics = useMemo(() => {
    return [
      {
        key: 'visitors',
        label: __('Visitors', 'wp-statistics'),
        color: 'var(--chart-1)',
        enabled: true,
        value: chartTotals.visitors >= 1000
          ? `${formatDecimal(chartTotals.visitors / 1000)}k`
          : formatDecimal(chartTotals.visitors),
        previousValue: chartTotals.visitorsPrevious >= 1000
          ? `${formatDecimal(chartTotals.visitorsPrevious / 1000)}k`
          : formatDecimal(chartTotals.visitorsPrevious),
      },
    ]
  }, [chartTotals])

  // Extract table data
  const tableData = useMemo(() => {
    if (!response?.data?.items?.table?.data?.rows) return []
    return response.data.items.table.data.rows
  }, [response])

  // Pagination info
  const totalRows = response?.data?.items?.table?.meta?.total_rows ?? 0
  const totalPages = response?.data?.items?.table?.meta?.total_pages || Math.ceil(totalRows / PER_PAGE) || 1

  const handleSortingChange = useCallback(
    (newSorting: SortingState) => {
      setSorting(newSorting)
      setPage(1)
    },
    [setPage]
  )

  const handlePageChange = useCallback(
    (newPage: number) => {
      setPage(newPage)
    },
    [setPage]
  )

  const handleTimeframeChange = useCallback((newTimeframe: 'daily' | 'weekly' | 'monthly') => {
    setTimeframe(newTimeframe)
  }, [])

  return (
    <div className="min-w-0">
      {/* Header row */}
      <div className="flex items-center justify-between px-4 py-3 bg-white border-b border-input">
        <h1 className="text-xl font-semibold text-neutral-800">{__('Source Categories', 'wp-statistics')}</h1>
        <div className="flex items-center gap-3">
          {filterFields.length > 0 && isInitialized && (
            <FilterButton
              fields={filterFields}
              appliedFilters={appliedFilters || []}
              onApplyFilters={handleApplyFilters}
            />
          )}
          <DateRangePicker
            initialDateFrom={dateFrom}
            initialDateTo={dateTo}
            initialCompareFrom={compareDateFrom}
            initialCompareTo={compareDateTo}
            showCompare={true}
            onUpdate={handleDateRangeUpdate}
            align="end"
          />
        </div>
      </div>

      <div className="p-2 space-y-4">
        {/* Applied filters row */}
        {appliedFilters && appliedFilters.length > 0 && (
          <FilterBar filters={appliedFilters} onRemoveFilter={handleRemoveFilter} />
        )}

        {isError ? (
          <div className="p-2 text-center">
            <ErrorMessage message={__('Failed to load source categories', 'wp-statistics')} />
            <p className="text-sm text-muted-foreground">{error?.message}</p>
          </div>
        ) : (
          <>
            {/* Chart */}
            <LineChart
              title={__('Traffic by Channel', 'wp-statistics')}
              data={chartData}
              metrics={chartMetrics}
              showPreviousPeriod={!!(compareDateFrom && compareDateTo)}
              timeframe={timeframe}
              onTimeframeChange={handleTimeframeChange}
              loading={isFetching && chartData.length === 0}
            />

            {/* Table */}
            <DataTable
              columns={columns}
              data={tableData}
              title={__('Source Categories', 'wp-statistics')}
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
              emptyStateMessage={__('No source categories found for the selected period', 'wp-statistics')}
              stickyHeader={true}
            />
          </>
        )}
      </div>
    </div>
  )
}
