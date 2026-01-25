import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'
import { __, sprintf } from '@wordpress/i18n'
import { useCallback, useEffect, useMemo, useRef, useState } from 'react'

import { DateRangePicker } from '@/components/custom/date-range-picker'
import { HorizontalBar } from '@/components/custom/horizontal-bar'
import { HorizontalBarList } from '@/components/custom/horizontal-bar-list'
import { LineChart } from '@/components/custom/line-chart'
import { type MetricItem, Metrics } from '@/components/custom/metrics'
import {
  type OverviewOptionsConfig,
  OptionsDrawerTrigger,
  OverviewOptionsDrawer,
  OverviewOptionsProvider,
  useOverviewOptions,
  type PageFilterConfig,
} from '@/components/custom/options-drawer'
import { TabbedPanel, type TabbedPanelTab } from '@/components/custom/tabbed-panel'
import { TaxonomySelect } from '@/components/custom/taxonomy-select'
import { EmptyState } from '@/components/ui/empty-state'
import { NoticeContainer } from '@/components/ui/notice-container'
import { Panel } from '@/components/ui/panel'
import { BarListSkeleton, ChartSkeleton, MetricsSkeleton, PanelSkeleton } from '@/components/ui/skeletons'
import { type MetricConfig, type WidgetConfig } from '@/contexts/page-options-context'
import { useChartData } from '@/hooks/use-chart-data'
import { useComparisonDateLabel } from '@/hooks/use-comparison-date-label'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { usePageOptions } from '@/hooks/use-page-options'
import { usePercentageCalc } from '@/hooks/use-percentage-calc'
import { transformToBarList } from '@/lib/bar-list-helpers'
import { getAnalyticsRoute } from '@/lib/url-utils'
import { formatCompactNumber, formatDecimal, formatDuration, getTotalValue } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'
import {
  getCategoriesOverviewQueryOptions,
  type AuthorRow,
  type BrowserRow,
  type ContentRow,
  type DeviceTypeRow,
  type OperatingSystemRow,
  type TermRow,
  type TopCountryRow,
  type TopReferrerRow,
} from '@/services/content-analytics/get-categories-overview'

// Widget configuration for Categories page
const WIDGET_CONFIGS: WidgetConfig[] = [
  { id: 'traffic-trends', label: __('Performance', 'wp-statistics'), defaultVisible: true },
  { id: 'top-terms', label: __('Top Terms', 'wp-statistics'), defaultVisible: true },
  { id: 'top-content', label: __('Top Content', 'wp-statistics'), defaultVisible: true },
  { id: 'top-authors', label: __('Top Authors', 'wp-statistics'), defaultVisible: true },
  { id: 'top-referrers', label: __('Top Referrers', 'wp-statistics'), defaultVisible: true },
  { id: 'top-search-engines', label: __('Top Search Engines', 'wp-statistics'), defaultVisible: true },
  { id: 'top-countries', label: __('Top Countries', 'wp-statistics'), defaultVisible: true },
  { id: 'top-browsers', label: __('Top Browsers', 'wp-statistics'), defaultVisible: true },
  { id: 'top-operating-systems', label: __('Top Operating Systems', 'wp-statistics'), defaultVisible: true },
  { id: 'top-device-categories', label: __('Top Device Categories', 'wp-statistics'), defaultVisible: true },
]

// Metric configuration for Categories page (5 metrics)
const METRIC_CONFIGS: MetricConfig[] = [
  { id: 'contents', label: __('Contents', 'wp-statistics'), defaultVisible: true },
  { id: 'visitors', label: __('Visitors', 'wp-statistics'), defaultVisible: true },
  { id: 'views', label: __('Views', 'wp-statistics'), defaultVisible: true },
  { id: 'bounce-rate', label: __('Bounce Rate', 'wp-statistics'), defaultVisible: true },
  { id: 'time-on-page', label: __('Avg. Time on Page', 'wp-statistics'), defaultVisible: true },
]

// Options configuration for this page - base config without pageFilters
// pageFilters are added dynamically in the component since they need state
const BASE_OPTIONS_CONFIG: Omit<OverviewOptionsConfig, 'pageFilters'> = {
  pageId: 'categories-overview',
  filterGroup: 'categories',
  widgetConfigs: WIDGET_CONFIGS,
  metricConfigs: METRIC_CONFIGS,
  hideFilters: true,
}

export const Route = createLazyFileRoute('/(content-analytics)/categories')({
  component: RouteComponent,
  errorComponent: ({ error }) => (
    <div className="p-6 text-center">
      <h2 className="text-xl font-semibold text-destructive mb-2">{__('Error Loading Page', 'wp-statistics')}</h2>
      <p className="text-muted-foreground">{error.message}</p>
    </div>
  ),
})

