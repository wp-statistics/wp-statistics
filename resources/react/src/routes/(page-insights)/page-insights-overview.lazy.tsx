import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute, useNavigate } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { useMemo } from 'react'

import { HorizontalBarList } from '@/components/custom/horizontal-bar-list'
import { Metrics } from '@/components/custom/metrics'
import {
  type OverviewOptionsConfig,
  OverviewOptionsDrawer,
  OverviewOptionsProvider,
  useOverviewOptions,
} from '@/components/custom/options-drawer'
import { ReportPageHeader } from '@/components/custom/report-page-header'
import { NoticeContainer } from '@/components/ui/notice-container'
import { Panel } from '@/components/ui/panel'
import {
  BarListSkeleton,
  MetricsSkeleton,
  PanelSkeleton,
} from '@/components/ui/skeletons'
import { useContentRegistry, type WidgetRenderProps } from '@/contexts/content-registry-context'
import { type MetricConfig, type WidgetConfig } from '@/contexts/page-options-context'
import { useComparisonDateLabel } from '@/hooks/use-comparison-date-label'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { usePageOptions } from '@/hooks/use-page-options'
import { usePercentageCalc } from '@/hooks/use-percentage-calc'
import { transformToBarList } from '@/lib/bar-list-helpers'
import { getAnalyticsRoute } from '@/lib/url-utils'
import { formatCompactNumber, formatDecimal, formatDuration, getTotalValue } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'
import { getPageInsightsOverviewQueryOptions } from '@/services/page-insight/get-page-insights-overview'

// Widget configuration for this page (core widgets only)
// Premium widgets are dynamically registered via ContentRegistry
const WIDGET_CONFIGS: WidgetConfig[] = [
  { id: 'metrics', label: __('Metrics Overview', 'wp-statistics'), defaultVisible: true },
  { id: 'top-pages', label: __('Top Pages', 'wp-statistics'), defaultVisible: true },
  { id: '404-pages', label: __('404 Pages', 'wp-statistics'), defaultVisible: true },
  { id: 'by-category', label: __('Category Pages', 'wp-statistics'), defaultVisible: true },
  { id: 'by-author', label: __('Author Pages', 'wp-statistics'), defaultVisible: true },
]

// Metric configuration for this page
const METRIC_CONFIGS: MetricConfig[] = [
  { id: 'total-views', label: __('Total Views', 'wp-statistics'), defaultVisible: true },
  { id: 'bounce-rate', label: __('Bounce Rate', 'wp-statistics'), defaultVisible: true },
  { id: 'avg-time-on-page', label: __('Avg Time on Page', 'wp-statistics'), defaultVisible: true },
  { id: 'top-page', label: __('Top Page', 'wp-statistics'), defaultVisible: true },
]

// Options configuration for this page
const OPTIONS_CONFIG: OverviewOptionsConfig = {
  pageId: 'page-insights-overview',
  filterGroup: 'views',
  widgetConfigs: WIDGET_CONFIGS,
  metricConfigs: METRIC_CONFIGS,
}

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
  return (
    <OverviewOptionsProvider config={OPTIONS_CONFIG}>
      <PageInsightsOverviewContent />
    </OverviewOptionsProvider>
  )
}

