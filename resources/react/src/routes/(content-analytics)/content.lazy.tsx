import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute, useNavigate } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { useCallback, useEffect, useMemo, useRef, useState } from 'react'

import { type DateRange, DateRangePicker } from '@/components/custom/date-range-picker'
import { FilterBar } from '@/components/custom/filter-bar'
import { FilterButton, type FilterField } from '@/components/custom/filter-button'
import { HorizontalBarList } from '@/components/custom/horizontal-bar-list'
import { LineChart } from '@/components/custom/line-chart'
import { type MetricItem, Metrics } from '@/components/custom/metrics'
import { TabbedList, type TabbedListTab } from '@/components/custom/tabbed-list'
import { Panel } from '@/components/ui/panel'
import { NoticeContainer } from '@/components/ui/notice-container'
import { BarListSkeleton, ChartSkeleton, MetricsSkeleton, PanelSkeleton } from '@/components/ui/skeletons'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { usePercentageCalc } from '@/hooks/use-percentage-calc'
import { calcSharePercentage, formatCompactNumber, formatDecimal, formatDuration } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'
import { getContentOverviewQueryOptions } from '@/services/content-analytics/get-content-overview'

// Helper to extract total value from API response (handles both flat and { current, previous } formats)
function getTotalValue(total: unknown): number {
  if (typeof total === 'number') return total
  if (typeof total === 'string') return Number(total) || 0
  if (typeof total === 'object' && total !== null) {
    const obj = total as { current?: number | string }
    return Number(obj.current) || 0
  }
  return 0
}

export const Route = createLazyFileRoute('/(content-analytics)/content')({
  component: RouteComponent,
  errorComponent: ({ error }) => (
    <div className="p-6 text-center">
      <h2 className="text-xl font-semibold text-destructive mb-2">{__('Error Loading Page', 'wp-statistics')}</h2>
      <p className="text-muted-foreground">{error.message}</p>
    </div>
  ),
})

function RouteComponent() {
  const { resource_id } = Route.useSearch()
  const navigate = useNavigate()

  // If resource_id is provided, redirect to individual-content route
  useEffect(() => {
    if (resource_id) {
      navigate({
        to: '/individual-content',
        search: { resource_id },
        replace: true,
      })
    }
  }, [resource_id, navigate])

  // Show loading while redirecting
  if (resource_id) {
    return null
  }

  // Otherwise show the overview
  return <ContentOverviewView />
}

/**
 * Content Overview View - Main content analytics page
 */