function RouteComponent() {
  return (
    <OverviewOptionsProvider config={BASE_OPTIONS_CONFIG}>
      <CategoriesOverviewContent />
    </OverviewOptionsProvider>
  )
}

/**
 * Categories Overview View - Main categories analytics page
 */
function CategoriesOverviewContent() {
  const {
    dateFrom,
    dateTo,
    compareDateFrom,
    compareDateTo,
    period,
    handleDateRangeUpdate,
    isInitialized,
    apiDateParams,
    isCompareEnabled,
  } = useGlobalFilters()

  // Page options for metric and widget visibility
  const { isMetricVisible, isWidgetVisible } = usePageOptions()

  const wp = WordPress.getInstance()
  const isPremium = wp.getIsPremium()
  const pluginUrl = wp.getPluginUrl()

  // Get taxonomies and filter based on premium status
  const availableTaxonomies = useMemo(() => {
    const allTaxonomies = wp.getTaxonomies()
    if (isPremium) {
      return allTaxonomies // All including custom
    }
    // Free: Only category and post_tag
    return allTaxonomies.filter((t) => t.value === 'category' || t.value === 'post_tag')
  }, [wp, isPremium])

  // Taxonomy selector state
  const [selectedTaxonomy, setSelectedTaxonomy] = useState<string>('category')

  // Get selected taxonomy label for display
  const selectedTaxonomyLabel = useMemo(() => {
    const taxonomy = availableTaxonomies.find((t) => t.value === selectedTaxonomy)
    return taxonomy?.label || __('Categories', 'wp-statistics')
  }, [availableTaxonomies, selectedTaxonomy])

  // Chart timeframe state
  const [timeframe, setTimeframe] = useState<'daily' | 'weekly' | 'monthly'>('daily')

  // Track if only timeframe changed (for loading behavior)
  const [isTimeframeOnlyChange, setIsTimeframeOnlyChange] = useState(false)
  const prevTaxonomyRef = useRef<string>(selectedTaxonomy)
  const prevDateFromRef = useRef<Date | undefined>(dateFrom)
  const prevDateToRef = useRef<Date | undefined>(dateTo)

  // Detect what changed when data updates
  useEffect(() => {
    const taxonomyChanged = selectedTaxonomy !== prevTaxonomyRef.current
    const dateRangeChanged = dateFrom !== prevDateFromRef.current || dateTo !== prevDateToRef.current

    // If taxonomy or dates changed, it's NOT a timeframe-only change
    if (taxonomyChanged || dateRangeChanged) {
      setIsTimeframeOnlyChange(false)
    }

    // Update refs
    prevTaxonomyRef.current = selectedTaxonomy
    prevDateFromRef.current = dateFrom
    prevDateToRef.current = dateTo
  }, [selectedTaxonomy, dateFrom, dateTo])

  // Custom timeframe setter that tracks the change type
  const handleTimeframeChange = useCallback((newTimeframe: 'daily' | 'weekly' | 'monthly') => {
    setIsTimeframeOnlyChange(true)
    setTimeframe(newTimeframe)
  }, [])

  // Handle taxonomy change
  const handleTaxonomyChange = useCallback((value: string) => {
    setSelectedTaxonomy(value)
  }, [])

  // Page filters config for Options drawer
  const pageFilters = useMemo<PageFilterConfig[]>(() => {
    return [
      {
        id: 'taxonomy',
        label: __('Taxonomy Type', 'wp-statistics'),
        value: selectedTaxonomy,
        options: availableTaxonomies,
        onChange: handleTaxonomyChange,
      },
    ]
  }, [selectedTaxonomy, availableTaxonomies, handleTaxonomyChange])

  // Build full options config with pageFilters
  const optionsConfig = useMemo<OverviewOptionsConfig>(() => ({
    ...BASE_OPTIONS_CONFIG,
    pageFilters,
  }), [pageFilters])

  // Options drawer (uses reusable components)
  const options = useOverviewOptions(optionsConfig)

  // Batch query for all overview data
  const {
    data: batchResponse,
    isLoading,
    isFetching,
  } = useQuery({
    ...getCategoriesOverviewQueryOptions({
      dateFrom: apiDateParams.date_from,
      dateTo: apiDateParams.date_to,
      compareDateFrom: apiDateParams.previous_date_from,
      compareDateTo: apiDateParams.previous_date_to,
      taxonomy: selectedTaxonomy,
      timeframe,
    }),
    retry: false,
    placeholderData: keepPreviousData,
    enabled: isInitialized,
  })

  // Only show skeleton on initial load (no data yet), not on refetches
  const showSkeleton = isLoading && !batchResponse
  // Show full page loading when taxonomy/dates change (not timeframe-only)
  const showFullPageLoading = isFetching && !isLoading && !isTimeframeOnlyChange
  // Show loading indicator on chart only when timeframe changes
  const isChartRefetching = isFetching && !isLoading && isTimeframeOnlyChange

  // Extract data from batch response
  const metricsResponse = batchResponse?.data?.items?.category_metrics
  const trafficTrendsResponse = batchResponse?.data?.items?.traffic_trends
  const topTermsResponse = batchResponse?.data?.items?.top_terms
  const topContentResponse = batchResponse?.data?.items?.top_content
  const topAuthorsResponse = batchResponse?.data?.items?.top_authors
  const topReferrersResponse = batchResponse?.data?.items?.top_referrers
  const topSearchEnginesResponse = batchResponse?.data?.items?.top_search_engines
  const topCountriesResponse = batchResponse?.data?.items?.top_countries
  const topBrowsersResponse = batchResponse?.data?.items?.top_browsers
  const topOperatingSystemsResponse = batchResponse?.data?.items?.top_operating_systems
  const topDeviceCategoriesResponse = batchResponse?.data?.items?.top_device_categories

  // Transform chart data using shared hook
  const { data: chartData, metrics: chartMetrics } = useChartData(trafficTrendsResponse, {
    metrics: [
      { key: 'visitors', label: __('Visitors', 'wp-statistics'), color: 'var(--chart-1)' },
      { key: 'views', label: __('Views', 'wp-statistics'), color: 'var(--chart-2)' },
      { key: 'published_content', label: __('Published Content', 'wp-statistics'), color: 'var(--chart-3)', type: 'bar' },
    ],
    showPreviousValues: isCompareEnabled,
    preserveNull: true,
  })

  const calcPercentage = usePercentageCalc()
  const { label: comparisonDateLabel } = useComparisonDateLabel()

  // Build metrics with visibility filtering
  const categoriesMetrics = useMemo(() => {
    const totals = metricsResponse?.totals
    if (!totals) return []

    const publishedContent = getTotalValue(totals.published_content)
    const visitors = getTotalValue(totals.visitors)
    const views = getTotalValue(totals.views)
    const bounceRate = getTotalValue(totals.bounce_rate)
    const avgTimeOnPage = getTotalValue(totals.avg_time_on_page)

    const prevPublishedContent = getTotalValue(totals.published_content?.previous)
    const prevVisitors = getTotalValue(totals.visitors?.previous)
    const prevViews = getTotalValue(totals.views?.previous)
    const prevBounceRate = getTotalValue(totals.bounce_rate?.previous)
    const prevAvgTimeOnPage = getTotalValue(totals.avg_time_on_page?.previous)

    // Build all metrics with IDs for filtering
    const allMetrics: (MetricItem & { id: string })[] = [
      {
        id: 'contents',
        label: __('Contents', 'wp-statistics'),
        value: formatCompactNumber(publishedContent),
        ...(isCompareEnabled
          ? {
              ...calcPercentage(publishedContent, prevPublishedContent),
              comparisonDateLabel,
              previousValue: formatCompactNumber(prevPublishedContent),
            }
          : {}),
      },
      {
        id: 'visitors',
        label: __('Visitors', 'wp-statistics'),
        value: formatCompactNumber(visitors),
        ...(isCompareEnabled
          ? {
              ...calcPercentage(visitors, prevVisitors),
              comparisonDateLabel,
              previousValue: formatCompactNumber(prevVisitors),
            }
          : {}),
      },
      {
        id: 'views',
        label: __('Views', 'wp-statistics'),
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
        id: 'time-on-page',
        label: __('Avg. Time on Page', 'wp-statistics'),
        value: formatDuration(avgTimeOnPage),
        ...(isCompareEnabled
          ? {
              ...calcPercentage(avgTimeOnPage, prevAvgTimeOnPage),
              comparisonDateLabel,
              previousValue: formatDuration(prevAvgTimeOnPage),
            }
          : {}),
      },
    ]

    // Filter metrics based on visibility
    return allMetrics.filter((metric) => isMetricVisible(metric.id))
  }, [metricsResponse, calcPercentage, isCompareEnabled, comparisonDateLabel, isMetricVisible])

  // Get term rows from response
  const termRows = useMemo(() => {
    return topTermsResponse?.data?.rows || []
  }, [topTermsResponse])

  // Sorted terms for each tab
  const sortedByViews = useMemo(() => {
    return [...termRows].sort((a, b) => Number(b.views) - Number(a.views)).slice(0, 5)
  }, [termRows])

  const sortedByContents = useMemo(() => {
    return [...termRows].sort((a, b) => Number(b.published_content) - Number(a.published_content)).slice(0, 5)
  }, [termRows])

  // Build tabs for top terms widget
  const topTermsTabs = useMemo((): TabbedPanelTab[] => {
    const renderTermItem = (
      term: TermRow,
      valueKey: 'views' | 'published_content',
      valueLabel: string,
      index: number
    ) => {
      const value = Number(term[valueKey]) || 0
      const formattedValue = formatCompactNumber(value)

      // Comparison data - only show for views, not published_content
      let comparison = null
      if (isCompareEnabled && valueKey === 'views') {
        const prevValue = Number(term.previous?.[valueKey]) || 0
        comparison = calcPercentage(Number(value), prevValue)
      }

      return (
        <HorizontalBar
          key={`${term.term_id}-${index}`}
          label={term.term_name || __('Unknown Term', 'wp-statistics')}
          value={`${formattedValue} ${valueLabel}`}
          percentage={comparison?.percentage}
          isNegative={comparison?.isNegative}
          tooltipSubtitle={
            isCompareEnabled && valueKey === 'views'
              ? `${__('Previous:', 'wp-statistics')} ${formatCompactNumber(Number(term.previous?.[valueKey]) || 0)}`
              : undefined
          }
          comparisonDateLabel={comparisonDateLabel}
          showComparison={isCompareEnabled && valueKey === 'views'}
          showBar={false}
          highlightFirst={false}
          linkTo="/category/$termId"
          linkParams={{ termId: String(term.term_id) }}
        />
      )
    }

    return [
      {
        id: 'views',
        label: __('Views', 'wp-statistics'),
        columnHeaders: {
          left: __('Term', 'wp-statistics'),
          right: __('Views', 'wp-statistics'),
        },
        content:
          sortedByViews.length > 0 ? (
            <div className="flex flex-col gap-3">
              {sortedByViews.map((term, i) => renderTermItem(term, 'views', __('views', 'wp-statistics'), i))}
            </div>
          ) : (
            <EmptyState title={__('No data available', 'wp-statistics')} className="py-6" />
          ),
      },
      {
        id: 'contents',
        label: __('Contents', 'wp-statistics'),
        columnHeaders: {
          left: __('Term', 'wp-statistics'),
          right: __('Contents', 'wp-statistics'),
        },
        content:
          sortedByContents.length > 0 ? (
            <div className="flex flex-col gap-3">
              {sortedByContents.map((term, i) =>
                renderTermItem(term, 'published_content', __('contents', 'wp-statistics'), i)
              )}
            </div>
          ) : (
            <EmptyState title={__('No data available', 'wp-statistics')} className="py-6" />
          ),
      },
    ]
  }, [sortedByViews, sortedByContents, isCompareEnabled, calcPercentage, comparisonDateLabel])

  // Get content rows from response
  const contentRows = useMemo(() => {
    return topContentResponse?.data?.rows || []
  }, [topContentResponse])

  // Transform top content data into tabbed panel format
  const topContentTabs = useMemo((): TabbedPanelTab[] => {
    // Most Popular: Sort by views desc
    const popularSorted = [...contentRows].sort((a, b) => Number(b.views) - Number(a.views)).slice(0, 5)

    // Most Commented: Filter items with comments > 0, then sort by comments desc
    const commentedSorted = [...contentRows]
      .filter((item) => Number(item.comments) > 0)
      .sort((a, b) => Number(b.comments) - Number(a.comments))
      .slice(0, 5)

    // Most Recent: Sort by published_date desc
    const recentSorted = [...contentRows]
      .filter((item) => item.published_date)
      .sort((a, b) => {
        const dateA = new Date(a.published_date || 0).getTime()
        const dateB = new Date(b.published_date || 0).getTime()
        return dateB - dateA
      })
      .slice(0, 5)

    const tabs: TabbedPanelTab[] = [
      {
        id: 'popular',
        label: __('Most Popular', 'wp-statistics'),
        columnHeaders: {
          left: __('Content', 'wp-statistics'),
          right: __('Views', 'wp-statistics'),
        },
        content:
          popularSorted.length > 0 ? (
            <div className="flex flex-col gap-3">
              {popularSorted.map((item, i) => {
                const views = Number(item.views) || 0
                const prevViews = Number(item.previous?.views) || 0
                const comparison = isCompareEnabled ? calcPercentage(views, prevViews) : null
                const route = getAnalyticsRoute(item.page_type, item.page_wp_id)

                return (
                  <HorizontalBar
                    key={`${item.page_uri}-${i}`}
                    label={item.page_title || item.page_uri || '/'}
                    value={`${formatCompactNumber(views)} ${__('views', 'wp-statistics')}`}
                    percentage={comparison?.percentage}
                    isNegative={comparison?.isNegative}
                    tooltipSubtitle={
                      isCompareEnabled ? `${__('Previous:', 'wp-statistics')} ${formatCompactNumber(prevViews)}` : undefined
                    }
                    comparisonDateLabel={comparisonDateLabel}
                    showComparison={isCompareEnabled}
                    showBar={false}
                    highlightFirst={false}
                    linkTo={route?.to}
                    linkParams={route?.params}
                  />
                )
              })}
            </div>
          ) : (
            <EmptyState title={__('No data available', 'wp-statistics')} className="py-6" />
          ),
      },
    ]

    // Only add "Most Commented" tab if there are items with comments
    if (commentedSorted.length > 0) {
      tabs.push({
        id: 'commented',
        label: __('Most Commented', 'wp-statistics'),
        columnHeaders: {
          left: __('Content', 'wp-statistics'),
          right: __('Comments', 'wp-statistics'),
        },
        content: (
          <div className="flex flex-col gap-3">
            {commentedSorted.map((item, i) => {
              const route = getAnalyticsRoute(item.page_type, item.page_wp_id)
              return (
                <HorizontalBar
                  key={`${item.page_uri}-${i}`}
                  label={item.page_title || item.page_uri || '/'}
                  value={`${formatCompactNumber(Number(item.comments))} ${__('comments', 'wp-statistics')}`}
                  showComparison={false}
                  showBar={false}
                  highlightFirst={false}
                  linkTo={route?.to}
                  linkParams={route?.params}
                />
              )
            })}
          </div>
        ),
      })
    }

    tabs.push({
      id: 'recent',
      label: __('Most Recent', 'wp-statistics'),
      columnHeaders: {
        left: __('Content', 'wp-statistics'),
        right: __('Views', 'wp-statistics'),
      },
      content:
        recentSorted.length > 0 ? (
          <div className="flex flex-col gap-3">
            {recentSorted.map((item, i) => {
              const route = getAnalyticsRoute(item.page_type, item.page_wp_id)
              return (
                <HorizontalBar
                  key={`${item.page_uri}-${i}`}
                  label={item.page_title || item.page_uri || '/'}
                  value={`${formatCompactNumber(Number(item.views))} ${__('views', 'wp-statistics')}`}
                  showComparison={false}
                  showBar={false}
                  highlightFirst={false}
                  linkTo={route?.to}
                  linkParams={route?.params}
                />
              )
            })}
          </div>
        ) : (
          <EmptyState title={__('No data available', 'wp-statistics')} className="py-6" />
        ),
    })

    return tabs
  }, [contentRows, isCompareEnabled, calcPercentage, comparisonDateLabel])

  // Get author rows from response
  const authorRows = useMemo(() => {
    return topAuthorsResponse?.data?.rows || []
  }, [topAuthorsResponse])

  // Sorted authors for each tab
  const authorsSortedByViews = useMemo(() => {
    return [...authorRows].sort((a, b) => Number(b.views) - Number(a.views)).slice(0, 5)
  }, [authorRows])

  const authorsSortedByPublishing = useMemo(() => {
    return [...authorRows].sort((a, b) => Number(b.published_content) - Number(a.published_content)).slice(0, 5)
  }, [authorRows])

  const authorsSortedByEngagement = useMemo(() => {
    return [...authorRows]
      .map((row) => ({
        ...row,
        views_per_content: Number(row.published_content) > 0 ? Number(row.views) / Number(row.published_content) : 0,
      }))
      .sort((a, b) => b.views_per_content - a.views_per_content)
      .slice(0, 5)
  }, [authorRows])

  // Build tabs for top authors widget
  const topAuthorsTabs = useMemo((): TabbedPanelTab[] => {
    const renderAuthorItem = (
      author: AuthorRow & { views_per_content?: number },
      valueKey: 'views' | 'published_content' | 'views_per_content',
      valueLabel: string,
      index: number
    ) => {
      const value = valueKey === 'views_per_content' ? (author[valueKey] ?? 0) : Number(author[valueKey]) || 0

      const formattedValue = valueKey === 'views_per_content' ? formatDecimal(value) : formatCompactNumber(value)

      // Comparison data for views and publishing tabs
      let comparison = null
      if (isCompareEnabled && (valueKey === 'views' || valueKey === 'published_content')) {
        const prevValue = Number(author.previous?.[valueKey]) || 0
        comparison = calcPercentage(Number(value), prevValue)
      }

      return (
        <HorizontalBar
          key={`${author.author_id}-${index}`}
          icon={
            author.author_avatar ? (
              <img src={author.author_avatar} alt={author.author_name || ''} className="h-6 w-6 rounded-full object-cover" />
            ) : (
              <div className="h-6 w-6 rounded-full bg-neutral-200" />
            )
          }
          label={author.author_name || __('Unknown Author', 'wp-statistics')}
          value={`${formattedValue} ${valueLabel}`}
          percentage={comparison?.percentage}
          isNegative={comparison?.isNegative}
          tooltipSubtitle={
            isCompareEnabled && (valueKey === 'views' || valueKey === 'published_content')
              ? `${__('Previous:', 'wp-statistics')} ${formatCompactNumber(Number(author.previous?.[valueKey]) || 0)}`
              : undefined
          }
          comparisonDateLabel={comparisonDateLabel}
          showComparison={isCompareEnabled && (valueKey === 'views' || valueKey === 'published_content')}
          showBar={false}
          highlightFirst={false}
          linkTo="/author/$authorId"
          linkParams={{ authorId: String(author.author_id) }}
        />
      )
    }

    return [
      {
        id: 'views',
        label: __('Views', 'wp-statistics'),
        columnHeaders: {
          left: __('Author', 'wp-statistics'),
          right: __('Views', 'wp-statistics'),
        },
        content:
          authorsSortedByViews.length > 0 ? (
            <div className="flex flex-col gap-3">
              {authorsSortedByViews.map((author, i) => renderAuthorItem(author, 'views', __('views', 'wp-statistics'), i))}
            </div>
          ) : (
            <EmptyState title={__('No data available', 'wp-statistics')} className="py-6" />
          ),
      },
      {
        id: 'publishing',
        label: __('Publishing', 'wp-statistics'),
        columnHeaders: {
          left: __('Author', 'wp-statistics'),
          right: __('Contents', 'wp-statistics'),
        },
        content:
          authorsSortedByPublishing.length > 0 ? (
            <div className="flex flex-col gap-3">
              {authorsSortedByPublishing.map((author, i) =>
                renderAuthorItem(author, 'published_content', __('contents', 'wp-statistics'), i)
              )}
            </div>
          ) : (
            <EmptyState title={__('No data available', 'wp-statistics')} className="py-6" />
          ),
      },
      {
        id: 'engagement',
        label: __('Engagement', 'wp-statistics'),
        columnHeaders: {
          left: __('Author', 'wp-statistics'),
          right: __('Views/Content', 'wp-statistics'),
        },
        content:
          authorsSortedByEngagement.length > 0 ? (
            <div className="flex flex-col gap-3">
              {authorsSortedByEngagement.map((author, i) =>
                renderAuthorItem(author, 'views_per_content', __('views/content', 'wp-statistics'), i)
              )}
            </div>
          ) : (
            <EmptyState title={__('No data available', 'wp-statistics')} className="py-6" />
          ),
      },
    ]
  }, [authorsSortedByViews, authorsSortedByPublishing, authorsSortedByEngagement, isCompareEnabled, calcPercentage, comparisonDateLabel])

  return (
    <div className="min-w-0">
      {/* Header */}
      <div className="flex items-center justify-between px-4 py-3 ">
        <h1 className="text-2xl font-semibold text-neutral-800">{selectedTaxonomyLabel}</h1>
        <div className="flex items-center gap-3">
          <div className="hidden lg:flex">
            <TaxonomySelect value={selectedTaxonomy} onValueChange={handleTaxonomyChange} />
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

      {/* Options Drawer */}
      <OverviewOptionsDrawer {...options} />

      <div className="p-3">
        <NoticeContainer className="mb-2" currentRoute="categories" />

        {showSkeleton || showFullPageLoading ? (
          <div className="grid gap-3 grid-cols-12">
            <div className="col-span-12">
              <PanelSkeleton showTitle={false}>
                <MetricsSkeleton count={8} columns={4} />
              </PanelSkeleton>
            </div>
            <div className="col-span-12">
              <ChartSkeleton />
            </div>
            <div className="col-span-12">
              <PanelSkeleton>
                <BarListSkeleton items={5} />
              </PanelSkeleton>
            </div>
            <div className="col-span-12">
              <PanelSkeleton>
                <BarListSkeleton items={5} />
              </PanelSkeleton>
            </div>
            <div className="col-span-12">
              <PanelSkeleton>
                <BarListSkeleton items={5} showIcon />
              </PanelSkeleton>
            </div>
            {/* New widget skeletons - 3 rows x 2 columns */}
            <div className="col-span-12 lg:col-span-6">
              <BarListSkeleton />
            </div>
            <div className="col-span-12 lg:col-span-6">
              <BarListSkeleton />
            </div>
            <div className="col-span-12 lg:col-span-6">
              <BarListSkeleton />
            </div>
            <div className="col-span-12 lg:col-span-6">
              <BarListSkeleton />
            </div>
            <div className="col-span-12 lg:col-span-6">
              <BarListSkeleton />
            </div>
            <div className="col-span-12 lg:col-span-6">
              <BarListSkeleton />
            </div>
          </div>
        ) : (
          <div className="grid gap-3 grid-cols-12">
            {/* Row 1: Categories Metrics */}
            {categoriesMetrics.length > 0 && (
              <div className="col-span-12">
                <Panel>
                  <Metrics metrics={categoriesMetrics} columns={4} />
                </Panel>
              </div>
            )}
            {/* Row 2: Performance Chart */}
            {isWidgetVisible('traffic-trends') && (
              <div className="col-span-12">
                <LineChart
                  title={__('Performance', 'wp-statistics')}
                  data={chartData}
                  metrics={chartMetrics}
                  showPreviousPeriod={isCompareEnabled}
                  timeframe={timeframe}
                  onTimeframeChange={handleTimeframeChange}
                  loading={isChartRefetching}
                  compareDateTo={apiDateParams.previous_date_to}
                  dateTo={apiDateParams.date_to}
                />
              </div>
            )}
            {/* Row 3: Top Terms Widget */}
            {isWidgetVisible('top-terms') && (
              <div className="col-span-12">
                <TabbedPanel title={__('Top Terms', 'wp-statistics')} tabs={topTermsTabs} defaultTab="views" />
              </div>
            )}
            {/* Row 4: Top Content Widget */}
            {isWidgetVisible('top-content') && (
              <div className="col-span-12">
                <TabbedPanel title={__('Top Content', 'wp-statistics')} tabs={topContentTabs} defaultTab="popular" />
              </div>
            )}
            {/* Row 5: Top Authors Widget */}
            {isWidgetVisible('top-authors') && (
              <div className="col-span-12">
                <TabbedPanel title={__('Top Authors', 'wp-statistics')} tabs={topAuthorsTabs} defaultTab="views" />
              </div>
            )}
            {/* Row 6: Top Referrers & Top Search Engines */}
            {isWidgetVisible('top-referrers') && (
              <div className="col-span-12 lg:col-span-6">
                <HorizontalBarList
                  title={__('Top Referrers', 'wp-statistics')}
                  showComparison={isCompareEnabled}
                  columnHeaders={{
                    left: __('Referrer', 'wp-statistics'),
                    right: __('Visitors', 'wp-statistics'),
                  }}
                  items={transformToBarList(topReferrersResponse?.data?.rows || [], {
                    label: (item) => item.referrer_name || item.referrer_domain || __('Direct', 'wp-statistics'),
                    value: (item) => Number(item.visitors) || 0,
                    previousValue: (item) => Number(item.previous?.visitors) || 0,
                    total: Number(topReferrersResponse?.data?.totals?.visitors?.current) || 1,
                    isCompareEnabled,
                    comparisonDateLabel,
                  })}
                />
              </div>
            )}
            {isWidgetVisible('top-search-engines') && (
              <div className="col-span-12 lg:col-span-6">
                <HorizontalBarList
                  title={__('Top Search Engines', 'wp-statistics')}
                  showComparison={isCompareEnabled}
                  columnHeaders={{
                    left: __('Search Engine', 'wp-statistics'),
                    right: __('Visitors', 'wp-statistics'),
                  }}
                  items={transformToBarList(topSearchEnginesResponse?.data?.rows || [], {
                    label: (item) => item.referrer_name || item.referrer_domain || __('Unknown', 'wp-statistics'),
                    value: (item) => Number(item.visitors) || 0,
                    previousValue: (item) => Number(item.previous?.visitors) || 0,
                    total: Number(topSearchEnginesResponse?.data?.totals?.visitors?.current) || 1,
                    isCompareEnabled,
                    comparisonDateLabel,
                  })}
                />
              </div>
            )}
            {/* Row 7: Top Countries & Top Browsers */}
            {isWidgetVisible('top-countries') && (
              <div className="col-span-12 lg:col-span-6">
                <HorizontalBarList
                  title={__('Top Countries', 'wp-statistics')}
                  showComparison={isCompareEnabled}
                  columnHeaders={{
                    left: __('Country', 'wp-statistics'),
                    right: __('Visitors', 'wp-statistics'),
                  }}
                  items={transformToBarList(topCountriesResponse?.data?.rows || [], {
                    label: (item) => item.country_name || __('Unknown', 'wp-statistics'),
                    value: (item) => Number(item.visitors) || 0,
                    previousValue: (item) => Number(item.previous?.visitors) || 0,
                    total: Number(topCountriesResponse?.data?.totals?.visitors?.current) || 1,
                    isCompareEnabled,
                    comparisonDateLabel,
                    icon: (item) => (
                      <img
                        src={`${pluginUrl}public/images/flags/${item.country_code?.toLowerCase() || '000'}.svg`}
                        alt={item.country_name || ''}
                        className="h-4 w-4 shrink-0"
                      />
                    ),
                  })}
                />
              </div>
            )}
            {isWidgetVisible('top-browsers') && (
              <div className="col-span-12 lg:col-span-6">
                <HorizontalBarList
                  title={__('Top Browsers', 'wp-statistics')}
                  showComparison={isCompareEnabled}
                  columnHeaders={{
                    left: __('Browser', 'wp-statistics'),
                    right: __('Visitors', 'wp-statistics'),
                  }}
                  items={transformToBarList(topBrowsersResponse?.data?.rows || [], {
                    label: (item) => item.browser_name || __('Unknown', 'wp-statistics'),
                    value: (item) => Number(item.visitors) || 0,
                    previousValue: (item) => Number(item.previous?.visitors) || 0,
                    total: Number(topBrowsersResponse?.data?.totals?.visitors?.current) || 1,
                    isCompareEnabled,
                    comparisonDateLabel,
                    icon: (item) => (
                      <img
                        src={`${pluginUrl}public/images/browser/${(item.browser_name || 'unknown').toLowerCase().replace(/\s+/g, '_')}.svg`}
                        alt={item.browser_name || ''}
                        className="h-4 w-4 shrink-0"
                      />
                    ),
                  })}
                />
              </div>
            )}
            {/* Row 8: Top Operating Systems & Top Device Categories */}
            {isWidgetVisible('top-operating-systems') && (
              <div className="col-span-12 lg:col-span-6">
                <HorizontalBarList
                  title={__('Top Operating Systems', 'wp-statistics')}
                  showComparison={isCompareEnabled}
                  columnHeaders={{
                    left: __('OS', 'wp-statistics'),
                    right: __('Visitors', 'wp-statistics'),
                  }}
                  items={transformToBarList(topOperatingSystemsResponse?.data?.rows || [], {
                    label: (item) => item.os_name || __('Unknown', 'wp-statistics'),
                    value: (item) => Number(item.visitors) || 0,
                    previousValue: (item) => Number(item.previous?.visitors) || 0,
                    total: Number(topOperatingSystemsResponse?.data?.totals?.visitors?.current) || 1,
                    isCompareEnabled,
                    comparisonDateLabel,
                    icon: (item) => (
                      <img
                        src={`${pluginUrl}public/images/operating-system/${(item.os_name || 'unknown').toLowerCase().replace(/\s+/g, '_')}.svg`}
                        alt={item.os_name || ''}
                        className="h-4 w-4 shrink-0"
                      />
                    ),
                  })}
                />
              </div>
            )}
            {isWidgetVisible('top-device-categories') && (
              <div className="col-span-12 lg:col-span-6">
                <HorizontalBarList
                  title={__('Top Device Categories', 'wp-statistics')}
                  showComparison={isCompareEnabled}
                  columnHeaders={{
                    left: __('Device', 'wp-statistics'),
                    right: __('Visitors', 'wp-statistics'),
                  }}
                  items={transformToBarList(topDeviceCategoriesResponse?.data?.rows || [], {
                    label: (item) => item.device_type_name || __('Unknown', 'wp-statistics'),
                    value: (item) => Number(item.visitors) || 0,
                    previousValue: (item) => Number(item.previous?.visitors) || 0,
                    total: Number(topDeviceCategoriesResponse?.data?.totals?.visitors?.current) || 1,
                    isCompareEnabled,
                    comparisonDateLabel,
                    icon: (item) => (
                      <img
                        src={`${pluginUrl}public/images/device/${(item.device_type_name || 'desktop').toLowerCase()}.svg`}
                        alt={item.device_type_name || ''}
                        className="h-4 w-4 shrink-0"
                      />
                    ),
                  })}
                />
              </div>
            )}
          </div>
        )}
      </div>
    </div>
  )
}
