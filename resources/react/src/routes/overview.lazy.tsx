 

import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute, useNavigate } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { useMemo, useState } from 'react'

import { HorizontalBarList } from '@/components/custom/horizontal-bar-list'
import { LineChart } from '@/components/custom/line-chart'
import { type MetricItem, Metrics } from '@/components/custom/metrics'
import {
  OptionsDrawerTrigger,
  type OverviewOptionsConfig,
  OverviewOptionsDrawer,
  OverviewOptionsProvider,
  useOverviewOptions,
} from '@/components/custom/options-drawer'
import { WidgetCatalog } from '@/components/custom/widget-catalog'
import { WidgetContextMenu } from '@/components/custom/widget-context-menu'
import { WidgetPresetSelector } from '@/components/custom/widget-preset-selector'
import { EmptyState } from '@/components/ui/empty-state'
import { NoticeContainer } from '@/components/ui/notice-container'
import { Panel } from '@/components/ui/panel'
import { pickMetrics } from '@/constants/metric-definitions'
import { type WidgetConfig, type WidgetSize } from '@/contexts/page-options-context'
import { useChartData } from '@/hooks/use-chart-data'
import { useComparisonDateLabel } from '@/hooks/use-comparison-date-label'
import { usePageOptions } from '@/hooks/use-page-options'
import { usePercentageCalc } from '@/hooks/use-percentage-calc'
import { useWidgetDateRange } from '@/hooks/use-widget-date-range'
import { transformToBarList } from '@/lib/bar-list-helpers'
import { getAnalyticsRoute } from '@/lib/url-utils'
import { formatCompactNumber, formatDecimal, formatDuration, getTotalValue } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'
import type { WidgetQueryParams } from '@/services/overview/get-overview-widgets'
import {
  getOverviewMetricsQueryOptions,
  getOverviewTopBrowsersQueryOptions,
  getOverviewTopCitiesQueryOptions,
  getOverviewTopCountriesQueryOptions,
  getOverviewTopDeviceCategoriesQueryOptions,
  getOverviewTopOSQueryOptions,
  getOverviewTopPagesQueryOptions,
  getOverviewTopReferrersQueryOptions,
  getOverviewTopSearchEnginesQueryOptions,
  getOverviewTopSocialMediaQueryOptions,
  getOverviewTopVisitorsQueryOptions,
  getOverviewTrafficTrendsQueryOptions,
} from '@/services/overview/get-overview-widgets'

export const Route = createLazyFileRoute('/overview')({
  component: OverviewDashboard,
})

// === Widget Configurations ===

const WIDGET_CONFIGS: WidgetConfig[] = [
  { id: 'metrics-overview', label: __('Metrics Overview', 'wp-statistics'), defaultVisible: true, defaultSize: 12, allowedSizes: [4, 6, 8, 12] },
  { id: 'traffic-trends', label: __('Traffic Trends', 'wp-statistics'), defaultVisible: true, defaultSize: 12, allowedSizes: [6, 8, 12] },
  { id: 'top-pages', label: __('Top Pages', 'wp-statistics'), defaultVisible: true, defaultSize: 6, allowedSizes: [4, 6, 8, 12] },
  { id: 'top-referrers', label: __('Top Referrers', 'wp-statistics'), defaultVisible: true, defaultSize: 6, allowedSizes: [4, 6, 8, 12] },
  { id: 'top-countries', label: __('Top Countries', 'wp-statistics'), defaultVisible: true, defaultSize: 6, allowedSizes: [4, 6, 8, 12] },
  { id: 'top-search-engines', label: __('Top Search Engines', 'wp-statistics'), defaultVisible: true, defaultSize: 6, allowedSizes: [4, 6, 8, 12] },
  { id: 'top-browsers', label: __('Top Browsers', 'wp-statistics'), defaultVisible: false, defaultSize: 6, allowedSizes: [4, 6, 8, 12] },
  { id: 'top-visitors', label: __('Top Visitors', 'wp-statistics'), defaultVisible: false, defaultSize: 6, allowedSizes: [4, 6, 8, 12] },
  { id: 'top-social-media', label: __('Top Social Media', 'wp-statistics'), defaultVisible: false, defaultSize: 6, allowedSizes: [4, 6, 8, 12] },
  { id: 'top-cities', label: __('Top Cities', 'wp-statistics'), defaultVisible: false, defaultSize: 6, allowedSizes: [4, 6, 8, 12] },
  { id: 'top-os', label: __('Top Operating Systems', 'wp-statistics'), defaultVisible: false, defaultSize: 6, allowedSizes: [4, 6, 8, 12] },
  { id: 'top-device-categories', label: __('Top Device Categories', 'wp-statistics'), defaultVisible: false, defaultSize: 6, allowedSizes: [4, 6, 8, 12] },
]

