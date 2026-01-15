import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'
import type { ColumnDef } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'
import { useCallback, useMemo, useState } from 'react'

import { DataTable } from '@/components/custom/data-table'
import { type DateRange, DateRangePicker } from '@/components/custom/date-range-picker'
import { ErrorMessage } from '@/components/custom/error-message'
import { FilterButton, type FilterField } from '@/components/custom/filter-button'
import { LineChart } from '@/components/custom/line-chart'
import { NoticeContainer } from '@/components/ui/notice-container'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { ChartSkeleton, PanelSkeleton, TableSkeleton } from '@/components/ui/skeletons'
import { useChartData } from '@/hooks/use-chart-data'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { usePercentageCalc } from '@/hooks/use-percentage-calc'
import { useUrlSortSync } from '@/hooks/use-url-sort-sync'
import { formatCompactNumber, formatDecimal, formatDuration } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'
import {
  getSearchEnginesQueryOptions,
  type SearchEngineRow,
  type SearchType,
} from '@/services/referral/get-search-engines'

const PER_PAGE = 20

export const Route = createLazyFileRoute('/(referrals)/search-engines')({
  component: RouteComponent,
})

function RouteComponent() {
  const {
    dateFrom,
    dateTo,
    compareDateFrom,
    compareDateTo,
    period,
    filters: appliedFilters,
    page,
    setPage,
    setDateRange,
    applyFilters: handleApplyFilters,
    removeFilter: handleRemoveFilter,
    isInitialized,
    apiDateParams,
    isCompareEnabled,
  } = useGlobalFilters()

  const [timeframe, setTimeframe] = useState<'daily' | 'weekly' | 'monthly'>('daily')
  const [searchType, setSearchType] = useState<SearchType>('all')

  const { sorting, handleSortingChange, orderBy, order } = useUrlSortSync({
    defaultSort: [{ id: 'visitors', desc: true }],
    onPageReset: () => setPage(1),
  })

  const wp = WordPress.getInstance()
  const calcPercentage = usePercentageCalc()

  // Get filter fields for 'referrals' group
  const filterFields = useMemo<FilterField[]>(() => {
    return wp.getFilterFieldsByGroup('referrals') as FilterField[]
  }, [wp])

  // Handle date range updates
  const handleDateRangeUpdate = useCallback(
    (values: { range: DateRange; rangeCompare?: DateRange; period?: string }) => {
      setDateRange(values.range, values.rangeCompare, values.period)
    },
    [setDateRange]
  )

  // Handle search type change
  const handleSearchTypeChange = useCallback(
    (value: string) => {
      setSearchType(value as SearchType)
      setPage(1)
    },
    [setPage]
  )

  // Fetch data
  const {
    data: response,
    isLoading,
    isFetching,
    isError,
    error,
  } = useQuery({
    ...getSearchEnginesQueryOptions({
      dateFrom: apiDateParams.date_from,
      dateTo: apiDateParams.date_to,
      compareDateFrom: apiDateParams.previous_date_from,
      compareDateTo: apiDateParams.previous_date_to,
      timeframe,
      searchType,
      page,
      perPage: PER_PAGE,
      orderBy,
      order: order.toUpperCase() as 'ASC' | 'DESC',
      filters: appliedFilters || [],
    }),
    placeholderData: keepPreviousData,
    enabled: isInitialized,
  })

  // Define columns
  const columns = useMemo<ColumnDef<SearchEngineRow>[]>(
    () => [
      {
        accessorKey: 'referrer_domain',
        header: __('Search Engine', 'wp-statistics'),
        enableSorting: true,
        cell: ({ row }) => {
          const domain = row.original.referrer_domain
          const name = row.original.referrer_name || domain
          const channel = row.original.referrer_channel

          return (
            <div className="flex items-center gap-2 min-w-0">
              <img
                src={`https://www.google.com/s2/favicons?domain=${domain}&sz=32`}
                alt=""
                className="w-4 h-4 shrink-0"
                loading="lazy"
              />
              <div className="min-w-0">
                <div className="text-xs font-medium text-neutral-700 truncate">{name}</div>
                <div className="text-xs text-muted-foreground truncate capitalize">{channel?.replace('-', ' ')}</div>
              </div>
            </div>
          )
        },
      },
      {
        accessorKey: 'visitors',
        header: __('Visitors', 'wp-statistics'),
        enableSorting: true,
        meta: { align: 'right' },
        cell: ({ row }) => {
          const current = Number(row.original.visitors)
          const previous = row.original.previous?.visitors ? Number(row.original.previous.visitors) : undefined

          const { percentage, isNegative } =
            previous !== undefined ? calcPercentage(current, previous) : { percentage: '', isNegative: false }

          return (
            <div className="text-right">
              <span className="text-xs font-medium text-neutral-700 tabular-nums">{formatCompactNumber(current)}</span>
              {isCompareEnabled && previous !== undefined && (
                <span className={`ml-2 text-xs ${isNegative ? 'text-red-500' : 'text-green-500'}`}>
                  {isNegative ? '↓' : '↑'}
                  {percentage}%
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
          const previous = row.original.previous?.views ? Number(row.original.previous.views) : undefined

          const { percentage, isNegative } =
            previous !== undefined ? calcPercentage(current, previous) : { percentage: '', isNegative: false }

          return (
            <div className="text-right">
              <span className="text-xs font-medium text-neutral-700 tabular-nums">{formatCompactNumber(current)}</span>
              {isCompareEnabled && previous !== undefined && (
                <span className={`ml-2 text-xs ${isNegative ? 'text-red-500' : 'text-green-500'}`}>
                  {isNegative ? '↓' : '↑'}
                  {percentage}%
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
              <span className="text-xs font-medium text-neutral-700 tabular-nums">{formatDuration(current)}</span>
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
              <span className="text-xs font-medium text-neutral-700 tabular-nums">{formatDecimal(current)}%</span>
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
              <span className="text-xs font-medium text-neutral-700 tabular-nums">{formatDecimal(current)}</span>
            </div>
          )
        },
      },
    ],
    [calcPercentage, isCompareEnabled]
  )

  // Transform chart data using shared hook
  const { data: chartData, metrics: chartMetrics } = useChartData(response?.data?.items?.chart, {
    metrics: [{ key: 'visitors', label: __('Visitors', 'wp-statistics'), color: 'var(--chart-1)' }],
    showPreviousValues: isCompareEnabled,
    preserveNull: true,
  })

  // Extract table data
  const tableData = useMemo(() => {
    if (!response?.data?.items?.table?.data?.rows) return []
    return response.data.items.table.data.rows
  }, [response])

  // Pagination info
  const totalRows = response?.data?.items?.table?.meta?.total_rows ?? 0
  const totalPages = response?.data?.items?.table?.meta?.total_pages || Math.ceil(totalRows / PER_PAGE) || 1

  const handlePageChange = useCallback(
    (newPage: number) => {
      setPage(newPage)
    },
    [setPage]
  )

  const handleTimeframeChange = useCallback((newTimeframe: 'daily' | 'weekly' | 'monthly') => {
    setTimeframe(newTimeframe)
  }, [])

  // Loading states
  const showSkeleton = isLoading && !response

  return (
    <div className="min-w-0">
      {/* Header row */}
      <div className="flex items-center justify-between px-4 py-3 ">
        <h1 className="text-2xl font-semibold text-neutral-800">{__('Search Engines', 'wp-statistics')}</h1>
        <div className="flex items-center gap-2">
          <Select value={searchType} onValueChange={handleSearchTypeChange}>
            <SelectTrigger className="h-8 w-auto text-xs gap-1 px-3 border-neutral-200 hover:bg-neutral-50">
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">{__('All Search', 'wp-statistics')}</SelectItem>
              <SelectItem value="organic">{__('Organic Search', 'wp-statistics')}</SelectItem>
              <SelectItem value="paid">{__('Paid Search', 'wp-statistics')}</SelectItem>
            </SelectContent>
          </Select>
          {filterFields.length > 0 && isInitialized && (
            <FilterButton
              fields={filterFields}
              appliedFilters={appliedFilters || []}
              onApplyFilters={handleApplyFilters}
              filterGroup="referrals"
            />
          )}
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
        </div>
      </div>

      <div className="p-2 space-y-4">
        <NoticeContainer currentRoute="search-engines" />
        {/* Applied filters row */}

        {isError ? (
          <div className="p-2 text-center">
            <ErrorMessage message={__('Failed to load search engines', 'wp-statistics')} />
            <p className="text-sm text-muted-foreground">{error?.message}</p>
          </div>
        ) : showSkeleton ? (
          <div className="space-y-4">
            <PanelSkeleton titleWidth="w-40">
              <ChartSkeleton height={256} showTitle={false} />
            </PanelSkeleton>
            <PanelSkeleton titleWidth="w-32">
              <TableSkeleton rows={10} columns={6} />
            </PanelSkeleton>
          </div>
        ) : (
          <>
            {/* Chart */}
            <LineChart
              title={__('Top Search Engines Trend', 'wp-statistics')}
              data={chartData}
              metrics={chartMetrics}
              showPreviousPeriod={isCompareEnabled}
              timeframe={timeframe}
              onTimeframeChange={handleTimeframeChange}
              loading={isFetching && chartData.length === 0}
              compareDateTo={apiDateParams.previous_date_to}
              dateTo={apiDateParams.date_to}
            />

            {/* Table */}
            <DataTable
              columns={columns}
              data={tableData}
              title={__('Search Engines', 'wp-statistics')}
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
              emptyStateMessage={__('No search engines found for the selected period', 'wp-statistics')}
              stickyHeader={true}
            />
          </>
        )}
      </div>
    </div>
  )
}
