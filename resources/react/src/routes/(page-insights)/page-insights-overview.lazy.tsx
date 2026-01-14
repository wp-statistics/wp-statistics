import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute, useNavigate } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { useCallback, useEffect, useMemo, useRef, useState } from 'react'

import { type DateRange, DateRangePicker } from '@/components/custom/date-range-picker'
import { ErrorMessage } from '@/components/custom/error-message'
import { FilterButton, type FilterField } from '@/components/custom/filter-button'
import { HorizontalBarList } from '@/components/custom/horizontal-bar-list'
import { LineChart } from '@/components/custom/line-chart'
import { type MetricItem, Metrics } from '@/components/custom/metrics'
import { Panel } from '@/components/ui/panel'
import { NoticeContainer } from '@/components/ui/notice-container'
import { BarListSkeleton, ChartSkeleton, MetricsSkeleton, PanelSkeleton } from '@/components/ui/skeletons'
import { useComparisonDateLabel } from '@/hooks/use-comparison-date-label'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { usePercentageCalc } from '@/hooks/use-percentage-calc'
import { useChartData } from '@/hooks/use-chart-data'
import { calcSharePercentage, decodeText, formatCompactNumber, formatDecimal, formatDuration, getTotalValue } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'
import { getPageInsightsOverviewQueryOptions } from '@/services/page-insight/get-page-insights-overview'

export const Route = createLazyFileRoute('/(page-insights)/page-insights-overview')({
  component: RouteComponent,
  errorComponent: ({ error }) => (
    <div className="p-6 text-center">
      <h2 className="text-xl font-semibold text-destructive mb-2">Error Loading Page</h2>
      <p className="text-muted-foreground">{error.message}</p>
    </div>
  ),
})