const WIDGET_CATEGORIES = [
  {
    label: __('Visitor Insights', 'wp-statistics'),
    widgets: ['metrics-overview', 'traffic-trends', 'top-visitors'],
  },
  {
    label: __('Content', 'wp-statistics'),
    widgets: ['top-pages'],
  },
  {
    label: __('Referrals', 'wp-statistics'),
    widgets: ['top-referrers', 'top-search-engines', 'top-social-media'],
  },
  {
    label: __('Geographic', 'wp-statistics'),
    widgets: ['top-countries', 'top-cities'],
  },
  {
    label: __('Devices', 'wp-statistics'),
    widgets: ['top-browsers', 'top-os', 'top-device-categories'],
  },
]

const METRIC_CONFIGS = pickMetrics('visitors', 'views', 'sessionDuration', 'viewsPerSession', 'bounceRate', 'onlineVisitors', 'searches')

const OPTIONS_CONFIG: OverviewOptionsConfig = {
  pageId: 'overview',
  filterGroup: 'overview',
  widgetConfigs: WIDGET_CONFIGS,
  metricConfigs: METRIC_CONFIGS,
  hideFilters: true,
  hideDateRange: true,
}

// === Size mapping ===

function sizeToColSpan(size: WidgetSize): string {
  switch (size) {
    case 4: return 'col-span-12 md:col-span-4'
    case 6: return 'col-span-12 md:col-span-6'
    case 8: return 'col-span-12 md:col-span-8'
    case 12: return 'col-span-12'
    default: return 'col-span-12'
  }
}

// === Widget Components ===

