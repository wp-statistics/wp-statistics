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
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { usePercentageCalc } from '@/hooks/use-percentage-calc'
import { calcSharePercentage, decodeText, formatCompactNumber, formatDecimal, formatDuration } from '@/lib/utils'
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

  // Transform metrics to component format
  const metricsData = useMemo<MetricItem[]>(() => {
    const totals = metricsResponse?.totals
    if (!totals) return []

    const viewsPerc = calcPercentage(Number(totals.views?.current) || 0, Number(totals.views?.previous) || 0)
    const visitorsPerc = calcPercentage(Number(totals.visitors?.current) || 0, Number(totals.visitors?.previous) || 0)
    const avgTimePerc = calcPercentage(Number(totals.avg_time_on_page?.current) || 0, Number(totals.avg_time_on_page?.previous) || 0)
    const bouncePerc = calcPercentage(Number(totals.bounce_rate?.current) || 0, Number(totals.bounce_rate?.previous) || 0)
    const pagesPerSessionPerc = calcPercentage(Number(totals.pages_per_session?.current) || 0, Number(totals.pages_per_session?.previous) || 0)

    return [
      {
        label: __('Views', 'wp-statistics'),
        value: formatCompactNumber(Number(totals.views?.current) || 0),
        percentage: viewsPerc.percentage,
        isNegative: viewsPerc.isNegative,
      },
      {
        label: __('Visitors', 'wp-statistics'),
        value: formatCompactNumber(Number(totals.visitors?.current) || 0),
        percentage: visitorsPerc.percentage,
        isNegative: visitorsPerc.isNegative,
      },
      {
        label: __('Avg. Time on Page', 'wp-statistics'),
        value: formatDuration(Number(totals.avg_time_on_page?.current) || 0),
        percentage: avgTimePerc.percentage,
        isNegative: avgTimePerc.isNegative,
      },
      {
        label: __('Bounce Rate', 'wp-statistics'),
        value: `${formatDecimal(Number(totals.bounce_rate?.current) || 0)}%`,
        percentage: bouncePerc.percentage,
        isNegative: !bouncePerc.isNegative, // Invert for bounce rate (lower is better)
      },
      {
        label: __('Pages/Session', 'wp-statistics'),
        value: formatDecimal(Number(totals.pages_per_session?.current) || 0),
        percentage: pagesPerSessionPerc.percentage,
        isNegative: pagesPerSessionPerc.isNegative,
      },
      {
        label: __('Top Content Type', 'wp-statistics'),
        value: metricsTopPostType?.items?.[0]?.page_type || '-',
      },
    ]
  }, [metricsResponse, metricsTopPostType, calcPercentage])

  // Transform page views trends chart data
  const pageViewsTrendsData = useMemo(() => {
    if (!pageViewsTrendsResponse?.labels || !pageViewsTrendsResponse?.datasets) return []

    const labels = pageViewsTrendsResponse.labels
    const datasets = pageViewsTrendsResponse.datasets

    const currentDatasets = datasets.filter((d) => !d.comparison)
    const previousDatasets = datasets.filter((d) => d.comparison)

    return labels.map((label, index) => {
      const point: Record<string, string | number> = { date: label }

      currentDatasets.forEach((dataset) => {
        point[dataset.key] = Number(dataset.data[index]) || 0
      })

      previousDatasets.forEach((dataset) => {
        const baseKey = dataset.key.replace('_previous', '')
        point[`${baseKey}Previous`] = Number(dataset.data[index]) || 0
      })

      return point
    })
  }, [pageViewsTrendsResponse])

  // Calculate totals for chart metrics
  const chartTotals = useMemo(() => {
    if (!pageViewsTrendsResponse?.datasets) {
      return { views: 0, viewsPrevious: 0, visitors: 0, visitorsPrevious: 0 }
    }

    const datasets = pageViewsTrendsResponse.datasets
    const viewsDataset = datasets.find((d) => d.key === 'views' && !d.comparison)
    const viewsPrevDataset = datasets.find((d) => d.key === 'views_previous' && d.comparison)
    const visitorsDataset = datasets.find((d) => d.key === 'visitors' && !d.comparison)
    const visitorsPrevDataset = datasets.find((d) => d.key === 'visitors_previous' && d.comparison)

    return {
      views: viewsDataset?.data?.reduce((sum, v) => sum + Number(v), 0) || 0,
      viewsPrevious: viewsPrevDataset?.data?.reduce((sum, v) => sum + Number(v), 0) || 0,
      visitors: visitorsDataset?.data?.reduce((sum, v) => sum + Number(v), 0) || 0,
      visitorsPrevious: visitorsPrevDataset?.data?.reduce((sum, v) => sum + Number(v), 0) || 0,
    }
  }, [pageViewsTrendsResponse])

  // Define chart metrics
  const chartMetrics = useMemo(() => {
    return [
      {
        key: 'views',
        label: __('Views', 'wp-statistics'),
        color: 'var(--chart-1)',
        enabled: true,
        value: chartTotals.views >= 1000 ? `${formatDecimal(chartTotals.views / 1000)}k` : formatDecimal(chartTotals.views),
        previousValue:
          chartTotals.viewsPrevious >= 1000
            ? `${formatDecimal(chartTotals.viewsPrevious / 1000)}k`
            : formatDecimal(chartTotals.viewsPrevious),
      },
      {
        key: 'visitors',
        label: __('Visitors', 'wp-statistics'),
        color: 'var(--chart-2)',
        enabled: true,
        value:
          chartTotals.visitors >= 1000
            ? `${formatDecimal(chartTotals.visitors / 1000)}k`
            : formatDecimal(chartTotals.visitors),
        previousValue:
          chartTotals.visitorsPrevious >= 1000
            ? `${formatDecimal(chartTotals.visitorsPrevious / 1000)}k`
            : formatDecimal(chartTotals.visitorsPrevious),
      },
    ]
  }, [chartTotals])

  // Transform top pages data
  const topPagesListData = useMemo(() => {
    const totalViews = Number(topPagesTotals?.views) || topPagesData.reduce((sum, row) => sum + Number(row.views), 0) || 1

    return topPagesData.map((row) => {
      const hasPrevious = row.previous !== undefined
      const percentageResult = hasPrevious
        ? calcPercentage(Number(row.views), Number(row.previous?.views) || 0)
        : { percentage: '0', isNegative: false }

      return {
        label: decodeText(row.page_title) || row.page_uri,
        value: formatCompactNumber(Number(row.views)),
        percentage: percentageResult.percentage,
        isNegative: percentageResult.isNegative,
        fillPercentage: calcSharePercentage(Number(row.views), totalViews),
      }
    })
  }, [topPagesData, topPagesTotals, calcPercentage])

  // Transform entry pages data
  const entryPagesListData = useMemo(() => {
    const totalSessions =
      Number(entryPagesTotals?.sessions) || entryPagesData.reduce((sum, row) => sum + Number(row.sessions), 0) || 1

    return entryPagesData.map((row) => {
      const hasPrevious = row.previous !== undefined
      const percentageResult = hasPrevious
        ? calcPercentage(Number(row.sessions), Number(row.previous?.sessions) || 0)
        : { percentage: '0', isNegative: false }

      return {
        label: decodeText(row.page_title) || row.page_uri,
        value: formatCompactNumber(Number(row.sessions)),
        percentage: percentageResult.percentage,
        isNegative: percentageResult.isNegative,
        fillPercentage: calcSharePercentage(Number(row.sessions), totalSessions),
      }
    })
  }, [entryPagesData, entryPagesTotals, calcPercentage])

  // Transform 404 pages data
  const pages404ListData = useMemo(() => {
    const totalViews = pages404Data.reduce((sum, row) => sum + Number(row.views), 0) || 1

    return pages404Data.map((row) => {
      const hasPrevious = row.previous !== undefined
      const percentageResult = hasPrevious
        ? calcPercentage(Number(row.views), Number(row.previous?.views) || 0)
        : { percentage: '0', isNegative: false }

      return {
        label: row.page_uri,
        value: formatCompactNumber(Number(row.views)),
        percentage: percentageResult.percentage,
        isNegative: percentageResult.isNegative,
        fillPercentage: calcSharePercentage(Number(row.views), totalViews),
      }
    })
  }, [pages404Data, calcPercentage])

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
                showPreviousPeriod={!!(compareDateFrom && compareDateTo)}
                timeframe={timeframe}
                onTimeframeChange={handleTimeframeChange}
                loading={isChartRefetching}
              />
            </div>

            {/* Top Pages */}
            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Top Pages', 'wp-statistics')}
                items={topPagesListData}
                link={{
                  title: __('View All Pages', 'wp-statistics'),
                  action: () => navigate({ to: '/top-pages' }),
                }}
              />
            </div>

            {/* Entry Pages */}
            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Entry Pages', 'wp-statistics')}
                items={entryPagesListData}
                link={{
                  title: __('View All Entry Pages', 'wp-statistics'),
                  action: () => navigate({ to: '/entry-pages' }),
                }}
              />
            </div>

            {/* 404 Pages */}
            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('404 Pages', 'wp-statistics')}
                items={pages404ListData}
                link={{
                  title: __('View All 404 Pages', 'wp-statistics'),
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