function RouteComponent() {
  const navigate = useNavigate()

  // Use global filters context for date range and filters
  const {
    dateFrom,
    dateTo,
    compareDateFrom,
    compareDateTo,
    period,
    filters: appliedFilters,
    setDateRange,
    applyFilters: handleApplyFilters,
    isInitialized,
    apiDateParams,
    isCompareEnabled,
  } = useGlobalFilters()

  const wp = WordPress.getInstance()

  // Get filter fields for 'views' group from localized data
  const filterFields = useMemo<FilterField[]>(() => {
    return (wp.getFilterFieldsByGroup('views') || []) as FilterField[]
  }, [wp])

  const [timeframe, setTimeframe] = useState<'daily' | 'weekly' | 'monthly'>('daily')

  // Track if only timeframe changed (for loading behavior)
  const [isTimeframeOnlyChange, setIsTimeframeOnlyChange] = useState(false)
  const prevFiltersRef = useRef<string>(JSON.stringify(appliedFilters))
  const prevDateFromRef = useRef<Date | undefined>(dateFrom)
  const prevDateToRef = useRef<Date | undefined>(dateTo)

  // Detect what changed when data is being fetched
  useEffect(() => {
    const currentFilters = JSON.stringify(appliedFilters)

    const filtersChanged = currentFilters !== prevFiltersRef.current
    const dateRangeChanged = dateFrom !== prevDateFromRef.current || dateTo !== prevDateToRef.current

    // If filters or dates changed, it's NOT a timeframe-only change
    if (filtersChanged || dateRangeChanged) {
      setIsTimeframeOnlyChange(false)
    }

    // Update refs
    prevFiltersRef.current = currentFilters
    prevDateFromRef.current = dateFrom
    prevDateToRef.current = dateTo
  }, [appliedFilters, dateFrom, dateTo])

  // Custom timeframe setter that tracks the change type
  const handleTimeframeChange = useCallback((newTimeframe: 'daily' | 'weekly' | 'monthly') => {
    setIsTimeframeOnlyChange(true)
    setTimeframe(newTimeframe)
  }, [])

  // Handle date range updates from DateRangePicker
  const handleDateRangeUpdate = useCallback(
    (values: { range: DateRange; rangeCompare?: DateRange; period?: string }) => {
      setDateRange(values.range, values.rangeCompare, values.period)
    },
    [setDateRange]
  )

  // Batch query for all overview data
  const {
    data: batchResponse,
    isLoading,
    isFetching,
    isError,
    error,
  } = useQuery({
    ...getPageInsightsOverviewQueryOptions({
      dateFrom: apiDateParams.date_from,
      dateTo: apiDateParams.date_to,
      compareDateFrom: apiDateParams.previous_date_from,
      compareDateTo: apiDateParams.previous_date_to,
      timeframe,
      filters: appliedFilters || [],
    }),
    retry: false,
    placeholderData: keepPreviousData,
    enabled: isInitialized,
  })

  // Only show skeleton on initial load
  const showSkeleton = isLoading && !batchResponse
  // Show full page loading when filters/dates change
  const showFullPageLoading = isFetching && !isLoading && !isTimeframeOnlyChange
  // Show loading indicator on chart only when timeframe changes
  const isChartRefetching = isFetching && !isLoading && isTimeframeOnlyChange

  // Extract data from batch response
  const metricsResponse = batchResponse?.data?.items?.metrics
  const metricsTopPostType = batchResponse?.data?.items?.metrics_top_post_type
  const pageViewsTrendsResponse = batchResponse?.data?.items?.page_views_trends
  const topPagesData = batchResponse?.data?.items?.top_pages?.data?.rows || []
  const topPagesTotals = batchResponse?.data?.items?.top_pages?.data?.totals
  const entryPagesData = batchResponse?.data?.items?.entry_pages?.data?.rows || []
  const entryPagesTotals = batchResponse?.data?.items?.entry_pages?.data?.totals
  const pages404Data = batchResponse?.data?.items?.pages_404?.data?.rows || []

  const calcPercentage = usePercentageCalc()
  const { label: comparisonDateLabel } = useComparisonDateLabel()

  // Transform metrics to component format
  const metricsData = useMemo<MetricItem[]>(() => {
    const totals = metricsResponse?.totals
    if (!totals) return []

    const views = getTotalValue(totals.views)
    const prevViews = getTotalValue(totals.views?.previous)
    const visitors = getTotalValue(totals.visitors)
    const prevVisitors = getTotalValue(totals.visitors?.previous)
    const avgTimeOnPage = getTotalValue(totals.avg_time_on_page)
    const prevAvgTimeOnPage = getTotalValue(totals.avg_time_on_page?.previous)
    const bounceRate = getTotalValue(totals.bounce_rate)
    const prevBounceRate = getTotalValue(totals.bounce_rate?.previous)
    const pagesPerSession = getTotalValue(totals.pages_per_session)
    const prevPagesPerSession = getTotalValue(totals.pages_per_session?.previous)

    return [
      {
        label: __('Views', 'wp-statistics'),
        value: formatCompactNumber(views),
        ...(isCompareEnabled ? calcPercentage(views, prevViews) : {}),
      },
      {
        label: __('Visitors', 'wp-statistics'),
        value: formatCompactNumber(visitors),
        ...(isCompareEnabled ? calcPercentage(visitors, prevVisitors) : {}),
      },
      {
        label: __('Avg. Time on Page', 'wp-statistics'),
        value: formatDuration(avgTimeOnPage),
        ...(isCompareEnabled ? calcPercentage(avgTimeOnPage, prevAvgTimeOnPage) : {}),
      },
      {
        label: __('Bounce Rate', 'wp-statistics'),
        value: `${formatDecimal(bounceRate)}%`,
        ...(isCompareEnabled ? {
          ...calcPercentage(bounceRate, prevBounceRate),
          isNegative: !calcPercentage(bounceRate, prevBounceRate).isNegative, // Invert for bounce rate (lower is better)
        } : {}),
      },
      {
        label: __('Pages/Session', 'wp-statistics'),
        value: formatDecimal(pagesPerSession),
        ...(isCompareEnabled ? calcPercentage(pagesPerSession, prevPagesPerSession) : {}),
      },
      {
        label: __('Top Content Type', 'wp-statistics'),
        value: metricsTopPostType?.items?.[0]?.page_type || '-',
      },
    ]
  }, [metricsResponse, metricsTopPostType, calcPercentage, isCompareEnabled])

  // Transform chart data using shared hook
  const { data: pageViewsTrendsData, metrics: chartMetrics } = useChartData(pageViewsTrendsResponse, {
    metrics: [
      { key: 'views', label: __('Views', 'wp-statistics'), color: 'var(--chart-1)' },
      { key: 'visitors', label: __('Visitors', 'wp-statistics'), color: 'var(--chart-2)' },
    ],
    showPreviousValues: isCompareEnabled,
    preserveNull: true,
  })

  // Transform top pages data
  const topPagesListData = useMemo(() => {
    const totalViews =
      Number(topPagesTotals?.views) || topPagesData.reduce((sum, row) => sum + Number(row.views), 0) || 1

    return topPagesData.map((row) => {
      const currentValue = Number(row.views) || 0
      const previousValue = Number(row.previous?.views) || 0
      const comparisonProps = isCompareEnabled
        ? {
            ...calcPercentage(currentValue, previousValue),
            tooltipSubtitle: `${__('Previous:', 'wp-statistics')} ${previousValue.toLocaleString()}`,
            comparisonDateLabel,
          }
        : {}

      return {
        label: decodeText(row.page_title) || row.page_uri,
        value: formatCompactNumber(currentValue),
        fillPercentage: calcSharePercentage(currentValue, totalViews),
        ...comparisonProps,
      }
    })
  }, [topPagesData, topPagesTotals, calcPercentage, isCompareEnabled])

  // Transform entry pages data
  const entryPagesListData = useMemo(() => {
    const totalSessions =
      Number(entryPagesTotals?.sessions) || entryPagesData.reduce((sum, row) => sum + Number(row.sessions), 0) || 1

    return entryPagesData.map((row) => {
      const currentValue = Number(row.sessions) || 0
      const previousValue = Number(row.previous?.sessions) || 0
      const comparisonProps = isCompareEnabled
        ? {
            ...calcPercentage(currentValue, previousValue),
            tooltipSubtitle: `${__('Previous:', 'wp-statistics')} ${previousValue.toLocaleString()}`,
            comparisonDateLabel,
          }
        : {}

      return {
        label: decodeText(row.page_title) || row.page_uri,
        value: formatCompactNumber(currentValue),
        fillPercentage: calcSharePercentage(currentValue, totalSessions),
        ...comparisonProps,
      }
    })
  }, [entryPagesData, entryPagesTotals, calcPercentage, isCompareEnabled])

  // Transform 404 pages data
  const pages404ListData = useMemo(() => {
    const totalViews = pages404Data.reduce((sum, row) => sum + Number(row.views), 0) || 1

    return pages404Data.map((row) => {
      const currentValue = Number(row.views) || 0
      const previousValue = Number(row.previous?.views) || 0
      const comparisonProps = isCompareEnabled
        ? {
            ...calcPercentage(currentValue, previousValue),
            tooltipSubtitle: `${__('Previous:', 'wp-statistics')} ${previousValue.toLocaleString()}`,
            comparisonDateLabel,
          }
        : {}

      return {
        label: row.page_uri,
        value: formatCompactNumber(currentValue),
        fillPercentage: calcSharePercentage(currentValue, totalViews),
        ...comparisonProps,
      }
    })
  }, [pages404Data, calcPercentage, isCompareEnabled])

  return (
    <div className="min-w-0">
      {/* Header row */}
      <div className="flex items-center justify-between px-4 py-3 ">
        <h1 className="text-2xl font-semibold text-neutral-800">{__('Page Insights', 'wp-statistics')}</h1>
        <div className="flex items-center gap-3">
          {filterFields && filterFields.length > 0 && isInitialized && (
            <FilterButton
              fields={filterFields}
              appliedFilters={appliedFilters || []}
              onApplyFilters={handleApplyFilters}
              filterGroup="views"
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

      <div className="p-3">
        <NoticeContainer className="mb-2" currentRoute="page-insights-overview" />

        {isError ? (
          <div className="p-4 text-center">
            <ErrorMessage message={__('Failed to load page insights', 'wp-statistics')} />
            <p className="text-sm text-muted-foreground mt-2">{error?.message}</p>
          </div>
        ) : showSkeleton ? (
          <div className="grid gap-3 grid-cols-12">
            {/* Metrics skeleton */}
            <div className="col-span-12">
              <PanelSkeleton showTitle={false}>
                <MetricsSkeleton count={6} columns={6} />
              </PanelSkeleton>
            </div>

            {/* Chart skeleton */}
            <div className="col-span-12">
              <PanelSkeleton titleWidth="w-36">
                <ChartSkeleton height={280} showTitle={false} />
              </PanelSkeleton>
            </div>

            {/* Bar lists skeleton */}
            <div className="col-span-12 lg:col-span-4">
              <PanelSkeleton titleWidth="w-24">
                <BarListSkeleton rows={5} />
              </PanelSkeleton>
            </div>
            <div className="col-span-12 lg:col-span-4">
              <PanelSkeleton titleWidth="w-28">
                <BarListSkeleton rows={5} />
              </PanelSkeleton>
            </div>
            <div className="col-span-12 lg:col-span-4">
              <PanelSkeleton titleWidth="w-24">
                <BarListSkeleton rows={5} />
              </PanelSkeleton>
            </div>
          </div>
        ) : (
          <div className={`grid gap-3 grid-cols-12 ${showFullPageLoading ? 'opacity-60 pointer-events-none' : ''}`}>
            {/* Metrics Panel */}
            <div className="col-span-12">
              <Panel>
                <Metrics metrics={metricsData} columns={6} />
              </Panel>
            </div>

            {/* Page Views Trends Chart */}
            <div className="col-span-12">
              <LineChart
                title={__('Page Views Trends', 'wp-statistics')}
                data={pageViewsTrendsData}
                metrics={chartMetrics}
                showPreviousPeriod={isCompareEnabled}
                timeframe={timeframe}
                onTimeframeChange={handleTimeframeChange}
                loading={isChartRefetching}
                dateTo={apiDateParams.date_to}
                compareDateTo={apiDateParams.previous_date_to}
              />
            </div>

            {/* Top Pages */}
            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Top Pages', 'wp-statistics')}
                items={topPagesListData}
                showComparison={isCompareEnabled}
                link={{
                  action: () => navigate({ to: '/top-pages' }),
                }}
              />
            </div>

            {/* Entry Pages */}
            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Entry Pages', 'wp-statistics')}
                items={entryPagesListData}
                showComparison={isCompareEnabled}
                link={{
                  action: () => navigate({ to: '/entry-pages' }),
                }}
              />
            </div>

            {/* 404 Pages */}
            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('404 Pages', 'wp-statistics')}
                items={pages404ListData}
                showComparison={isCompareEnabled}
                link={{
                  action: () => navigate({ to: '/404-pages' }),
                }}
              />
            </div>
          </div>
        )}
      </div>
    </div>
  )
}