function PageInsightsOverviewContent() {
  const navigate = useNavigate()

  // Use global filters context for date range and filters (hybrid URL + preferences)
  const {
    filters: appliedFilters,
    isInitialized,
    isCompareEnabled,
    apiDateParams,
  } = useGlobalFilters()

  // Page options for widget/metric visibility
  const { isWidgetVisible, isMetricVisible } = usePageOptions()

  // Get registered widgets from premium plugins
  const { getWidgetsForPage } = useContentRegistry()
  const registeredWidgets = getWidgetsForPage('page-insights-overview')

  // Options drawer - config is passed once and returned for drawer
  const options = useOverviewOptions(OPTIONS_CONFIG)

  const wp = WordPress.getInstance()

  // Batch query for all overview data (only when filters are initialized)
  const {
    data: batchResponse,
    isLoading,
    isFetching,
  } = useQuery({
    ...getPageInsightsOverviewQueryOptions({
      dateFrom: apiDateParams.date_from,
      dateTo: apiDateParams.date_to,
      compareDateFrom: apiDateParams.previous_date_from,
      compareDateTo: apiDateParams.previous_date_to,
      filters: appliedFilters || [],
    }),
    retry: false,
    placeholderData: keepPreviousData,
    enabled: isInitialized,
  })

  // Only show skeleton on initial load (no data yet), not on refetches
  const showSkeleton = isLoading && !batchResponse
  // Show full page loading when filters/dates change
  const showFullPageLoading = isFetching && !isLoading

  // Extract data from batch response
  const metricsResponse = batchResponse?.data?.items?.metrics
  const metricsTopPage = batchResponse?.data?.items?.metrics_top_page
  const topPagesData = batchResponse?.data?.items?.top_pages?.data?.rows || []
  const topPagesTotals = batchResponse?.data?.items?.top_pages?.data?.totals
  const pages404Data = batchResponse?.data?.items?.pages_404?.data?.rows || []
  const pages404Totals = batchResponse?.data?.items?.pages_404?.data?.totals
  const byCategoryData = batchResponse?.data?.items?.by_category?.data?.rows || []
  const byCategoryTotals = batchResponse?.data?.items?.by_category?.data?.totals
  const byAuthorData = batchResponse?.data?.items?.by_author?.data?.rows || []
  const byAuthorTotals = batchResponse?.data?.items?.by_author?.data?.totals

  // Helper to safely extract total value (handles both direct values and {current, previous} objects)
  const getTotalFromResponse = (totals: Record<string, unknown> | undefined, key: string): number => {
    const value = totals?.[key]
    if (typeof value === 'number') return value
    if (typeof value === 'string') return Number(value) || 0
    if (value && typeof value === 'object' && 'current' in value) {
      return Number((value as { current: unknown }).current) || 0
    }
    return 0
  }

  // Use the shared percentage calculation hook
  const calcPercentage = usePercentageCalc()
  // Get comparison date label for tooltips
  const { label: comparisonDateLabel } = useComparisonDateLabel()

  // Build metrics from batch response
  const overviewMetrics = useMemo(() => {
    const totals = metricsResponse?.totals

    if (!totals) return []

    // Extract current and previous values
    const views = getTotalValue(totals.views)
    const bounceRate = getTotalValue(totals.bounce_rate)
    const avgTimeOnPage = getTotalValue(totals.avg_time_on_page)

    const prevViews = getTotalValue(totals.views?.previous)
    const prevBounceRate = getTotalValue(totals.bounce_rate?.previous)
    const prevAvgTimeOnPage = getTotalValue(totals.avg_time_on_page?.previous)

    // Context metric
    const topPageName = metricsTopPage?.items?.[0]?.page_title

    // Build all metrics with IDs for filtering
    const allMetrics = [
      {
        id: 'total-views',
        label: __('Total Views', 'wp-statistics'),
        value: formatCompactNumber(views),
        ...(isCompareEnabled
          ? {
              ...calcPercentage(views, prevViews),
              comparisonDateLabel,
              previousValue: formatCompactNumber(prevViews),
            }
          : {}),
      },
      {
        id: 'bounce-rate',
        label: __('Bounce Rate', 'wp-statistics'),
        value: `${formatDecimal(bounceRate)}%`,
        ...(isCompareEnabled
          ? {
              ...calcPercentage(bounceRate, prevBounceRate),
              comparisonDateLabel,
              previousValue: `${formatDecimal(prevBounceRate)}%`,
            }
          : {}),
      },
      {
        id: 'avg-time-on-page',
        label: __('Avg Time on Page', 'wp-statistics'),
        value: formatDuration(avgTimeOnPage),
        ...(isCompareEnabled
          ? {
              ...calcPercentage(avgTimeOnPage, prevAvgTimeOnPage),
              comparisonDateLabel,
              previousValue: formatDuration(prevAvgTimeOnPage),
            }
          : {}),
      },
      {
        id: 'top-page',
        label: __('Top Page', 'wp-statistics'),
        value: topPageName || '-',
      },
    ]

    // Filter metrics based on visibility
    return allMetrics.filter((metric) => isMetricVisible(metric.id))
  }, [metricsResponse, metricsTopPage, isCompareEnabled, comparisonDateLabel, isMetricVisible, calcPercentage])

  return (
    <div className="min-w-0">
      <ReportPageHeader
        title={__('Page Insights', 'wp-statistics')}
        filterGroup="views"
        optionsTriggerProps={options.triggerProps}
      />

      {/* Options Drawer */}
      <OverviewOptionsDrawer {...options} />

      <div className="p-3">
        <NoticeContainer className="mb-3" currentRoute="page-insights-overview" />

        {showSkeleton || showFullPageLoading ? (
          <div className="grid gap-3 grid-cols-12">
            {/* Metrics skeleton */}
            <div className="col-span-12">
              <PanelSkeleton showTitle={false}>
                <MetricsSkeleton count={4} columns={4} />
              </PanelSkeleton>
            </div>
            {/* Two column bar lists skeleton (Top Pages + 404 Pages) */}
            <div className="col-span-12 lg:col-span-6">
              <PanelSkeleton>
                <BarListSkeleton items={5} />
              </PanelSkeleton>
            </div>
            <div className="col-span-12 lg:col-span-6">
              <PanelSkeleton>
                <BarListSkeleton items={5} />
              </PanelSkeleton>
            </div>
            {/* Two column lists skeleton (By Category + By Author) */}
            {[1, 2].map((i) => (
              <div key={i} className="col-span-12 lg:col-span-6">
                <PanelSkeleton>
                  <BarListSkeleton items={5} />
                </PanelSkeleton>
              </div>
            ))}
          </div>
        ) : (
          <div className="grid gap-3 grid-cols-12">
            {isWidgetVisible('metrics') && overviewMetrics.length > 0 && (
              <div className="col-span-12">
                <Panel>
                  <Metrics metrics={overviewMetrics} />
                </Panel>
              </div>
            )}

            {isWidgetVisible('top-pages') && (
              <div className="col-span-12 lg:col-span-6">
                <HorizontalBarList
                  title={__('Top Pages', 'wp-statistics')}
                  showComparison={isCompareEnabled}
                  columnHeaders={{
                    left: __('Page', 'wp-statistics'),
                    right: __('Views', 'wp-statistics'),
                  }}
                  items={transformToBarList(topPagesData, {
                    label: (item) => item.page_title || item.page_uri || __('Unknown', 'wp-statistics'),
                    value: (item) => Number(item.views) || 0,
                    previousValue: (item) => Number(item.previous?.views) || 0,
                    total: getTotalFromResponse(topPagesTotals, 'views') || 1,
                    isCompareEnabled,
                    comparisonDateLabel,
                    linkTo: (item) => getAnalyticsRoute(item.page_type, item.page_wp_id)?.to,
                    linkParams: (item) => getAnalyticsRoute(item.page_type, item.page_wp_id)?.params,
                  })}
                  link={{
                    title: __('See all', 'wp-statistics'),
                    action: () => navigate({ to: '/top-pages' }),
                  }}
                />
              </div>
            )}

            {isWidgetVisible('404-pages') && (
              <div className="col-span-12 lg:col-span-6">
                <HorizontalBarList
                  title={__('404 Pages', 'wp-statistics')}
                  showComparison={isCompareEnabled}
                  columnHeaders={{
                    left: __('Page', 'wp-statistics'),
                    right: __('Views', 'wp-statistics'),
                  }}
                  items={transformToBarList(pages404Data, {
                    label: (item) => item.page_uri || __('Unknown', 'wp-statistics'),
                    value: (item) => Number(item.views) || 0,
                    previousValue: (item) => Number(item.previous?.views) || 0,
                    total: getTotalFromResponse(pages404Totals, 'views') || 1,
                    isCompareEnabled,
                    comparisonDateLabel,
                  })}
                  link={{
                    title: __('See all', 'wp-statistics'),
                    action: () => navigate({ to: '/404-pages' }),
                  }}
                />
              </div>
            )}

            {isWidgetVisible('by-category') && (
              <div className="col-span-12 lg:col-span-6">
                <HorizontalBarList
                  title={__('Category Pages', 'wp-statistics')}
                  showComparison={isCompareEnabled}
                  columnHeaders={{
                    left: __('Category', 'wp-statistics'),
                    right: __('Views', 'wp-statistics'),
                  }}
                  items={transformToBarList(byCategoryData, {
                    label: (item) => item.page_title || __('Unknown', 'wp-statistics'),
                    value: (item) => Number(item.views) || 0,
                    previousValue: (item) => Number(item.previous?.views) || 0,
                    total: getTotalFromResponse(byCategoryTotals, 'views') || 1,
                    isCompareEnabled,
                    comparisonDateLabel,
                  })}
                  link={{
                    title: __('See all', 'wp-statistics'),
                    action: () => navigate({ to: '/category-pages' }),
                  }}
                />
              </div>
            )}

            {isWidgetVisible('by-author') && (
              <div className="col-span-12 lg:col-span-6">
                <HorizontalBarList
                  title={__('Author Pages', 'wp-statistics')}
                  showComparison={isCompareEnabled}
                  columnHeaders={{
                    left: __('Author', 'wp-statistics'),
                    right: __('Views', 'wp-statistics'),
                  }}
                  items={transformToBarList(byAuthorData, {
                    label: (item) => item.author_name || __('Unknown', 'wp-statistics'),
                    value: (item) => Number(item.views) || 0,
                    previousValue: (item) => Number(item.previous?.views) || 0,
                    total: getTotalFromResponse(byAuthorTotals, 'views') || 1,
                    isCompareEnabled,
                    comparisonDateLabel,
                  })}
                  link={{
                    title: __('See all', 'wp-statistics'),
                    action: () => navigate({ to: '/author-pages' }),
                  }}
                />
              </div>
            )}

            {/* Render registered widgets from premium plugins */}
            {registeredWidgets.map((widget) => {
              // Check widget visibility (if configured in options)
              if (!isWidgetVisible(widget.id)) return null

              // Get data from batch response using the widget's queryId
              const widgetData = batchResponse?.data?.items?.[widget.queryId as keyof typeof batchResponse.data.items]
              const data = (widgetData as { data?: { rows?: unknown[]; totals?: Record<string, unknown> } })?.data?.rows || []
              const totals = (widgetData as { data?: { rows?: unknown[]; totals?: Record<string, unknown> } })?.data?.totals || {}

              // Create props for the widget render function
              const widgetProps: WidgetRenderProps = {
                data,
                totals,
                isCompareEnabled,
                comparisonDateLabel,
                navigate,
                getTotalFromResponse,
              }

              return (
                <div key={widget.id} className="col-span-12 lg:col-span-6">
                  {widget.render(widgetProps)}
                </div>
              )
            })}
          </div>
        )}
      </div>
    </div>
  )
}
