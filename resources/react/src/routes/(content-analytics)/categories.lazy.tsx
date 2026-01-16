import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute, Navigate } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { useCallback, useMemo, useState } from 'react'

import { type DateRange, DateRangePicker } from '@/components/custom/date-range-picker'
import { FilterButton, type FilterField } from '@/components/custom/filter-button'
import { HorizontalBarList } from '@/components/custom/horizontal-bar-list'
import {
  DetailOptionsDrawer,
  OptionsDrawerTrigger,
  useDetailOptions,
} from '@/components/custom/options-drawer'
import { LineChart } from '@/components/custom/line-chart'
import { type MetricItem, Metrics } from '@/components/custom/metrics'
import { TabbedList, type TabbedListTab } from '@/components/custom/tabbed-list'
import { NoticeContainer } from '@/components/ui/notice-container'
import { Panel } from '@/components/ui/panel'
import { BarListSkeleton, ChartSkeleton, MetricsSkeleton, PanelSkeleton } from '@/components/ui/skeletons'
import { useChartData } from '@/hooks/use-chart-data'
import { useComparisonDateLabel } from '@/hooks/use-comparison-date-label'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { usePercentageCalc } from '@/hooks/use-percentage-calc'
import { transformToBarList } from '@/lib/bar-list-helpers'
import { formatCompactNumber, formatDecimal, formatDuration, getTotalValue } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'
import { getCategoriesOverviewQueryOptions } from '@/services/content-analytics/get-categories-overview'

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
  const { term } = Route.useSearch()

  // If term is provided, redirect to the individual category page
  if (term) {
    return <Navigate to="/individual-category" search={{ term_id: term }} replace />
  }

  // Otherwise show the overview
  return <CategoriesOverviewView />
}

/**
 * Categories Overview View - Main categories analytics page
 */