function MetricsOverviewWidget({ widgetId }: { widgetId: string }) {
  const widgetRange = useWidgetDateRange(widgetId)
  const { isMetricVisible } = usePageOptions()
  const calcPercentage = usePercentageCalc()
  const { label: comparisonDateLabel } = useComparisonDateLabel()
  const isCompareEnabled = !!(widgetRange.compareDateFrom && widgetRange.compareDateTo)

  const { data: response } = useQuery({
    ...getOverviewMetricsQueryOptions(widgetRange),
    placeholderData: keepPreviousData,
  })

  const metrics = useMemo((): MetricItem[] => {
    const totals = response?.data?.items?.metrics?.totals
    if (!totals) return []

    const visitors = getTotalValue(totals.visitors?.current ?? totals.visitors) || 0
    const views = getTotalValue(totals.views?.current ?? totals.views) || 0
    const avgSessionDuration = getTotalValue(totals.avg_session_duration?.current ?? totals.avg_session_duration) || 0
    const pagesPerSession = getTotalValue(totals.pages_per_session?.current ?? totals.pages_per_session) || 0
    const bounceRate = getTotalValue(totals.bounce_rate?.current ?? totals.bounce_rate) || 0
    const onlineVisitors = getTotalValue(totals.online_visitors?.current ?? totals.online_visitors) || 0
    const searches = getTotalValue(totals.searches?.current ?? totals.searches) || 0

    const prevVisitors = getTotalValue(totals.visitors?.previous) || 0
    const prevViews = getTotalValue(totals.views?.previous) || 0
    const prevAvgSessionDuration = getTotalValue(totals.avg_session_duration?.previous) || 0
    const prevPagesPerSession = getTotalValue(totals.pages_per_session?.previous) || 0
    const prevBounceRate = getTotalValue(totals.bounce_rate?.previous) || 0
    const prevSearches = getTotalValue(totals.searches?.previous) || 0

    const allMetrics: MetricItem[] = [
      {
        id: 'visitors',
        label: __('Visitors', 'wp-statistics'),
        value: formatCompactNumber(visitors),
        ...(isCompareEnabled ? { ...calcPercentage(visitors, prevVisitors), comparisonDateLabel, previousValue: formatCompactNumber(prevVisitors) } : {}),
      },
      {
        id: 'views',
        label: __('Views', 'wp-statistics'),
        value: formatCompactNumber(views),
        ...(isCompareEnabled ? { ...calcPercentage(views, prevViews), comparisonDateLabel, previousValue: formatCompactNumber(prevViews) } : {}),
      },
      {
        id: 'session-duration',
        label: __('Session Duration', 'wp-statistics'),
        value: formatDuration(avgSessionDuration),
        ...(isCompareEnabled ? { ...calcPercentage(avgSessionDuration, prevAvgSessionDuration), comparisonDateLabel, previousValue: formatDuration(prevAvgSessionDuration) } : {}),
      },
      {
        id: 'views-per-session',
        label: __('Views/Session', 'wp-statistics'),
        value: formatDecimal(pagesPerSession),
        ...(isCompareEnabled ? { ...calcPercentage(pagesPerSession, prevPagesPerSession), comparisonDateLabel, previousValue: formatDecimal(prevPagesPerSession) } : {}),
      },
      {
        id: 'bounce-rate',
        label: __('Bounce Rate', 'wp-statistics'),
        value: `${formatDecimal(bounceRate)}%`,
        ...(isCompareEnabled ? { ...calcPercentage(bounceRate, prevBounceRate), comparisonDateLabel, previousValue: `${formatDecimal(prevBounceRate)}%` } : {}),
      },
      {
        id: 'online-visitors',
        label: __('Online Visitors', 'wp-statistics'),
        value: formatCompactNumber(onlineVisitors),
      },
      {
        id: 'searches',
        label: __('Searches', 'wp-statistics'),
        value: formatCompactNumber(searches),
        ...(isCompareEnabled ? { ...calcPercentage(searches, prevSearches), comparisonDateLabel, previousValue: formatCompactNumber(prevSearches) } : {}),
      },
    ]

    return allMetrics.filter((metric) => isMetricVisible(metric.id!))
  }, [response, isCompareEnabled, comparisonDateLabel, isMetricVisible])

  return (
    <Panel className="h-full">
      <div className="flex items-center justify-between px-4 pt-3 pb-1">
        <WidgetPresetSelector widgetId={widgetId} />
        <WidgetContextMenu widgetId={widgetId} allowedSizes={[4, 6, 8, 12]} />
      </div>
      {metrics.length > 0 ? (
        <Metrics metrics={metrics} columns="auto" />
      ) : (
        <EmptyState title={__('No data available', 'wp-statistics')} className="py-6" />
      )}
    </Panel>
  )
}

function TrafficTrendsWidget({ widgetId }: { widgetId: string }) {
  const widgetRange = useWidgetDateRange(widgetId)
  const [timeframe, setTimeframe] = useState<'daily' | 'weekly' | 'monthly'>('daily')

  const { data: response, isFetching, isLoading } = useQuery({
    ...getOverviewTrafficTrendsQueryOptions({ ...widgetRange, timeframe }),
    placeholderData: keepPreviousData,
  })

  const chartResponse = response?.data?.items?.traffic_trends
  const { data: chartData, metrics: chartMetrics } = useChartData(chartResponse, {
    metrics: [
      { key: 'visitors', label: __('Visitors', 'wp-statistics'), color: 'var(--chart-1)' },
      { key: 'views', label: __('Views', 'wp-statistics'), color: 'var(--chart-2)' },
    ],
    showPreviousValues: widgetRange.isCompareEnabled,
    preserveNull: true,
  })

  return (
    <LineChart
      title={__('Traffic Trends', 'wp-statistics')}
      data={chartData}
      metrics={chartMetrics}
      showPreviousPeriod={widgetRange.isCompareEnabled}
      timeframe={timeframe}
      onTimeframeChange={setTimeframe}
      loading={isFetching && !isLoading}
      dateTo={widgetRange.dateTo}
      compareDateTo={widgetRange.compareDateTo}
      headerActions={<WidgetPresetSelector widgetId={widgetId} />}
      headerRight={<WidgetContextMenu widgetId={widgetId} allowedSizes={[6, 8, 12]} />}
    />
  )
}