function ContentOverviewView() {
  const {
    dateFrom,
    dateTo,
    compareDateFrom,
    compareDateTo,
    period,
    filters: appliedFilters,
    setDateRange,
    applyFilters: handleApplyFilters,
    removeFilter: handleRemoveFilter,
    isInitialized,
    apiDateParams,
  } = useGlobalFilters()

  const wp = WordPress.getInstance()
  const pluginUrl = wp.getPluginUrl()

  // Get filter fields for content analytics
  const filterFields = useMemo<FilterField[]>(() => {
    return wp.getFilterFieldsByGroup('content') as FilterField[]
  }, [wp])

  const [timeframe, setTimeframe] = useState<'daily' | 'weekly' | 'monthly'>('daily')
  const [activeTab, setActiveTab] = useState<string>('popular')
  const [defaultFilterRemoved, setDefaultFilterRemoved] = useState(false)

  // Build default post_type filter for Content page (page-specific, not global)
  const defaultPostTypeFilter = useMemo(() => {
    const postTypeField = filterFields.find((f) => f.name === 'post_type')
    const postTypeOption = postTypeField?.options?.find((o) => o.value === 'post')

    return {
      id: 'post_type-content-default',
      label: postTypeField?.label || __('Post Type', 'wp-statistics'),
      operator: '=',
      rawOperator: 'is',
      value: postTypeOption?.label || __('Post', 'wp-statistics'),
      rawValue: 'post',
    }
  }, [filterFields])

  // Check if user has applied a post_type filter (overriding default)
  const hasUserPostTypeFilter = useMemo(() => {
    return appliedFilters?.some((f) => f.id.startsWith('post_type')) ?? false
  }, [appliedFilters])

  // Reset defaultFilterRemoved when user applies a post_type filter
  useEffect(() => {
    if (hasUserPostTypeFilter) {
      setDefaultFilterRemoved(false)
    }
  }, [hasUserPostTypeFilter])

  // Filters to use for API requests (includes default if no user filter and not removed)
  const filtersForApi = useMemo(() => {
    if (hasUserPostTypeFilter) {
      return appliedFilters || []
    }
    if (defaultFilterRemoved) {
      return appliedFilters || []
    }
    return [...(appliedFilters || []), defaultPostTypeFilter]
  }, [appliedFilters, hasUserPostTypeFilter, defaultPostTypeFilter, defaultFilterRemoved])

  // Filters to display in FilterBar (includes default if no user filter and not removed)
  const filtersForDisplay = useMemo(() => {
    if (hasUserPostTypeFilter) {
      return appliedFilters || []
    }
    if (defaultFilterRemoved) {
      return appliedFilters || []
    }
    return [...(appliedFilters || []), defaultPostTypeFilter]
  }, [appliedFilters, hasUserPostTypeFilter, defaultPostTypeFilter, defaultFilterRemoved])

  // Wrap handleApplyFilters to detect when post_type filter is intentionally removed
  const handleContentApplyFilters = useCallback(
    (newFilters: typeof appliedFilters) => {
      // Check if post_type filter existed before but not in new filters
      const hadPostTypeFilter = filtersForDisplay.some((f) => f.id.startsWith('post_type'))
      const hasNewPostTypeFilter = newFilters?.some((f) => f.id.startsWith('post_type')) ?? false

      if (hadPostTypeFilter && !hasNewPostTypeFilter) {
        // User intentionally removed the post_type filter
        setDefaultFilterRemoved(true)
      }

      // Apply only the non-default filters to global state
      const globalFilters = newFilters?.filter((f) => f.id !== 'post_type-content-default') ?? []
      handleApplyFilters(globalFilters)
    },
    [filtersForDisplay, handleApplyFilters]
  )

  // Track if only timeframe changed
  const [isTimeframeOnlyChange, setIsTimeframeOnlyChange] = useState(false)
  const prevDateFromRef = useRef<Date | undefined>(dateFrom)
  const prevDateToRef = useRef<Date | undefined>(dateTo)

  useEffect(() => {
    const dateRangeChanged = dateFrom !== prevDateFromRef.current || dateTo !== prevDateToRef.current

    if (dateRangeChanged) {
      setIsTimeframeOnlyChange(false)
    }

    prevDateFromRef.current = dateFrom
    prevDateToRef.current = dateTo
  }, [dateFrom, dateTo])

  const handleTimeframeChange = useCallback((newTimeframe: 'daily' | 'weekly' | 'monthly') => {
    setIsTimeframeOnlyChange(true)
    setTimeframe(newTimeframe)
  }, [])

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
  } = useQuery({
    ...getContentOverviewQueryOptions({
      dateFrom: apiDateParams.date_from,
      dateTo: apiDateParams.date_to,
      compareDateFrom: apiDateParams.previous_date_from,
      compareDateTo: apiDateParams.previous_date_to,
      timeframe,
      filters: filtersForApi,
    }),
    retry: false,
    placeholderData: keepPreviousData,
    enabled: isInitialized,
  })

  const showSkeleton = isLoading && !batchResponse
  const showFullPageLoading = isFetching && !isLoading && !isTimeframeOnlyChange
  const isChartRefetching = isFetching && !isLoading && isTimeframeOnlyChange

  // Extract data from batch response
  const metricsResponse = batchResponse?.data?.items?.content_metrics
  const performanceResponse = batchResponse?.data?.items?.content_performance
  const topReferrersData = batchResponse?.data?.items?.top_referrers?.data?.rows || []
  const topReferrersTotals = batchResponse?.data?.items?.top_referrers?.data?.totals
  const topSearchEnginesData = batchResponse?.data?.items?.top_search_engines?.data?.rows || []
  const topSearchEnginesTotals = batchResponse?.data?.items?.top_search_engines?.data?.totals
  const topCountriesData = batchResponse?.data?.items?.top_countries?.data?.rows || []
  const topCountriesTotals = batchResponse?.data?.items?.top_countries?.data?.totals
  const topBrowsersData = batchResponse?.data?.items?.top_browsers?.data?.rows || []
  const topBrowsersTotals = batchResponse?.data?.items?.top_browsers?.data?.totals
  const topOSData = batchResponse?.data?.items?.top_os?.data?.rows || []
  const topOSTotals = batchResponse?.data?.items?.top_os?.data?.totals
  const topDevicesData = batchResponse?.data?.items?.top_devices?.data?.rows || []
  const topDevicesTotals = batchResponse?.data?.items?.top_devices?.data?.totals

  // Transform chart data
  const chartData = useMemo(() => {
    if (!performanceResponse?.labels || !performanceResponse?.datasets) return []

    const labels = performanceResponse.labels
    const datasets = performanceResponse.datasets
    const currentDatasets = datasets.filter((d) => !d.comparison)
    const previousDatasets = datasets.filter((d) => d.comparison)

    return labels.map((label, index) => {
      const point: { date: string; [key: string]: string | number } = { date: label }

      currentDatasets.forEach((dataset) => {
        point[dataset.key] = Number(dataset.data[index]) || 0
      })

      previousDatasets.forEach((dataset) => {
        const baseKey = dataset.key.replace('_previous', '')
        point[`${baseKey}Previous`] = Number(dataset.data[index]) || 0
      })

      return point
    })
  }, [performanceResponse])

  // Calculate chart totals
  const chartTotals = useMemo(() => {
    if (!performanceResponse?.datasets) {
      return { visitors: 0, visitorsPrevious: 0, views: 0, viewsPrevious: 0 }
    }

    const datasets = performanceResponse.datasets
    const visitorsDataset = datasets.find((d) => d.key === 'visitors' && !d.comparison)
    const visitorsPrevDataset = datasets.find((d) => d.key === 'visitors_previous' && d.comparison)
    const viewsDataset = datasets.find((d) => d.key === 'views' && !d.comparison)
    const viewsPrevDataset = datasets.find((d) => d.key === 'views_previous' && d.comparison)

    return {
      visitors: visitorsDataset?.data?.reduce((sum: number, v) => sum + Number(v), 0) || 0,
      visitorsPrevious: visitorsPrevDataset?.data?.reduce((sum: number, v) => sum + Number(v), 0) || 0,
      views: viewsDataset?.data?.reduce((sum: number, v) => sum + Number(v), 0) || 0,
      viewsPrevious: viewsPrevDataset?.data?.reduce((sum: number, v) => sum + Number(v), 0) || 0,
    }
  }, [performanceResponse])

  const chartMetrics = [
    {
      key: 'visitors',
      label: __('Visitors', 'wp-statistics'),
      color: 'var(--chart-1)',
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
    {
      key: 'views',
      label: __('Views', 'wp-statistics'),
      color: 'var(--chart-2)',
      enabled: true,
      value:
        chartTotals.views >= 1000 ? `${formatDecimal(chartTotals.views / 1000)}k` : formatDecimal(chartTotals.views),
      previousValue:
        chartTotals.viewsPrevious >= 1000
          ? `${formatDecimal(chartTotals.viewsPrevious / 1000)}k`
          : formatDecimal(chartTotals.viewsPrevious),
    },
  ]

  const calcPercentage = usePercentageCalc()

  // Get post type label from filters (includes page-specific default)
  const postTypeLabel = useMemo(() => {
    // Look for post_type filter in filters for API (includes default)
    const postTypeFilter = filtersForApi.find((f) => f.id.startsWith('post_type'))
    if (postTypeFilter?.value) {
      // Return the display value which is the label
      return String(postTypeFilter.value)
    }
    return __('Content', 'wp-statistics')
  }, [filtersForApi])

  // Build metrics
  const contentMetrics = useMemo(() => {
    const totals = metricsResponse?.totals
    if (!totals) return []

    const publishedContent = Number(totals.published_content?.current) || 0
    const visitors = Number(totals.visitors?.current) || 0
    const views = Number(totals.views?.current) || 0
    const bounceRate = Number(totals.bounce_rate?.current) || 0
    const avgTimeOnPage = Number(totals.avg_time_on_page?.current) || 0
    const comments = Number(totals.comments?.current) || 0

    const prevPublishedContent = Number(totals.published_content?.previous) || 0
    const prevVisitors = Number(totals.visitors?.previous) || 0
    const prevViews = Number(totals.views?.previous) || 0
    const prevBounceRate = Number(totals.bounce_rate?.previous) || 0
    const prevAvgTimeOnPage = Number(totals.avg_time_on_page?.previous) || 0
    const prevComments = Number(totals.comments?.previous) || 0

    const viewsPerPost = publishedContent > 0 ? views / publishedContent : 0
    const prevViewsPerPost = prevPublishedContent > 0 ? prevViews / prevPublishedContent : 0

    const avgCommentsPerPost = publishedContent > 0 ? comments / publishedContent : 0
    const prevAvgCommentsPerPost = prevPublishedContent > 0 ? prevComments / prevPublishedContent : 0

    const metrics: MetricItem[] = [
      {
        label: `${__('Published', 'wp-statistics')} ${postTypeLabel}`,
        value: formatCompactNumber(publishedContent),
        ...calcPercentage(publishedContent, prevPublishedContent),
        tooltipContent: __('Number of published content items', 'wp-statistics'),
      },
      {
        label: __('Visitors', 'wp-statistics'),
        value: formatCompactNumber(visitors),
        ...calcPercentage(visitors, prevVisitors),
        tooltipContent: __('Unique visitors to content', 'wp-statistics'),
      },
      {
        label: __('Views', 'wp-statistics'),
        value: formatCompactNumber(views),
        ...calcPercentage(views, prevViews),
        tooltipContent: __('Total page views', 'wp-statistics'),
      },
      {
        label: `${__('Views per', 'wp-statistics')} ${postTypeLabel}`,
        value: formatDecimal(viewsPerPost),
        ...calcPercentage(viewsPerPost, prevViewsPerPost),
        tooltipContent: __('Average views per content item', 'wp-statistics'),
      },
      {
        label: __('Bounce Rate', 'wp-statistics'),
        value: `${formatDecimal(bounceRate)}%`,
        ...calcPercentage(bounceRate, prevBounceRate),
        tooltipContent: __('Percentage of single-page sessions', 'wp-statistics'),
      },
      {
        label: __('Time on Page', 'wp-statistics'),
        value: formatDuration(avgTimeOnPage),
        ...calcPercentage(avgTimeOnPage, prevAvgTimeOnPage),
        tooltipContent: __('Average time spent on content', 'wp-statistics'),
      },
      {
        label: __('Comments', 'wp-statistics'),
        value: formatCompactNumber(comments),
        ...calcPercentage(comments, prevComments),
        tooltipContent: __('Total comments on content', 'wp-statistics'),
      },
      {
        label: `${__('Avg. Comments per', 'wp-statistics')} ${postTypeLabel}`,
        value: formatDecimal(avgCommentsPerPost),
        ...calcPercentage(avgCommentsPerPost, prevAvgCommentsPerPost),
        tooltipContent: __('Average comments per content item', 'wp-statistics'),
      },
    ]

    return metrics
  }, [metricsResponse, postTypeLabel, calcPercentage])

  // Build tabbed list tabs for top content
  const topContentTabs: TabbedListTab[] = useMemo(() => {
    const topContentPopular = batchResponse?.data?.items?.top_content_popular?.data?.rows || []
    const topContentCommented = batchResponse?.data?.items?.top_content_commented?.data?.rows || []
    const topContentRecent = batchResponse?.data?.items?.top_content_recent?.data?.rows || []

    // Build "See all" link with date range preserved
    const topPagesBaseUrl = '/pages'
    const dateParams = `date_from=${apiDateParams.date_from}&date_to=${apiDateParams.date_to}`
    // Extract post_type from filters (includes page-specific default)
    const postTypeFilter = filtersForApi.find((f) => f.id.startsWith('post_type'))
    const postTypeValue = postTypeFilter?.rawValue || 'post'
    const postTypeParam = `post_type=${postTypeValue}`

    return [
      {
        id: 'popular',
        label: __('Most Popular', 'wp-statistics'),
        items: topContentPopular.map((item) => ({
          id: String(item.resource_id),
          title: item.page_title || item.page_uri,
          subtitle: `${formatCompactNumber(Number(item.views))} ${__('views', 'wp-statistics')}`,
          thumbnail: item.thumbnail_url || `${pluginUrl}public/images/placeholder.png`,
          href: `/individual-content?resource_id=${item.resource_id}`,
        })),
        link: {
          title: `${__('See all', 'wp-statistics')} ${postTypeLabel}`,
          href: `${topPagesBaseUrl}?${dateParams}&${postTypeParam}&order_by=views&order=desc`,
        },
      },
      {
        id: 'commented',
        label: __('Most Commented', 'wp-statistics'),
        items: topContentCommented.map((item) => ({
          id: String(item.resource_id),
          title: item.page_title || item.page_uri,
          subtitle: `${formatCompactNumber(Number(item.comments || 0))} ${__('comments', 'wp-statistics')} · ${formatCompactNumber(Number(item.views))} ${__('views', 'wp-statistics')}`,
          thumbnail: item.thumbnail_url || `${pluginUrl}public/images/placeholder.png`,
          href: `/individual-content?resource_id=${item.resource_id}`,
        })),
        // No link for Most Commented per spec
      },
      {
        id: 'recent',
        label: __('Most Recent', 'wp-statistics'),
        items: topContentRecent.map((item) => ({
          id: String(item.resource_id),
          title: item.page_title || item.page_uri,
          subtitle: `${item.published_date || ''} · ${formatCompactNumber(Number(item.views))} ${__('views', 'wp-statistics')}`,
          thumbnail: item.thumbnail_url || `${pluginUrl}public/images/placeholder.png`,
          href: `/individual-content?resource_id=${item.resource_id}`,
        })),
        link: {
          title: `${__('See all', 'wp-statistics')} ${postTypeLabel}`,
          href: `${topPagesBaseUrl}?${dateParams}&${postTypeParam}&order_by=date&order=desc`,
        },
      },
    ]
  }, [batchResponse, apiDateParams, filtersForApi, postTypeLabel, pluginUrl])

  return (
    <div className="min-w-0">
      {/* Header */}
      <div className="flex items-center justify-between px-4 py-3 ">
        <h1 className="text-2xl font-semibold text-neutral-800">{__('Content', 'wp-statistics')}</h1>
        <div className="flex items-center gap-3">
          {filterFields.length > 0 && isInitialized && (
            <FilterButton
              fields={filterFields}
              appliedFilters={filtersForDisplay}
              onApplyFilters={handleContentApplyFilters}
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
        <NoticeContainer className="mb-2" currentRoute="content" />
        {filtersForDisplay.length > 0 && (
          <FilterBar
            filters={filtersForDisplay}
            onRemoveFilter={(filterId) => {
              // If removing the default post_type filter, clear it by setting a flag
              if (filterId === 'post_type-content-default') {
                setDefaultFilterRemoved(true)
                return
              }
              handleRemoveFilter(filterId)
            }}
            className="mb-2"
          />
        )}

        {showSkeleton || showFullPageLoading ? (
          <div className="grid gap-3 grid-cols-12">
            <div className="col-span-12">
              <PanelSkeleton showTitle={false}>
                <MetricsSkeleton count={8} columns={4} />
              </PanelSkeleton>
            </div>
            <div className="col-span-12">
              <PanelSkeleton titleWidth="w-32">
                <ChartSkeleton height={256} showTitle={false} />
              </PanelSkeleton>
            </div>
            <div className="col-span-12">
              <PanelSkeleton>
                <BarListSkeleton items={5} showIcon />
              </PanelSkeleton>
            </div>
            {[1, 2, 3].map((i) => (
              <div key={i} className="col-span-12 lg:col-span-4">
                <PanelSkeleton>
                  <BarListSkeleton items={5} />
                </PanelSkeleton>
              </div>
            ))}
            {[1, 2, 3].map((i) => (
              <div key={`device-${i}`} className="col-span-12 lg:col-span-4">
                <PanelSkeleton>
                  <BarListSkeleton items={5} showIcon />
                </PanelSkeleton>
              </div>
            ))}
          </div>
        ) : (
          <div className="grid gap-3 grid-cols-12">
            {/* Row 1: Content Metrics */}
            <div className="col-span-12">
              <Panel>
                <Metrics metrics={contentMetrics} columns={4} />
              </Panel>
            </div>

            {/* Row 2: Content Performance Chart */}
            <div className="col-span-12">
              <LineChart
                title={__('Content Performance', 'wp-statistics')}
                data={chartData}
                metrics={chartMetrics}
                showPreviousPeriod={true}
                timeframe={timeframe}
                onTimeframeChange={handleTimeframeChange}
                loading={isChartRefetching}
              />
            </div>

            {/* Row 3: Top Content Tabbed List */}
            <div className="col-span-12">
              <TabbedList
                title={__('Top Content', 'wp-statistics')}
                tabs={topContentTabs}
                activeTab={activeTab}
                onTabChange={setActiveTab}
                emptyMessage={__('No content available for the selected period', 'wp-statistics')}
              />
            </div>

            {/* Row 4: Traffic Sources (3 columns) */}
            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Top Referrers', 'wp-statistics')}
                items={(() => {
                  const totalVisitors = getTotalValue(topReferrersTotals?.visitors) || 1
                  return topReferrersData.map((item) => {
                    const currentValue = Number(item.visitors) || 0
                    const previousValue = Number(item.previous?.visitors) || 0
                    const { percentage, isNegative } = calcPercentage(currentValue, previousValue)
                    const displayName = item.referrer_name || item.referrer_domain || __('Direct', 'wp-statistics')

                    return {
                      label: displayName,
                      value: currentValue,
                      percentage,
                      fillPercentage: calcSharePercentage(currentValue, totalVisitors),
                      isNegative,
                      tooltipTitle: displayName,
                      tooltipSubtitle: `${__('Previous:', 'wp-statistics')} ${previousValue.toLocaleString()}`,
                    }
                  })
                })()}
              />
            </div>

            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Top Search Engines', 'wp-statistics')}
                items={(() => {
                  const totalVisitors = getTotalValue(topSearchEnginesTotals?.visitors) || 1
                  return topSearchEnginesData.map((item) => {
                    const currentValue = Number(item.visitors) || 0
                    const previousValue = Number(item.previous?.visitors) || 0
                    const { percentage, isNegative } = calcPercentage(currentValue, previousValue)
                    const displayName = item.referrer_name || item.referrer_domain || __('Unknown', 'wp-statistics')

                    return {
                      label: displayName,
                      value: currentValue,
                      percentage,
                      fillPercentage: calcSharePercentage(currentValue, totalVisitors),
                      isNegative,
                      tooltipTitle: displayName,
                      tooltipSubtitle: `${__('Previous:', 'wp-statistics')} ${previousValue.toLocaleString()}`,
                    }
                  })
                })()}
              />
            </div>

            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Top Countries', 'wp-statistics')}
                items={(() => {
                  const totalVisitors = getTotalValue(topCountriesTotals?.visitors) || 1
                  return topCountriesData.map((item) => {
                    const currentValue = Number(item.visitors) || 0
                    const previousValue = Number(item.previous?.visitors) || 0
                    const { percentage, isNegative } = calcPercentage(currentValue, previousValue)

                    return {
                      icon: (
                        <img
                          src={`${pluginUrl}public/images/flags/${item.country_code?.toLowerCase() || '000'}.svg`}
                          alt={item.country_name || ''}
                          className="w-4 h-3"
                        />
                      ),
                      label: item.country_name || __('Unknown', 'wp-statistics'),
                      value: currentValue,
                      percentage,
                      fillPercentage: calcSharePercentage(currentValue, totalVisitors),
                      isNegative,
                      tooltipTitle: item.country_name || '',
                      tooltipSubtitle: `${__('Previous:', 'wp-statistics')} ${previousValue.toLocaleString()}`,
                    }
                  })
                })()}
              />
            </div>

            {/* Row 5: Device Analytics (3 columns) */}
            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Top Browsers', 'wp-statistics')}
                items={(() => {
                  const totalVisitors = getTotalValue(topBrowsersTotals?.visitors) || 1
                  return topBrowsersData.map((item) => {
                    const currentValue = Number(item.visitors) || 0
                    const previousValue = Number(item.previous?.visitors) || 0
                    const { percentage, isNegative } = calcPercentage(currentValue, previousValue)
                    const iconName = (item.browser_name || 'unknown').toLowerCase()

                    return {
                      icon: (
                        <img
                          src={`${pluginUrl}public/images/browser/${iconName}.svg`}
                          alt={item.browser_name || ''}
                          className="w-4 h-3"
                        />
                      ),
                      label: item.browser_name || __('Unknown', 'wp-statistics'),
                      value: currentValue,
                      percentage,
                      fillPercentage: calcSharePercentage(currentValue, totalVisitors),
                      isNegative,
                      tooltipTitle: item.browser_name || '',
                      tooltipSubtitle: `${__('Previous:', 'wp-statistics')} ${previousValue.toLocaleString()}`,
                    }
                  })
                })()}
              />
            </div>

            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Top Operating Systems', 'wp-statistics')}
                items={(() => {
                  const totalVisitors = getTotalValue(topOSTotals?.visitors) || 1
                  return topOSData.map((item) => {
                    const currentValue = Number(item.visitors) || 0
                    const previousValue = Number(item.previous?.visitors) || 0
                    const { percentage, isNegative } = calcPercentage(currentValue, previousValue)
                    const iconName = (item.os_name || 'unknown').toLowerCase().replace(/\s+/g, '_')

                    return {
                      icon: (
                        <img
                          src={`${pluginUrl}public/images/operating-system/${iconName}.svg`}
                          alt={item.os_name || ''}
                          className="w-4 h-3"
                        />
                      ),
                      label: item.os_name || __('Unknown', 'wp-statistics'),
                      value: currentValue,
                      percentage,
                      fillPercentage: calcSharePercentage(currentValue, totalVisitors),
                      isNegative,
                      tooltipTitle: item.os_name || '',
                      tooltipSubtitle: `${__('Previous:', 'wp-statistics')} ${previousValue.toLocaleString()}`,
                    }
                  })
                })()}
              />
            </div>

            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Top Device Categories', 'wp-statistics')}
                items={(() => {
                  const totalVisitors = getTotalValue(topDevicesTotals?.visitors) || 1
                  return topDevicesData.map((item) => {
                    const currentValue = Number(item.visitors) || 0
                    const previousValue = Number(item.previous?.visitors) || 0
                    const { percentage, isNegative } = calcPercentage(currentValue, previousValue)
                    const iconName = (item.device_type_name || 'desktop').toLowerCase()

                    return {
                      icon: (
                        <img
                          src={`${pluginUrl}public/images/device/${iconName}.svg`}
                          alt={item.device_type_name || ''}
                          className="w-4 h-3"
                        />
                      ),
                      label: item.device_type_name || __('Unknown', 'wp-statistics'),
                      value: currentValue,
                      percentage,
                      fillPercentage: calcSharePercentage(currentValue, totalVisitors),
                      isNegative,
                      tooltipTitle: item.device_type_name || '',
                      tooltipSubtitle: `${__('Previous:', 'wp-statistics')} ${previousValue.toLocaleString()}`,
                    }
                  })
                })()}
              />
            </div>
          </div>
        )}
      </div>
    </div>
  )
}