function CategoriesOverviewView() {
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
    isCompareEnabled,
    apiDateParams,
  } = useGlobalFilters()

  const wp = WordPress.getInstance()
  const pluginUrl = wp.getPluginUrl()

  // Options drawer
  const options = useDetailOptions({ filterGroup: 'categories' })

  // Get filter fields for categories page
  const filterFields = wp.getFilterFieldsByGroup('categories') as FilterField[]

  const [activeTermsTab, setActiveTermsTab] = useState<string>('views')
  const [defaultFilterRemoved, setDefaultFilterRemoved] = useState(false)

  // Filter out post_type filters since categories page doesn't use post_type
  const filtersWithoutPostType = (appliedFilters || []).filter((f) => !f.id.startsWith('post_type'))

  // Check if user has applied a taxonomy_type filter (overriding default)
  const hasUserTaxonomyFilter = filtersWithoutPostType.some((f) => f.id.startsWith('taxonomy_type'))

  // Build default taxonomy_type filter for Categories page
  const taxonomyField = filterFields.find((f) => f.name === 'taxonomy_type')
  const categoryOption = taxonomyField?.options?.find((o) => o.value === 'category')
  const defaultTaxonomyFilter = {
    id: 'taxonomy_type-categories-default',
    label: taxonomyField?.label || __('Taxonomy Type', 'wp-statistics'),
    operator: '=',
    rawOperator: 'is',
    value: categoryOption?.label || __('Category', 'wp-statistics'),
    rawValue: 'category',
  }

  // Determine if we should show the default filter
  const showDefaultFilter = !hasUserTaxonomyFilter && !defaultFilterRemoved

  // Filters to use for API requests and display
  const filtersForApi = showDefaultFilter ? [...filtersWithoutPostType, defaultTaxonomyFilter] : filtersWithoutPostType

  const filtersForDisplay = filtersForApi

  // Handle filter removal - detect when taxonomy_type filter is intentionally removed
  const handleCategoriesApplyFilters = (newFilters: typeof appliedFilters) => {
    const hasNewTaxonomyFilter = newFilters?.some((f) => f.id.startsWith('taxonomy_type')) ?? false

    // If we had default filter showing and new filters don't have taxonomy, user removed it
    if (showDefaultFilter && !hasNewTaxonomyFilter) {
      setDefaultFilterRemoved(true)
    }
    // If user adds a taxonomy filter, reset the removed flag
    if (hasNewTaxonomyFilter) {
      setDefaultFilterRemoved(false)
    }

    // Apply only the non-default filters to global state (also filter out post_type)
    const globalFilters =
      newFilters?.filter((f) => f.id !== 'taxonomy_type-categories-default' && !f.id.startsWith('post_type')) ?? []
    handleApplyFilters(globalFilters)
  }

  const [activeContentTab, setActiveContentTab] = useState<string>('popular')
  const [activeAuthorsTab, setActiveAuthorsTab] = useState<string>('views')
  const [timeframe, setTimeframe] = useState<'daily' | 'weekly' | 'monthly'>('daily')

  // Default taxonomy is 'category'
  const [selectedTaxonomy, setSelectedTaxonomy] = useState<string>('category')

  // Get taxonomy label for display
  const taxonomyLabel = useMemo(() => {
    switch (selectedTaxonomy) {
      case 'category':
        return __('Categories', 'wp-statistics')
      case 'post_tag':
        return __('Tags', 'wp-statistics')
      default:
        return selectedTaxonomy
    }
  }, [selectedTaxonomy])

  const taxonomyLabelSingular = useMemo(() => {
    switch (selectedTaxonomy) {
      case 'category':
        return __('Category', 'wp-statistics')
      case 'post_tag':
        return __('Tag', 'wp-statistics')
      default:
        return selectedTaxonomy
    }
  }, [selectedTaxonomy])

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
    ...getCategoriesOverviewQueryOptions({
      dateFrom: apiDateParams.date_from,
      dateTo: apiDateParams.date_to,
      compareDateFrom: apiDateParams.previous_date_from,
      compareDateTo: apiDateParams.previous_date_to,
      timeframe,
      taxonomy: selectedTaxonomy,
      filters: filtersForApi,
    }),
    retry: false,
    placeholderData: keepPreviousData,
    enabled: isInitialized,
  })

  const showSkeleton = isLoading && !batchResponse
  const showFullPageLoading = isFetching && !isLoading

  // Extract data from batch response
  const metricsResponse = batchResponse?.data?.items?.categories_metrics
  const performanceResponse = batchResponse?.data?.items?.categories_performance

  // Traffic sources data
  const topReferrersData = batchResponse?.data?.items?.top_referrers?.data?.rows || []
  const topReferrersTotals = batchResponse?.data?.items?.top_referrers?.data?.totals
  const topSearchEnginesData = batchResponse?.data?.items?.top_search_engines?.data?.rows || []
  const topSearchEnginesTotals = batchResponse?.data?.items?.top_search_engines?.data?.totals
  const topCountriesData = batchResponse?.data?.items?.top_countries?.data?.rows || []
  const topCountriesTotals = batchResponse?.data?.items?.top_countries?.data?.totals

  // Device analytics data
  const topBrowsersData = batchResponse?.data?.items?.top_browsers?.data?.rows || []
  const topBrowsersTotals = batchResponse?.data?.items?.top_browsers?.data?.totals
  const topOsData = batchResponse?.data?.items?.top_os?.data?.rows || []
  const topOsTotals = batchResponse?.data?.items?.top_os?.data?.totals
  const topDevicesData = batchResponse?.data?.items?.top_devices?.data?.rows || []
  const topDevicesTotals = batchResponse?.data?.items?.top_devices?.data?.totals

  const calcPercentage = usePercentageCalc()
  const { label: comparisonDateLabel } = useComparisonDateLabel()

  // Build metrics (8 metrics in 2 rows of 4)
  const categoriesMetrics = useMemo(() => {
    const totals = metricsResponse?.totals
    if (!totals) return []

    const activeTerms = getTotalValue(totals.active_terms)
    const publishedContent = getTotalValue(totals.published_content)
    const visitors = getTotalValue(totals.visitors)
    const views = getTotalValue(totals.views)
    const bounceRate = getTotalValue(totals.bounce_rate)
    const avgTimeOnPage = getTotalValue(totals.avg_time_on_page)

    const prevActiveTerms = getTotalValue(totals.active_terms?.previous)
    const prevPublishedContent = getTotalValue(totals.published_content?.previous)
    const prevVisitors = getTotalValue(totals.visitors?.previous)
    const prevViews = getTotalValue(totals.views?.previous)
    const prevBounceRate = getTotalValue(totals.bounce_rate?.previous)
    const prevAvgTimeOnPage = getTotalValue(totals.avg_time_on_page?.previous)

    const viewsPerTerm = activeTerms > 0 ? views / activeTerms : 0
    const prevViewsPerTerm = prevActiveTerms > 0 ? prevViews / prevActiveTerms : 0

    const avgContentsPerTerm = activeTerms > 0 ? publishedContent / activeTerms : 0
    const prevAvgContentsPerTerm = prevActiveTerms > 0 ? prevPublishedContent / prevActiveTerms : 0

    const metrics: MetricItem[] = [
      {
        label: __('Terms', 'wp-statistics'),
        value: formatCompactNumber(activeTerms),
        ...(isCompareEnabled ? calcPercentage(activeTerms, prevActiveTerms) : {}),
        tooltipContent: __('Number of taxonomy terms with content', 'wp-statistics'),
      },
      {
        label: __('Contents', 'wp-statistics'),
        value: formatCompactNumber(publishedContent),
        ...(isCompareEnabled ? calcPercentage(publishedContent, prevPublishedContent) : {}),
        tooltipContent: __('Number of content items in this taxonomy', 'wp-statistics'),
      },
      {
        label: __('Visitors', 'wp-statistics'),
        value: formatCompactNumber(visitors),
        ...(isCompareEnabled ? calcPercentage(visitors, prevVisitors) : {}),
        tooltipContent: __('Unique visitors to taxonomy content', 'wp-statistics'),
      },
      {
        label: __('Views', 'wp-statistics'),
        value: formatCompactNumber(views),
        ...(isCompareEnabled ? calcPercentage(views, prevViews) : {}),
        tooltipContent: __('Total page views', 'wp-statistics'),
      },
      {
        label: __('Views per Term', 'wp-statistics'),
        value: formatDecimal(viewsPerTerm),
        ...(isCompareEnabled ? calcPercentage(viewsPerTerm, prevViewsPerTerm) : {}),
        tooltipContent: __('Average views per taxonomy term', 'wp-statistics'),
      },
      {
        label: __('Avg. Contents per Term', 'wp-statistics'),
        value: formatDecimal(avgContentsPerTerm),
        ...(isCompareEnabled ? calcPercentage(avgContentsPerTerm, prevAvgContentsPerTerm) : {}),
        tooltipContent: __('Average content items per term', 'wp-statistics'),
      },
      {
        label: __('Bounce Rate', 'wp-statistics'),
        value: `${formatDecimal(bounceRate)}%`,
        ...(isCompareEnabled ? calcPercentage(bounceRate, prevBounceRate) : {}),
        tooltipContent: __('Percentage of single-page sessions', 'wp-statistics'),
      },
      {
        label: __('Avg. Time on Page', 'wp-statistics'),
        value: formatDuration(avgTimeOnPage),
        ...(isCompareEnabled ? calcPercentage(avgTimeOnPage, prevAvgTimeOnPage) : {}),
        tooltipContent: __('Average time spent on pages', 'wp-statistics'),
      },
    ]

    return metrics
  }, [metricsResponse, calcPercentage, isCompareEnabled])

  // Transform chart data using shared hook
  const { data: chartData, metrics: chartMetrics } = useChartData(performanceResponse, {
    metrics: [
      { key: 'visitors', label: __('Visitors', 'wp-statistics'), color: 'var(--chart-1)' },
      { key: 'views', label: __('Views', 'wp-statistics'), color: 'var(--chart-2)' },
      { key: 'published_content', label: __('Content', 'wp-statistics'), color: 'var(--chart-3)' },
    ],
    showPreviousValues: isCompareEnabled,
    preserveNull: true,
  })

  // Get current taxonomy type for "See all" links
  const currentTaxonomyType = useMemo(() => {
    const taxonomyFilter = filtersForApi.find((f) => f.id.startsWith('taxonomy_type'))
    return (taxonomyFilter?.rawValue as string) || 'category'
  }, [filtersForApi])

  // Get taxonomy label for "See all" link text (plural form)
  const seeAllTaxonomyLabel = useMemo(() => {
    const taxonomyFilter = filtersForApi.find((f) => f.id.startsWith('taxonomy_type'))
    if (taxonomyFilter?.value) {
      // Make sure it's plural (add 's' if needed, or use the label as-is if already plural)
      const label = String(taxonomyFilter.value).toLowerCase()
      return label.endsWith('y') ? label.slice(0, -1) + 'ies' : label.endsWith('s') ? label : label + 's'
    }
    return 'categories'
  }, [filtersForApi])

  // Build tabbed list tabs for top terms
  const topTermsTabs: TabbedListTab[] = useMemo(() => {
    const topTermsViews = batchResponse?.data?.items?.top_terms_views?.data?.rows || []
    const topTermsPublishing = batchResponse?.data?.items?.top_terms_publishing?.data?.rows || []

    // Build "See all" link with current taxonomy
    const seeAllLink = {
      href: `/top-categories?taxonomy=${currentTaxonomyType}`,
    }

    const tabs: TabbedListTab[] = [
      {
        id: 'views',
        label: __('Views', 'wp-statistics'),
        items: topTermsViews.map((item) => ({
          id: String(item.term_id),
          title: item.term_name || __('Unknown Term', 'wp-statistics'),
          subtitle: `${formatCompactNumber(Number(item.views))} ${__('views', 'wp-statistics')}`,
          thumbnail: `${pluginUrl}public/images/placeholder.png`,
          href: `/individual-category?term_id=${item.term_id}`,
        })),
        link: seeAllLink,
      },
      {
        id: 'publishing',
        label: __('Publishing', 'wp-statistics'),
        items: topTermsPublishing.map((item) => ({
          id: String(item.term_id),
          title: item.term_name || __('Unknown Term', 'wp-statistics'),
          subtitle: `${formatCompactNumber(Number(item.published_content))} ${__('contents', 'wp-statistics')}`,
          thumbnail: `${pluginUrl}public/images/placeholder.png`,
          href: `/individual-category?term_id=${item.term_id}`,
        })),
        link: seeAllLink,
      },
    ]

    return tabs
  }, [batchResponse, pluginUrl, currentTaxonomyType, seeAllTaxonomyLabel])

  // Build tabbed list tabs for top content
  const topContentTabs: TabbedListTab[] = useMemo(() => {
    const topContentPopular = batchResponse?.data?.items?.top_content_popular?.data?.rows || []
    const topContentCommented = batchResponse?.data?.items?.top_content_commented?.data?.rows || []
    const topContentRecent = batchResponse?.data?.items?.top_content_recent?.data?.rows || []

    const tabs: TabbedListTab[] = [
      {
        id: 'popular',
        label: __('Most Popular', 'wp-statistics'),
        items: topContentPopular.map((item) => ({
          id: String(item.resource_id),
          title: item.page_title || __('Unknown Page', 'wp-statistics'),
          subtitle: `${formatCompactNumber(Number(item.views))} ${__('views', 'wp-statistics')}`,
          thumbnail: item.thumbnail_url || `${pluginUrl}public/images/placeholder.png`,
          href: `/individual-content?resource_id=${item.resource_id}`,
        })),
      },
      {
        id: 'commented',
        label: __('Most Commented', 'wp-statistics'),
        items: topContentCommented.map((item) => ({
          id: String(item.resource_id),
          title: item.page_title || __('Unknown Page', 'wp-statistics'),
          subtitle: `${formatCompactNumber(Number(item.comments))} ${__('comments', 'wp-statistics')} · ${formatCompactNumber(Number(item.views))} ${__('views', 'wp-statistics')}`,
          thumbnail: item.thumbnail_url || `${pluginUrl}public/images/placeholder.png`,
          href: `/individual-content?resource_id=${item.resource_id}`,
        })),
      },
      {
        id: 'recent',
        label: __('Most Recent', 'wp-statistics'),
        items: topContentRecent.map((item) => ({
          id: String(item.resource_id),
          title: item.page_title || __('Unknown Page', 'wp-statistics'),
          subtitle: `${item.published_date || ''} · ${formatCompactNumber(Number(item.views))} ${__('views', 'wp-statistics')}`,
          thumbnail: item.thumbnail_url || `${pluginUrl}public/images/placeholder.png`,
          href: `/individual-content?resource_id=${item.resource_id}`,
        })),
      },
    ]

    return tabs
  }, [batchResponse, pluginUrl])

  // Build tabbed list tabs for top authors (only 2 tabs for categories context)
  const topAuthorsTabs: TabbedListTab[] = useMemo(() => {
    const topAuthorsViews = batchResponse?.data?.items?.top_authors_views?.data?.rows || []
    const topAuthorsPublishing = batchResponse?.data?.items?.top_authors_publishing?.data?.rows || []

    const tabs: TabbedListTab[] = [
      {
        id: 'views',
        label: __('Views', 'wp-statistics'),
        items: topAuthorsViews.map((item) => ({
          id: String(item.author_id),
          title: item.author_name || __('Unknown Author', 'wp-statistics'),
          subtitle: `${formatCompactNumber(Number(item.views))} ${__('views', 'wp-statistics')}`,
          thumbnail: item.author_avatar || `${pluginUrl}public/images/placeholder.png`,
          href: `/individual-author?author_id=${item.author_id}`,
        })),
        link: {
          href: '/top-authors',
        },
      },
      {
        id: 'publishing',
        label: __('Publishing', 'wp-statistics'),
        items: topAuthorsPublishing.map((item) => ({
          id: String(item.author_id),
          title: item.author_name || __('Unknown Author', 'wp-statistics'),
          subtitle: `${formatCompactNumber(Number(item.published_content))} ${__('contents', 'wp-statistics')}`,
          thumbnail: item.author_avatar || `${pluginUrl}public/images/placeholder.png`,
          href: `/individual-author?author_id=${item.author_id}`,
        })),
        link: {
          href: '/top-authors',
        },
      },
    ]

    return tabs
  }, [batchResponse, pluginUrl])

  return (
    <div className="min-w-0">
      {/* Header */}
      <div className="flex items-center justify-between px-4 py-3 ">
        <h1 className="text-2xl font-semibold text-neutral-800">{taxonomyLabel}</h1>
        <div className="flex items-center gap-3">
          <div className="hidden lg:flex">
            {filterFields.length > 0 && isInitialized && (
              <FilterButton
                fields={filterFields}
                appliedFilters={filtersForDisplay}
                onApplyFilters={handleCategoriesApplyFilters}
                filterGroup="categories"
              />
            )}
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
      <DetailOptionsDrawer
        config={{ filterGroup: 'categories' }}
        isOpen={options.isOpen}
        setIsOpen={options.setIsOpen}
      />

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
              <PanelSkeleton>
                <ChartSkeleton />
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
            <div className="col-span-12">
              <PanelSkeleton>
                <BarListSkeleton items={5} showIcon />
              </PanelSkeleton>
            </div>
          </div>
        ) : (
          <div className="grid gap-3 grid-cols-12">
            {/* Row 1: Categories Metrics */}
            <div className="col-span-12">
              <Panel>
                <Metrics metrics={categoriesMetrics} columns={4} />
              </Panel>
            </div>

            {/* Row 2: Categories Performance Chart */}
            <div className="col-span-12">
              <LineChart
                title={__('Performance', 'wp-statistics')}
                data={chartData}
                metrics={chartMetrics}
                showPreviousPeriod={isCompareEnabled}
                timeframe={timeframe}
                onTimeframeChange={setTimeframe}
                compareDateTo={apiDateParams.previous_date_to}
                dateTo={apiDateParams.date_to}
              />
            </div>

            {/* Row 3: Top Terms Tabbed List */}
            <div className="col-span-12">
              <TabbedList
                title={`${__('Top', 'wp-statistics')} ${taxonomyLabel}`}
                tabs={topTermsTabs}
                activeTab={activeTermsTab}
                onTabChange={setActiveTermsTab}
                emptyMessage={__('No terms available for the selected period', 'wp-statistics')}
              />
            </div>

            {/* Row 4: Top Content Tabbed List */}
            <div className="col-span-12">
              <TabbedList
                title={__('Top Content', 'wp-statistics')}
                tabs={topContentTabs}
                activeTab={activeContentTab}
                onTabChange={setActiveContentTab}
                emptyMessage={__('No content available for the selected period', 'wp-statistics')}
              />
            </div>

            {/* Row 5: Top Authors Tabbed List (2 tabs only) */}
            <div className="col-span-12">
              <TabbedList
                title={__('Top Authors', 'wp-statistics')}
                tabs={topAuthorsTabs}
                activeTab={activeAuthorsTab}
                onTabChange={setActiveAuthorsTab}
                emptyMessage={__('No authors available for the selected period', 'wp-statistics')}
              />
            </div>

            {/* Row 6: Traffic Sources (Three Columns) */}
            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Top Referrers', 'wp-statistics')}
                showComparison={isCompareEnabled}
                items={transformToBarList(topReferrersData, {
                  label: (item) =>
                    item.referrer_name || item.referrer_domain || item.referrer_channel || __('Direct', 'wp-statistics'),
                  value: (item) => Number(item.visitors) || 0,
                  previousValue: (item) => Number(item.previous?.visitors) || 0,
                  total: Number(topReferrersTotals?.visitors?.current ?? topReferrersTotals?.visitors) || 1,
                  isCompareEnabled,
                  comparisonDateLabel,
                })}
              />
            </div>

            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Top Search Engines', 'wp-statistics')}
                showComparison={isCompareEnabled}
                items={transformToBarList(topSearchEnginesData, {
                  label: (item) => item.referrer_name || item.referrer_domain || __('Unknown', 'wp-statistics'),
                  value: (item) => Number(item.visitors) || 0,
                  previousValue: (item) => Number(item.previous?.visitors) || 0,
                  total: Number(topSearchEnginesTotals?.visitors?.current ?? topSearchEnginesTotals?.visitors) || 1,
                  isCompareEnabled,
                  comparisonDateLabel,
                })}
              />
            </div>

            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Top Countries', 'wp-statistics')}
                showComparison={isCompareEnabled}
                items={transformToBarList(topCountriesData, {
                  label: (item) => item.country_name || __('Unknown', 'wp-statistics'),
                  value: (item) => Number(item.visitors) || 0,
                  previousValue: (item) => Number(item.previous?.visitors) || 0,
                  total: Number(topCountriesTotals?.visitors?.current ?? topCountriesTotals?.visitors) || 1,
                  icon: (item) => (
                    <img
                      src={`${pluginUrl}public/images/flags/${item.country_code?.toLowerCase() || '000'}.svg`}
                      alt={item.country_name || ''}
                      className="w-4 h-3"
                    />
                  ),
                  isCompareEnabled,
                  comparisonDateLabel,
                })}
              />
            </div>

            {/* Row 7: Device Analytics (Three Columns) */}
            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Top Browsers', 'wp-statistics')}
                showComparison={isCompareEnabled}
                items={transformToBarList(topBrowsersData, {
                  label: (item) => item.browser_name || __('Unknown', 'wp-statistics'),
                  value: (item) => Number(item.visitors) || 0,
                  previousValue: (item) => Number(item.previous?.visitors) || 0,
                  total: Number(topBrowsersTotals?.visitors?.current ?? topBrowsersTotals?.visitors) || 1,
                  icon: (item) => (
                    <img
                      src={`${pluginUrl}public/images/browser/${(item.browser_name || 'unknown').toLowerCase().replace(/\s+/g, '_')}.svg`}
                      alt={item.browser_name || ''}
                      className="w-4 h-3"
                    />
                  ),
                  isCompareEnabled,
                  comparisonDateLabel,
                })}
              />
            </div>

            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Top Operating Systems', 'wp-statistics')}
                showComparison={isCompareEnabled}
                items={transformToBarList(topOsData, {
                  label: (item) => item.os_name || __('Unknown', 'wp-statistics'),
                  value: (item) => Number(item.visitors) || 0,
                  previousValue: (item) => Number(item.previous?.visitors) || 0,
                  total: Number(topOsTotals?.visitors?.current ?? topOsTotals?.visitors) || 1,
                  icon: (item) => (
                    <img
                      src={`${pluginUrl}public/images/operating-system/${(item.os_name || 'unknown').toLowerCase().replace(/\s+/g, '_')}.svg`}
                      alt={item.os_name || ''}
                      className="w-4 h-3"
                    />
                  ),
                  isCompareEnabled,
                  comparisonDateLabel,
                })}
              />
            </div>

            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Top Device Categories', 'wp-statistics')}
                showComparison={isCompareEnabled}
                items={transformToBarList(topDevicesData, {
                  label: (item) => item.device_type_name || __('Unknown', 'wp-statistics'),
                  value: (item) => Number(item.visitors) || 0,
                  previousValue: (item) => Number(item.previous?.visitors) || 0,
                  total: Number(topDevicesTotals?.visitors?.current ?? topDevicesTotals?.visitors) || 1,
                  icon: (item) => (
                    <img
                      src={`${pluginUrl}public/images/device/${(item.device_type_name || 'desktop').toLowerCase()}.svg`}
                      alt={item.device_type_name || ''}
                      className="w-4 h-3"
                    />
                  ),
                  isCompareEnabled,
                  comparisonDateLabel,
                })}
              />
            </div>
          </div>
        )}
      </div>
    </div>
  )
}
