import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'
import { __, sprintf } from '@wordpress/i18n'
import { useCallback, useEffect, useMemo, useState } from 'react'

import { DateRangePicker } from '@/components/custom/date-range-picker'
import { FilterButton, type FilterField } from '@/components/custom/filter-button'
import { HorizontalBar } from '@/components/custom/horizontal-bar'
import { type MetricItem, Metrics } from '@/components/custom/metrics'
import {
  type OverviewOptionsConfig,
  OptionsDrawerTrigger,
  OverviewOptionsDrawer,
  OverviewOptionsProvider,
  useOverviewOptions,
} from '@/components/custom/options-drawer'
import { TabbedPanel, type TabbedPanelTab } from '@/components/custom/tabbed-panel'
import { EmptyState } from '@/components/ui/empty-state'
import { NoticeContainer } from '@/components/ui/notice-container'
import { Panel } from '@/components/ui/panel'
import { BarListSkeleton, MetricsSkeleton, PanelSkeleton } from '@/components/ui/skeletons'
import { type MetricConfig, type WidgetConfig } from '@/contexts/page-options-context'
import { useComparisonDateLabel } from '@/hooks/use-comparison-date-label'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { usePageOptions } from '@/hooks/use-page-options'
import { usePercentageCalc } from '@/hooks/use-percentage-calc'
import { formatCompactNumber, formatDecimal, getTotalValue } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'
import { getAuthorsOverviewQueryOptions, type AuthorRow } from '@/services/content-analytics/get-authors-overview'

// Widget configuration for Authors page
const WIDGET_CONFIGS: WidgetConfig[] = [
  { id: 'top-authors', label: __('Top Authors', 'wp-statistics'), defaultVisible: true },
]

// Metric configuration for Authors page (6 metrics)
const METRIC_CONFIGS: MetricConfig[] = [
  { id: 'published-content', label: __('Published Content', 'wp-statistics'), defaultVisible: true },
  { id: 'active-authors', label: __('Active Authors', 'wp-statistics'), defaultVisible: true },
  { id: 'visitors', label: __('Visitors', 'wp-statistics'), defaultVisible: true },
  { id: 'views', label: __('Views', 'wp-statistics'), defaultVisible: true },
  { id: 'views-per-author', label: __('Views per Author', 'wp-statistics'), defaultVisible: true },
  { id: 'avg-posts-per-author', label: __('Avg. Posts per Author', 'wp-statistics'), defaultVisible: true },
]

// Options configuration for this page
const OPTIONS_CONFIG: OverviewOptionsConfig = {
  pageId: 'authors-overview',
  filterGroup: 'content',
  widgetConfigs: WIDGET_CONFIGS,
  metricConfigs: METRIC_CONFIGS,
}

export const Route = createLazyFileRoute('/(content-analytics)/authors')({
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
    <OverviewOptionsProvider config={OPTIONS_CONFIG}>
      <AuthorsOverviewContent />
    </OverviewOptionsProvider>
  )
}

/**
 * Authors Overview View - Main authors analytics page
 */