function BarListWidget({
  widgetId,
  title,
  queryOptionsFn,
  queryItemKey,
  columnLeft,
  columnRight,
  labelAccessor,
  iconAccessor,
  linkTo,
  linkParams,
  allowedSizes,
  seeAllRoute,
}: {
  widgetId: string
  title: string
  // eslint-disable-next-line @typescript-eslint/no-explicit-any -- analytics API returns dynamic response shapes per widget
  queryOptionsFn: (params: WidgetQueryParams) => any
  queryItemKey: string
  columnLeft: string
  columnRight: string
  // eslint-disable-next-line @typescript-eslint/no-explicit-any -- row shape varies per widget
  labelAccessor: (item: any) => string
  iconAccessor?: (item: any) => React.ReactNode
  linkTo?: (item: any) => string
  linkParams?: (item: any) => Record<string, string>
  allowedSizes?: WidgetSize[]
  seeAllRoute?: string
}) {
  const navigate = useNavigate()
  const widgetRange = useWidgetDateRange(widgetId)

  const { data: response, isFetching } = useQuery({
    ...queryOptionsFn(widgetRange),
    placeholderData: keepPreviousData,
  })

  const rows = response?.data?.items?.[queryItemKey]?.data?.rows || []
  const totals = response?.data?.items?.[queryItemKey]?.data?.totals
  const totalVisitors = Number(totals?.visitors?.current ?? totals?.visitors) || 1

  const items = transformToBarList(rows, {
    label: labelAccessor,
    value: (item: any) => Number(item.visitors) || 0,
    previousValue: (item: any) => Number(item.previous?.visitors) || 0,
    total: totalVisitors,
    icon: iconAccessor,
    isCompareEnabled: widgetRange.isCompareEnabled,
    linkTo,
    linkParams,
  })

  return (
    <HorizontalBarList
      title={title}
      showComparison={widgetRange.isCompareEnabled}
      columnHeaders={{ left: columnLeft, right: columnRight }}
      items={items}
      loading={isFetching}
      footerLeft={<WidgetPresetSelector widgetId={widgetId} />}
      headerRight={<WidgetContextMenu widgetId={widgetId} allowedSizes={allowedSizes} />}
      link={seeAllRoute ? { action: () => navigate({ to: seeAllRoute }) } : undefined}
    />
  )
}

function TopVisitorsWidget({ widgetId }: { widgetId: string }) {
  const navigate = useNavigate()
  const widgetRange = useWidgetDateRange(widgetId)
  const pluginUrl = WordPress.getInstance().getPluginUrl()

  const { data: response, isFetching } = useQuery({
    ...getOverviewTopVisitorsQueryOptions(widgetRange),
    placeholderData: keepPreviousData,
  })

  const rows = (response?.data?.items?.top_visitors?.data?.rows || []) as any[]

  const items = transformToBarList(rows, {
    label: (item) => item.user_login || item.ip_address || item.visitor_hash?.substring(0, 8) || __('Anonymous', 'wp-statistics'),
    value: (item) => Number(item.total_views) || 0,
    previousValue: (item) => Number(item.previous?.total_views) || 0,
    total: rows.reduce((sum, r) => sum + (Number(r.total_views) || 0), 0) || 1,
    icon: (item) =>
      item.country_code ? (
        <img
          src={`${pluginUrl}public/images/flags/${item.country_code.toLowerCase()}.svg`}
          alt={item.country_name || ''}
          className="w-4 h-3"
        />
      ) : undefined,
    isCompareEnabled: widgetRange.isCompareEnabled,
  })

  return (
    <HorizontalBarList
      title={__('Top Visitors', 'wp-statistics')}
      showComparison={widgetRange.isCompareEnabled}
      columnHeaders={{ left: __('Visitor', 'wp-statistics'), right: __('Views', 'wp-statistics') }}
      items={items}
      loading={isFetching}
      footerLeft={<WidgetPresetSelector widgetId={widgetId} />}
      headerRight={<WidgetContextMenu widgetId={widgetId} allowedSizes={[4, 6, 8, 12]} />}
      link={{ action: () => navigate({ to: '/top-visitors' }) }}
    />
  )
}

// === Widget Renderer Map ===

