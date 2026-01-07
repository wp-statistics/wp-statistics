import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { useCallback, useMemo, useState } from 'react'

import { type DateRange, DateRangePicker } from '@/components/custom/date-range-picker'
import { FilterBar } from '@/components/custom/filter-bar'
import { FilterButton, type FilterField } from '@/components/custom/filter-button'
import { HorizontalBarList } from '@/components/custom/horizontal-bar-list'
import { LineChart, type LineChartDataPoint } from '@/components/custom/line-chart'
import { type MetricItem, Metrics } from '@/components/custom/metrics'
import { TabbedList, type TabbedListTab } from '@/components/custom/tabbed-list'
import { Panel } from '@/components/ui/panel'
import { NoticeContainer } from '@/components/ui/notice-container'
import { BarListSkeleton, ChartSkeleton, MetricsSkeleton, PanelSkeleton } from '@/components/ui/skeletons'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { usePercentageCalc } from '@/hooks/use-percentage-calc'
import { calcSharePercentage, formatCompactNumber, formatDecimal, formatDuration } from '@/lib/utils'
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
  const { term, taxonomy, view } = Route.useSearch()

  // If term and taxonomy are provided, show individual category view
  if (term && taxonomy) {
    return <IndividualCategoryView termId={term} taxonomy={taxonomy} />
  }

  // If view is 'table', show the top categories table view
  if (view === 'table') {
    return <TopCategoriesTableView />
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

  const [activeTermsTab, setActiveTermsTab] = useState<string>('views')
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
    (values: { range: DateRange; rangeCompare?: DateRange }) => {
      setDateRange(values.range, values.rangeCompare)
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
      filters: appliedFilters || [],
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

  // Build metrics (8 metrics in 2 rows of 4)
  const categoriesMetrics = useMemo(() => {
    const totals = metricsResponse?.totals
    if (!totals) return []

    const activeTerms = Number(totals.active_terms?.current) || 0
    const publishedContent = Number(totals.published_content?.current) || 0
    const visitors = Number(totals.visitors?.current) || 0
    const views = Number(totals.views?.current) || 0
    const bounceRate = Number(totals.bounce_rate?.current) || 0
    const avgTimeOnPage = Number(totals.avg_time_on_page?.current) || 0

    const prevActiveTerms = Number(totals.active_terms?.previous) || 0
    const prevPublishedContent = Number(totals.published_content?.previous) || 0
    const prevVisitors = Number(totals.visitors?.previous) || 0
    const prevViews = Number(totals.views?.previous) || 0
    const prevBounceRate = Number(totals.bounce_rate?.previous) || 0
    const prevAvgTimeOnPage = Number(totals.avg_time_on_page?.previous) || 0

    const viewsPerTerm = activeTerms > 0 ? views / activeTerms : 0
    const prevViewsPerTerm = prevActiveTerms > 0 ? prevViews / prevActiveTerms : 0

    const avgContentsPerTerm = activeTerms > 0 ? publishedContent / activeTerms : 0
    const prevAvgContentsPerTerm = prevActiveTerms > 0 ? prevPublishedContent / prevActiveTerms : 0

    const metrics: MetricItem[] = [
      {
        label: __('Terms', 'wp-statistics'),
        value: formatCompactNumber(activeTerms),
        ...calcPercentage(activeTerms, prevActiveTerms),
        tooltipContent: __('Number of taxonomy terms with content', 'wp-statistics'),
      },
      {
        label: __('Contents', 'wp-statistics'),
        value: formatCompactNumber(publishedContent),
        ...calcPercentage(publishedContent, prevPublishedContent),
        tooltipContent: __('Number of content items in this taxonomy', 'wp-statistics'),
      },
      {
        label: __('Visitors', 'wp-statistics'),
        value: formatCompactNumber(visitors),
        ...calcPercentage(visitors, prevVisitors),
        tooltipContent: __('Unique visitors to taxonomy content', 'wp-statistics'),
      },
      {
        label: __('Views', 'wp-statistics'),
        value: formatCompactNumber(views),
        ...calcPercentage(views, prevViews),
        tooltipContent: __('Total page views', 'wp-statistics'),
      },
      {
        label: __('Views per Term', 'wp-statistics'),
        value: formatDecimal(viewsPerTerm),
        ...calcPercentage(viewsPerTerm, prevViewsPerTerm),
        tooltipContent: __('Average views per taxonomy term', 'wp-statistics'),
      },
      {
        label: __('Avg. Contents per Term', 'wp-statistics'),
        value: formatDecimal(avgContentsPerTerm),
        ...calcPercentage(avgContentsPerTerm, prevAvgContentsPerTerm),
        tooltipContent: __('Average content items per term', 'wp-statistics'),
      },
      {
        label: __('Bounce Rate', 'wp-statistics'),
        value: `${formatDecimal(bounceRate)}%`,
        ...calcPercentage(bounceRate, prevBounceRate),
        tooltipContent: __('Percentage of single-page sessions', 'wp-statistics'),
      },
      {
        label: __('Avg. Time on Page', 'wp-statistics'),
        value: formatDuration(avgTimeOnPage),
        ...calcPercentage(avgTimeOnPage, prevAvgTimeOnPage),
        tooltipContent: __('Average time spent on pages', 'wp-statistics'),
      },
    ]

    return metrics
  }, [metricsResponse, calcPercentage])

  // Transform chart format response to data points for LineChart component
  // Chart format: { labels: string[], datasets: [{ key, data, comparison? }] }
  // Previous data comes as separate datasets with key like "visitors_previous" and comparison: true
  const chartData = useMemo((): LineChartDataPoint[] => {
    if (!performanceResponse?.labels || !performanceResponse?.datasets) return []

    const labels = performanceResponse.labels
    const datasets = performanceResponse.datasets

    // Separate current and previous datasets
    const currentDatasets = datasets.filter((d) => !d.comparison)
    const previousDatasets = datasets.filter((d) => d.comparison)

    return labels.map((label, index) => {
      const point: LineChartDataPoint = { date: label }

      // Add current period data
      currentDatasets.forEach((dataset) => {
        point[dataset.key] = Number(dataset.data[index]) || 0
      })

      // Add previous period data (datasets have keys like "visitors_previous")
      previousDatasets.forEach((dataset) => {
        // Convert "visitors_previous" to "visitorsPrevious"
        const baseKey = dataset.key.replace('_previous', '')
        point[`${baseKey}Previous`] = Number(dataset.data[index]) || 0
      })

      return point
    })
  }, [performanceResponse])

  // Calculate totals from chart datasets
  const chartTotals = useMemo(() => {
    if (!performanceResponse?.datasets) {
      return {
        visitors: 0,
        visitorsPrevious: 0,
        views: 0,
        viewsPrevious: 0,
        published_content: 0,
        published_contentPrevious: 0,
      }
    }

    const datasets = performanceResponse.datasets
    const visitorsDataset = datasets.find((d) => d.key === 'visitors' && !d.comparison)
    const visitorsPrevDataset = datasets.find((d) => d.key === 'visitors_previous' && d.comparison)
    const viewsDataset = datasets.find((d) => d.key === 'views' && !d.comparison)
    const viewsPrevDataset = datasets.find((d) => d.key === 'views_previous' && d.comparison)
    const contentDataset = datasets.find((d) => d.key === 'published_content' && !d.comparison)
    const contentPrevDataset = datasets.find((d) => d.key === 'published_content_previous' && d.comparison)

    return {
      visitors: visitorsDataset?.data?.reduce((sum: number, v) => sum + Number(v), 0) || 0,
      visitorsPrevious: visitorsPrevDataset?.data?.reduce((sum: number, v) => sum + Number(v), 0) || 0,
      views: viewsDataset?.data?.reduce((sum: number, v) => sum + Number(v), 0) || 0,
      viewsPrevious: viewsPrevDataset?.data?.reduce((sum: number, v) => sum + Number(v), 0) || 0,
      published_content: contentDataset?.data?.reduce((sum: number, v) => sum + Number(v), 0) || 0,
      published_contentPrevious: contentPrevDataset?.data?.reduce((sum: number, v) => sum + Number(v), 0) || 0,
    }
  }, [performanceResponse])

  // Build chart metrics for LineChart legend
  const chartMetrics = useMemo(
    () => [
      {
        key: 'visitors',
        label: __('Visitors', 'wp-statistics'),
        color: 'var(--chart-1)',
        enabled: true,
        value: formatCompactNumber(chartTotals.visitors),
        previousValue: formatCompactNumber(chartTotals.visitorsPrevious),
      },
      {
        key: 'views',
        label: __('Views', 'wp-statistics'),
        color: 'var(--chart-2)',
        enabled: true,
        value: formatCompactNumber(chartTotals.views),
        previousValue: formatCompactNumber(chartTotals.viewsPrevious),
      },
      {
        key: 'published_content',
        label: __('Content', 'wp-statistics'),
        color: 'var(--chart-3)',
        enabled: true,
        value: formatCompactNumber(chartTotals.published_content),
        previousValue: formatCompactNumber(chartTotals.published_contentPrevious),
      },
    ],
    [chartTotals]
  )

  // Build tabbed list tabs for top terms
  const topTermsTabs: TabbedListTab[] = useMemo(() => {
    const topTermsViews = batchResponse?.data?.items?.top_terms_views?.data?.rows || []
    const topTermsPublishing = batchResponse?.data?.items?.top_terms_publishing?.data?.rows || []

    const dateParams = `date_from=${apiDateParams.date_from}&date_to=${apiDateParams.date_to}`

    const tabs: TabbedListTab[] = [
      {
        id: 'views',
        label: __('Views', 'wp-statistics'),
        items: topTermsViews.map((item) => ({
          id: String(item.term_id),
          title: item.term_name || __('Unknown Term', 'wp-statistics'),
          subtitle: `${formatCompactNumber(Number(item.views))} ${__('views', 'wp-statistics')}`,
          thumbnail: `${pluginUrl}public/images/placeholder.png`,
          href: `?term=${item.term_id}&taxonomy=${item.taxonomy_type}`,
        })),
        link: {
          title: `${__('See all', 'wp-statistics')} ${taxonomyLabel.toLowerCase()}`,
          href: `?view=table&taxonomy=${selectedTaxonomy}&${dateParams}&order_by=views&order=desc`,
        },
      },
      {
        id: 'publishing',
        label: __('Publishing', 'wp-statistics'),
        items: topTermsPublishing.map((item) => ({
          id: String(item.term_id),
          title: item.term_name || __('Unknown Term', 'wp-statistics'),
          subtitle: `${formatCompactNumber(Number(item.published_content))} ${__('contents', 'wp-statistics')}`,
          thumbnail: `${pluginUrl}public/images/placeholder.png`,
          href: `?term=${item.term_id}&taxonomy=${item.taxonomy_type}`,
        })),
        link: {
          title: `${__('See all', 'wp-statistics')} ${taxonomyLabel.toLowerCase()}`,
          href: `?view=table&taxonomy=${selectedTaxonomy}&${dateParams}&order_by=published_content&order=desc`,
        },
      },
    ]

    return tabs
  }, [batchResponse, apiDateParams, taxonomyLabel, selectedTaxonomy, pluginUrl])

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
          href: item.page_uri,
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
          href: item.page_uri,
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
          href: item.page_uri,
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
          href: `/authors?author=${item.author_id}`,
        })),
        link: {
          title: __('See all authors', 'wp-statistics'),
          href: '/authors',
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
          href: `/authors?author=${item.author_id}`,
        })),
        link: {
          title: __('See all authors', 'wp-statistics'),
          href: '/authors',
        },
      },
    ]

    return tabs
  }, [batchResponse, pluginUrl])

  return (
    <div className="min-w-0">
      {/* Header */}
      <div className="flex items-center justify-between px-4 py-3 bg-white border-b border-input">
        <h1 className="text-xl font-semibold text-neutral-800">{taxonomyLabel}</h1>
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

      <div className="p-2">
        <NoticeContainer className="mb-2" currentRoute="categories" />
        {appliedFilters && appliedFilters.length > 0 && (
          <FilterBar filters={appliedFilters} onRemoveFilter={handleRemoveFilter} className="mb-2" />
        )}

        {showSkeleton || showFullPageLoading ? (
          <div className="grid gap-2 grid-cols-12">
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
          <div className="grid gap-2 grid-cols-12">
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
                showPreviousPeriod={!!compareDateFrom}
                timeframe={timeframe}
                onTimeframeChange={setTimeframe}
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
                items={(() => {
                  const totalVisitors =
                    Number(topReferrersTotals?.visitors?.current ?? topReferrersTotals?.visitors) || 1
                  return topReferrersData.map((item) => {
                    const currentValue = Number(item.visitors) || 0
                    const previousValue = Number(item.previous?.visitors) || 0
                    const { percentage, isNegative } = calcPercentage(currentValue, previousValue)
                    const displayName =
                      item.referrer_name ||
                      item.referrer_domain ||
                      item.referrer_channel ||
                      __('Direct', 'wp-statistics')

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
                  const totalVisitors =
                    Number(topSearchEnginesTotals?.visitors?.current ?? topSearchEnginesTotals?.visitors) || 1
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
                  const totalVisitors =
                    Number(topCountriesTotals?.visitors?.current ?? topCountriesTotals?.visitors) || 1
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

            {/* Row 7: Device Analytics (Three Columns) */}
            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Top Browsers', 'wp-statistics')}
                items={(() => {
                  const totalVisitors = Number(topBrowsersTotals?.visitors?.current ?? topBrowsersTotals?.visitors) || 1
                  return topBrowsersData.map((item) => {
                    const currentValue = Number(item.visitors) || 0
                    const previousValue = Number(item.previous?.visitors) || 0
                    const { percentage, isNegative } = calcPercentage(currentValue, previousValue)
                    const iconName = (item.browser_name || 'unknown').toLowerCase().replace(/\s+/g, '_')

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
                  const totalVisitors = Number(topOsTotals?.visitors?.current ?? topOsTotals?.visitors) || 1
                  return topOsData.map((item) => {
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
                  const totalVisitors = Number(topDevicesTotals?.visitors?.current ?? topDevicesTotals?.visitors) || 1
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

/**
 * Individual Category View - Detailed view for a single taxonomy term
 * TODO: Implement this view in a future phase
 */
function IndividualCategoryView({ termId, taxonomy }: { termId: number; taxonomy: string }) {
  return (
    <div className="min-w-0 p-6">
      <div className="text-center">
        <h1 className="text-xl font-semibold text-neutral-800 mb-2">
          {__('Individual Category View', 'wp-statistics')}
        </h1>
        <p className="text-muted-foreground">
          {__('Term ID:', 'wp-statistics')} {termId}
        </p>
        <p className="text-muted-foreground">
          {__('Taxonomy:', 'wp-statistics')} {taxonomy}
        </p>
        <p className="text-muted-foreground mt-2">
          {__('This view will be implemented in a future phase.', 'wp-statistics')}
        </p>
      </div>
    </div>
  )
}

/**
 * Top Categories Table View - Table view showing all taxonomy terms
 * TODO: Implement this view in a future phase
 */
function TopCategoriesTableView() {
  return (
    <div className="min-w-0 p-6">
      <div className="text-center">
        <h1 className="text-xl font-semibold text-neutral-800 mb-2">{__('Top Categories Table', 'wp-statistics')}</h1>
        <p className="text-muted-foreground mt-2">
          {__('This view will be implemented in a future phase.', 'wp-statistics')}
        </p>
      </div>
    </div>
  )
}