function AuthorsOverviewContent() {
  const {
    dateFrom,
    dateTo,
    compareDateFrom,
    compareDateTo,
    period,
    filters: appliedFilters,
    handleDateRangeUpdate,
    applyFilters: handleApplyFilters,
    isInitialized,
    apiDateParams,
    isCompareEnabled,
  } = useGlobalFilters()

  // Page options for metric and widget visibility
  const { isMetricVisible, isWidgetVisible } = usePageOptions()

  // Options drawer (uses reusable components)
  const options = useOverviewOptions(OPTIONS_CONFIG)

  const wp = WordPress.getInstance()

  // Get filter fields for content analytics
  const filterFields = useMemo<FilterField[]>(() => {
    return wp.getFilterFieldsByGroup('content') as FilterField[]
  }, [wp])

  const [defaultFilterRemoved, setDefaultFilterRemoved] = useState(false)

  // Build default post_type filter for Authors page (page-specific, not global)
  const defaultPostTypeFilter = useMemo(() => {
    const postTypeField = filterFields.find((f) => f.name === 'post_type')
    const postTypeOption = postTypeField?.options?.find((o) => o.value === 'post')

    return {
      id: 'post_type-authors-default',
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

  // Filters to display (includes default if no user filter and not removed)
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
  const handleAuthorsApplyFilters = useCallback(
    (newFilters: typeof appliedFilters) => {
      // Check if post_type filter existed before but not in new filters
      const hadPostTypeFilter = filtersForDisplay.some((f) => f.id.startsWith('post_type'))
      const hasNewPostTypeFilter = newFilters?.some((f) => f.id.startsWith('post_type')) ?? false

      if (hadPostTypeFilter && !hasNewPostTypeFilter) {
        // User intentionally removed the post_type filter
        setDefaultFilterRemoved(true)
      }

      // Apply only the non-default filters to global state
      const globalFilters = newFilters?.filter((f) => f.id !== 'post_type-authors-default') ?? []
      handleApplyFilters(globalFilters)
    },
    [filtersForDisplay, handleApplyFilters]
  )

  // Batch query for all overview data
  const {
    data: batchResponse,
    isLoading,
    isFetching,
  } = useQuery({
    ...getAuthorsOverviewQueryOptions({
      dateFrom: apiDateParams.date_from,
      dateTo: apiDateParams.date_to,
      compareDateFrom: apiDateParams.previous_date_from,
      compareDateTo: apiDateParams.previous_date_to,
      filters: filtersForApi,
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
  const metricsResponse = batchResponse?.data?.items?.author_metrics
  const topAuthorsResponse = batchResponse?.data?.items?.top_authors

  const calcPercentage = usePercentageCalc()
  const { label: comparisonDateLabel } = useComparisonDateLabel()

  // Get post type label from filters (includes page-specific default)
  const postTypeLabel = useMemo(() => {
    // Look for post_type filter in filters for API (includes default)
    const postTypeFilter = filtersForApi.find((f) => f.id.startsWith('post_type'))
    if (postTypeFilter?.value) {
      // Return the display value which is the label
      return String(postTypeFilter.value)
    }
    return __('Posts', 'wp-statistics')
  }, [filtersForApi])

  // Build metrics with visibility filtering
  const authorsMetrics = useMemo(() => {
    const totals = metricsResponse?.totals
    if (!totals) return []

    const publishedContent = getTotalValue(totals.published_content)
    const activeAuthors = getTotalValue(totals.active_authors)
    const visitors = getTotalValue(totals.visitors)
    const views = getTotalValue(totals.views)

    const prevPublishedContent = getTotalValue(totals.published_content?.previous)
    const prevActiveAuthors = getTotalValue(totals.active_authors?.previous)
    const prevVisitors = getTotalValue(totals.visitors?.previous)
    const prevViews = getTotalValue(totals.views?.previous)

    // Computed metrics
    const viewsPerAuthor = activeAuthors > 0 ? Math.round(views / activeAuthors) : 0
    const prevViewsPerAuthor = prevActiveAuthors > 0 ? Math.round(prevViews / prevActiveAuthors) : 0

    const avgPostsPerAuthor = activeAuthors > 0 ? publishedContent / activeAuthors : 0
    const prevAvgPostsPerAuthor = prevActiveAuthors > 0 ? prevPublishedContent / prevActiveAuthors : 0

    // Build all metrics with IDs for filtering
    const allMetrics: (MetricItem & { id: string })[] = [
      {
        id: 'published-content',
        label: sprintf(__('Published %s', 'wp-statistics'), postTypeLabel),
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
        id: 'active-authors',
        label: __('Active Authors', 'wp-statistics'),
        value: formatCompactNumber(activeAuthors),
        ...(isCompareEnabled
          ? {
              ...calcPercentage(activeAuthors, prevActiveAuthors),
              comparisonDateLabel,
              previousValue: formatCompactNumber(prevActiveAuthors),
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
        id: 'views-per-author',
        label: __('Views per Author', 'wp-statistics'),
        value: formatCompactNumber(viewsPerAuthor),
        ...(isCompareEnabled
          ? {
              ...calcPercentage(viewsPerAuthor, prevViewsPerAuthor),
              comparisonDateLabel,
              previousValue: formatCompactNumber(prevViewsPerAuthor),
            }
          : {}),
      },
      {
        id: 'avg-posts-per-author',
        label: sprintf(__('Avg. %s per Author', 'wp-statistics'), postTypeLabel),
        value: formatDecimal(avgPostsPerAuthor),
        ...(isCompareEnabled
          ? {
              ...calcPercentage(avgPostsPerAuthor, prevAvgPostsPerAuthor),
              comparisonDateLabel,
              previousValue: formatDecimal(prevAvgPostsPerAuthor),
            }
          : {}),
      },
    ]

    // Filter metrics based on visibility
    return allMetrics.filter((metric) => isMetricVisible(metric.id))
  }, [metricsResponse, postTypeLabel, calcPercentage, isCompareEnabled, comparisonDateLabel, isMetricVisible])

  // Get author rows from response
  const authorRows = useMemo(() => {
    return topAuthorsResponse?.data?.rows || []
  }, [topAuthorsResponse])

  // Sorted authors for each tab
  const sortedByViews = useMemo(() => {
    return [...authorRows]
      .sort((a, b) => Number(b.views) - Number(a.views))
      .slice(0, 5)
  }, [authorRows])

  const sortedByPublishing = useMemo(() => {
    return [...authorRows]
      .sort((a, b) => Number(b.published_content) - Number(a.published_content))
      .slice(0, 5)
  }, [authorRows])

  const sortedByViewsPerPost = useMemo(() => {
    return [...authorRows]
      .map((row) => ({
        ...row,
        views_per_post: Number(row.published_content) > 0 ? Number(row.views) / Number(row.published_content) : 0,
      }))
      .sort((a, b) => b.views_per_post - a.views_per_post)
      .slice(0, 5)
  }, [authorRows])

  const sortedByCommentsPerPost = useMemo(() => {
    return [...authorRows]
      .map((row) => ({
        ...row,
        comments_per_post: Number(row.published_content) > 0 ? Number(row.comments) / Number(row.published_content) : 0,
      }))
      .sort((a, b) => b.comments_per_post - a.comments_per_post)
      .slice(0, 5)
  }, [authorRows])

  // Build tabs for top authors widget
  const topAuthorsTabs = useMemo((): TabbedPanelTab[] => {
    const renderAuthorItem = (
      author: AuthorRow & { views_per_post?: number; comments_per_post?: number },
      valueKey: 'views' | 'published_content' | 'views_per_post' | 'comments_per_post',
      valueLabel: string,
      index: number
    ) => {
      const value =
        valueKey === 'views_per_post' || valueKey === 'comments_per_post'
          ? (author[valueKey] ?? 0)
          : Number(author[valueKey]) || 0

      const formattedValue =
        valueKey === 'views_per_post' || valueKey === 'comments_per_post'
          ? formatDecimal(value)
          : formatCompactNumber(value)

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
              <img
                src={author.author_avatar}
                alt={author.author_name || ''}
                className="h-6 w-6 rounded-full object-cover"
              />
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

    const tabs: TabbedPanelTab[] = [
      {
        id: 'views',
        label: __('Views', 'wp-statistics'),
        columnHeaders: {
          left: __('Author', 'wp-statistics'),
          right: __('Views', 'wp-statistics'),
        },
        content:
          sortedByViews.length > 0 ? (
            <div className="flex flex-col gap-3">
              {sortedByViews.map((author, i) =>
                renderAuthorItem(author, 'views', __('views', 'wp-statistics'), i)
              )}
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
          right: postTypeLabel,
        },
        content:
          sortedByPublishing.length > 0 ? (
            <div className="flex flex-col gap-3">
              {sortedByPublishing.map((author, i) =>
                renderAuthorItem(author, 'published_content', postTypeLabel.toLowerCase(), i)
              )}
            </div>
          ) : (
            <EmptyState title={__('No data available', 'wp-statistics')} className="py-6" />
          ),
      },
      {
        id: 'views-per-post',
        label: sprintf(__('Views per %s', 'wp-statistics'), postTypeLabel),
        columnHeaders: {
          left: __('Author', 'wp-statistics'),
          right: sprintf(__('Views/%s', 'wp-statistics'), postTypeLabel),
        },
        content:
          sortedByViewsPerPost.length > 0 ? (
            <div className="flex flex-col gap-3">
              {sortedByViewsPerPost.map((author, i) =>
                renderAuthorItem(
                  author,
                  'views_per_post',
                  sprintf(__('views/%s', 'wp-statistics'), postTypeLabel.toLowerCase()),
                  i
                )
              )}
            </div>
          ) : (
            <EmptyState title={__('No data available', 'wp-statistics')} className="py-6" />
          ),
      },
      {
        id: 'comments-per-post',
        label: sprintf(__('Comments per %s', 'wp-statistics'), postTypeLabel),
        columnHeaders: {
          left: __('Author', 'wp-statistics'),
          right: sprintf(__('Comments/%s', 'wp-statistics'), postTypeLabel),
        },
        content:
          sortedByCommentsPerPost.length > 0 ? (
            <div className="flex flex-col gap-3">
              {sortedByCommentsPerPost.map((author, i) =>
                renderAuthorItem(
                  author,
                  'comments_per_post',
                  sprintf(__('comments/%s', 'wp-statistics'), postTypeLabel.toLowerCase()),
                  i
                )
              )}
            </div>
          ) : (
            <EmptyState title={__('No data available', 'wp-statistics')} className="py-6" />
          ),
      },
    ]

    return tabs
  }, [
    sortedByViews,
    sortedByPublishing,
    sortedByViewsPerPost,
    sortedByCommentsPerPost,
    postTypeLabel,
    isCompareEnabled,
    calcPercentage,
    comparisonDateLabel,
  ])

  return (
    <div className="min-w-0">
      {/* Header */}
      <div className="flex items-center justify-between px-4 py-3 ">
        <h1 className="text-2xl font-semibold text-neutral-800">{__('Authors', 'wp-statistics')}</h1>
        <div className="flex items-center gap-3">
          <div className="hidden lg:flex">
            {filterFields.length > 0 && isInitialized && (
              <FilterButton
                fields={filterFields}
                appliedFilters={filtersForDisplay}
                onApplyFilters={handleAuthorsApplyFilters}
                filterGroup="content"
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
      <OverviewOptionsDrawer {...options} />

      <div className="p-3">
        <NoticeContainer className="mb-2" currentRoute="authors" />

        {showSkeleton || showFullPageLoading ? (
          <div className="grid gap-3 grid-cols-12">
            <div className="col-span-12">
              <PanelSkeleton showTitle={false}>
                <MetricsSkeleton count={6} columns={3} />
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
            {/* Row 1: Authors Metrics */}
            {authorsMetrics.length > 0 && (
              <div className="col-span-12">
                <Panel>
                  <Metrics metrics={authorsMetrics} columns={3} />
                </Panel>
              </div>
            )}
            {/* Row 2: Top Authors Widget */}
            {isWidgetVisible('top-authors') && (
              <div className="col-span-12">
                <TabbedPanel
                  title={__('Top Authors', 'wp-statistics')}
                  tabs={topAuthorsTabs}
                  defaultTab="views"
                />
              </div>
            )}
          </div>
        )}
      </div>
    </div>
  )
}