function createWidgetRenderers(): Record<string, (widgetId: string) => React.ReactNode> {
  const pluginUrl = WordPress.getInstance().getPluginUrl()

  return {
    'metrics-overview': (id) => <MetricsOverviewWidget widgetId={id} />,
    'traffic-trends': (id) => <TrafficTrendsWidget widgetId={id} />,
    'top-pages': (id) => (
      <BarListWidget
        widgetId={id}
        title={__('Top Pages', 'wp-statistics')}
        queryOptionsFn={getOverviewTopPagesQueryOptions}
        queryItemKey="top_pages"
        columnLeft={__('Page', 'wp-statistics')}
        columnRight={__('Views', 'wp-statistics')}
        labelAccessor={(item) => item.page_title || item.page_uri || '/'}
        linkTo={(item) => getAnalyticsRoute(item.page_type, item.page_wp_id, undefined, item.resource_id)?.to}
        linkParams={(item) => getAnalyticsRoute(item.page_type, item.page_wp_id, undefined, item.resource_id)?.params}
        seeAllRoute="/top-pages"
      />
    ),
    'top-referrers': (id) => (
      <BarListWidget
        widgetId={id}
        title={__('Top Referrers', 'wp-statistics')}
        queryOptionsFn={getOverviewTopReferrersQueryOptions}
        queryItemKey="top_referrers"
        columnLeft={__('Referrer', 'wp-statistics')}
        columnRight={__('Visitors', 'wp-statistics')}
        labelAccessor={(item) => item.referrer_name || item.referrer_domain || __('Direct', 'wp-statistics')}
        seeAllRoute="/referrers"
      />
    ),
    'top-countries': (id) => (
      <BarListWidget
        widgetId={id}
        title={__('Top Countries', 'wp-statistics')}
        queryOptionsFn={getOverviewTopCountriesQueryOptions}
        queryItemKey="top_countries"
        columnLeft={__('Country', 'wp-statistics')}
        columnRight={__('Visitors', 'wp-statistics')}
        labelAccessor={(item) => item.country_name || __('Unknown', 'wp-statistics')}
        iconAccessor={(item) => (
          <img
            src={`${pluginUrl}public/images/flags/${item.country_code?.toLowerCase() || '000'}.svg`}
            alt={item.country_name || ''}
            className="w-4 h-3"
          />
        )}
        linkTo={() => '/country/$countryCode'}
        linkParams={(item) => ({ countryCode: item.country_code?.toLowerCase() || '000' })}
        seeAllRoute="/countries"
      />
    ),
    'top-browsers': (id) => (
      <BarListWidget
        widgetId={id}
        title={__('Top Browsers', 'wp-statistics')}
        queryOptionsFn={getOverviewTopBrowsersQueryOptions}
        queryItemKey="top_browsers"
        columnLeft={__('Browser', 'wp-statistics')}
        columnRight={__('Visitors', 'wp-statistics')}
        labelAccessor={(item) => item.browser_name || __('Unknown', 'wp-statistics')}
        iconAccessor={(item) => (
          <img
            src={`${pluginUrl}public/images/browser/${(item.browser_name || 'unknown').toLowerCase().replace(/\s+/g, '_')}.svg`}
            alt={item.browser_name || ''}
            className="w-4 h-3"
            onError={(e) => { (e.target as HTMLImageElement).style.display = 'none' }}
          />
        )}
        seeAllRoute="/browsers"
      />
    ),
    'top-visitors': (id) => <TopVisitorsWidget widgetId={id} />,
    'top-search-engines': (id) => (
      <BarListWidget
        widgetId={id}
        title={__('Top Search Engines', 'wp-statistics')}
        queryOptionsFn={getOverviewTopSearchEnginesQueryOptions}
        queryItemKey="top_search_engines"
        columnLeft={__('Search Engine', 'wp-statistics')}
        columnRight={__('Visitors', 'wp-statistics')}
        labelAccessor={(item) => item.referrer_name || item.referrer_domain || __('Unknown', 'wp-statistics')}
        seeAllRoute="/search-engines"
      />
    ),
    'top-social-media': (id) => (
      <BarListWidget
        widgetId={id}
        title={__('Top Social Media', 'wp-statistics')}
        queryOptionsFn={getOverviewTopSocialMediaQueryOptions}
        queryItemKey="top_social_media"
        columnLeft={__('Social Media', 'wp-statistics')}
        columnRight={__('Visitors', 'wp-statistics')}
        labelAccessor={(item) => item.referrer_name || item.referrer_domain || __('Unknown', 'wp-statistics')}
        seeAllRoute="/social-media"
      />
    ),
    'top-cities': (id) => (
      <BarListWidget
        widgetId={id}
        title={__('Top Cities', 'wp-statistics')}
        queryOptionsFn={getOverviewTopCitiesQueryOptions}
        queryItemKey="top_cities"
        columnLeft={__('City', 'wp-statistics')}
        columnRight={__('Visitors', 'wp-statistics')}
        labelAccessor={(item) => item.city_name || __('Unknown', 'wp-statistics')}
        iconAccessor={(item) => (
          <img
            src={`${pluginUrl}public/images/flags/${item.country_code?.toLowerCase() || '000'}.svg`}
            alt={item.country_name || ''}
            className="w-4 h-3"
          />
        )}
        seeAllRoute="/cities"
      />
    ),
    'top-os': (id) => (
      <BarListWidget
        widgetId={id}
        title={__('Top Operating Systems', 'wp-statistics')}
        queryOptionsFn={getOverviewTopOSQueryOptions}
        queryItemKey="top_os"
        columnLeft={__('Operating System', 'wp-statistics')}
        columnRight={__('Visitors', 'wp-statistics')}
        labelAccessor={(item) => item.os_name || __('Unknown', 'wp-statistics')}
        iconAccessor={(item) => (
          <img
            src={`${pluginUrl}public/images/operating-system/${(item.os_name || 'unknown').toLowerCase().replace(/\s+/g, '_')}.svg`}
            alt={item.os_name || ''}
            className="w-4 h-3"
            onError={(e) => { (e.target as HTMLImageElement).style.display = 'none' }}
          />
        )}
        seeAllRoute="/operating-systems"
      />
    ),
    'top-device-categories': (id) => (
      <BarListWidget
        widgetId={id}
        title={__('Top Device Categories', 'wp-statistics')}
        queryOptionsFn={getOverviewTopDeviceCategoriesQueryOptions}
        queryItemKey="top_device_categories"
        columnLeft={__('Device', 'wp-statistics')}
        columnRight={__('Visitors', 'wp-statistics')}
        labelAccessor={(item) => item.device_type_name || __('Unknown', 'wp-statistics')}
        iconAccessor={(item) => (
          <img
            src={`${pluginUrl}public/images/device/${(item.device_type_name || 'desktop').toLowerCase()}.svg`}
            alt={item.device_type_name || ''}
            className="w-4 h-3"
            onError={(e) => { (e.target as HTMLImageElement).style.display = 'none' }}
          />
        )}
        seeAllRoute="/device-categories"
      />
    ),
  }
}

// === Main Page ===

function OverviewDashboard() {
  return (
    <OverviewOptionsProvider config={OPTIONS_CONFIG}>
      <OverviewDashboardContent />
    </OverviewOptionsProvider>
  )
}

function OverviewDashboardContent() {
  const { getOrderedVisibleWidgets, getWidgetSize } = usePageOptions()
  const options = useOverviewOptions(OPTIONS_CONFIG)

  const visibleWidgets = getOrderedVisibleWidgets()
  const widgetRenderers = useMemo(() => createWidgetRenderers(), [])

  return (
    <div className="min-w-0">
      {/* Header */}
      <div className="px-4 py-3">
        <div className="flex items-center justify-between">
          <h1 className="text-2xl font-semibold text-neutral-800">
            {__('Overview', 'wp-statistics')}
          </h1>
          <div className="flex items-center gap-3">
            <WidgetCatalog categories={WIDGET_CATEGORIES} />
            <OptionsDrawerTrigger {...options.triggerProps} />
          </div>
        </div>
      </div>

      {/* Options Drawer */}
      <OverviewOptionsDrawer {...options} />

      {/* Widget Grid */}
      <div className="p-3">
        <NoticeContainer className="mb-2" currentRoute="overview" />

        <div className="grid gap-3 grid-cols-12">
          {visibleWidgets.map((widget) => {
            const size = getWidgetSize(widget.id)
            const renderer = widgetRenderers[widget.id]
            if (!renderer) return null

            return (
              <div key={widget.id} className={`group/widget ${sizeToColSpan(size)}`}>
                {renderer(widget.id)}
              </div>
            )
          })}
        </div>

        {/* Bottom add widget button */}
        {visibleWidgets.length < WIDGET_CONFIGS.length && (
          <div className="mt-4 flex justify-center">
            <WidgetCatalog categories={WIDGET_CATEGORIES} />
          </div>
        )}
      </div>
    </div>
  )
}
