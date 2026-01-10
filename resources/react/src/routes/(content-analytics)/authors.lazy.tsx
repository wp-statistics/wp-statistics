import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { useCallback, useEffect, useMemo, useState } from 'react'

import { type DateRange, DateRangePicker } from '@/components/custom/date-range-picker'
import { FilterBar } from '@/components/custom/filter-bar'
import { FilterButton, type FilterField } from '@/components/custom/filter-button'
import { type MetricItem, Metrics } from '@/components/custom/metrics'
import { TabbedList, type TabbedListTab } from '@/components/custom/tabbed-list'
import { NoticeContainer } from '@/components/ui/notice-container'
import { Panel } from '@/components/ui/panel'
import { BarListSkeleton, MetricsSkeleton, PanelSkeleton } from '@/components/ui/skeletons'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { usePercentageCalc } from '@/hooks/use-percentage-calc'
import { formatCompactNumber, formatDecimal } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'
import { getAuthorsOverviewQueryOptions } from '@/services/content-analytics/get-authors-overview'

export const Route = createLazyFileRoute('/(content-analytics)/authors')({
  component: AuthorsOverviewView,
  errorComponent: ({ error }) => (
    <div className="p-6 text-center">
      <h2 className="text-xl font-semibold text-destructive mb-2">{__('Error Loading Page', 'wp-statistics')}</h2>
      <p className="text-muted-foreground">{error.message}</p>
    </div>
  ),
})

/**
 * Authors Overview View - Main authors analytics page
 */
function AuthorsOverviewView() {
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

  const [activeTab, setActiveTab] = useState<string>('views')
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

  const showSkeleton = isLoading && !batchResponse
  const showFullPageLoading = isFetching && !isLoading

  // Extract data from batch response
  const metricsResponse = batchResponse?.data?.items?.authors_metrics

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
  const authorsMetrics = useMemo(() => {
    const totals = metricsResponse?.totals
    if (!totals) return []

    const publishedContent = Number(totals.published_content?.current) || 0
    const activeAuthors = Number(totals.active_authors?.current) || 0
    const visitors = Number(totals.visitors?.current) || 0
    const views = Number(totals.views?.current) || 0

    const prevPublishedContent = Number(totals.published_content?.previous) || 0
    const prevActiveAuthors = Number(totals.active_authors?.previous) || 0
    const prevVisitors = Number(totals.visitors?.previous) || 0
    const prevViews = Number(totals.views?.previous) || 0

    const viewsPerAuthor = activeAuthors > 0 ? views / activeAuthors : 0
    const prevViewsPerAuthor = prevActiveAuthors > 0 ? prevViews / prevActiveAuthors : 0

    const avgContentPerAuthor = activeAuthors > 0 ? publishedContent / activeAuthors : 0
    const prevAvgContentPerAuthor = prevActiveAuthors > 0 ? prevPublishedContent / prevActiveAuthors : 0

    const metrics: MetricItem[] = [
      {
        label: `${__('Published', 'wp-statistics')} ${postTypeLabel}`,
        value: formatCompactNumber(publishedContent),
        ...calcPercentage(publishedContent, prevPublishedContent),
        tooltipContent: __('Number of published content items', 'wp-statistics'),
      },
      {
        label: __('Active Authors', 'wp-statistics'),
        value: formatCompactNumber(activeAuthors),
        ...calcPercentage(activeAuthors, prevActiveAuthors),
        tooltipContent: __('Authors who published content in this period', 'wp-statistics'),
      },
      {
        label: __('Visitors', 'wp-statistics'),
        value: formatCompactNumber(visitors),
        ...calcPercentage(visitors, prevVisitors),
        tooltipContent: __('Unique visitors to author content', 'wp-statistics'),
      },
      {
        label: __('Views', 'wp-statistics'),
        value: formatCompactNumber(views),
        ...calcPercentage(views, prevViews),
        tooltipContent: __('Total page views', 'wp-statistics'),
      },
      {
        label: __('Views per Author', 'wp-statistics'),
        value: formatDecimal(viewsPerAuthor),
        ...calcPercentage(viewsPerAuthor, prevViewsPerAuthor),
        tooltipContent: __('Average views per author', 'wp-statistics'),
      },
      {
        label: `${__('Avg.', 'wp-statistics')} ${postTypeLabel} ${__('per Author', 'wp-statistics')}`,
        value: formatDecimal(avgContentPerAuthor),
        ...calcPercentage(avgContentPerAuthor, prevAvgContentPerAuthor),
        tooltipContent: __('Average content items per author', 'wp-statistics'),
      },
    ]

    return metrics
  }, [metricsResponse, postTypeLabel, calcPercentage])

  // Build tabbed list tabs for top authors
  const topAuthorsTabs: TabbedListTab[] = useMemo(() => {
    const topAuthorsViews = batchResponse?.data?.items?.top_authors_views?.data?.rows || []
    const topAuthorsPublishing = batchResponse?.data?.items?.top_authors_publishing?.data?.rows || []
    const topAuthorsViewsPerContent = batchResponse?.data?.items?.top_authors_views_per_content?.data?.rows || []
    const topAuthorsCommentsPerContent = batchResponse?.data?.items?.top_authors_comments_per_content?.data?.rows || []

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
          title: __('See all authors', 'wp-statistics'),
          href: '/top-authors',
        },
      },
      {
        id: 'publishing',
        label: __('Publishing', 'wp-statistics'),
        items: topAuthorsPublishing.map((item) => ({
          id: String(item.author_id),
          title: item.author_name || __('Unknown Author', 'wp-statistics'),
          subtitle: `${formatCompactNumber(Number(item.published_content))} ${postTypeLabel.toLowerCase()}`,
          thumbnail: item.author_avatar || `${pluginUrl}public/images/placeholder.png`,
          href: `/individual-author?author_id=${item.author_id}`,
        })),
        link: {
          title: __('See all authors', 'wp-statistics'),
          href: '/top-authors',
        },
      },
      {
        id: 'views_per_content',
        label: `${__('Views per', 'wp-statistics')} ${postTypeLabel}`,
        items: [...topAuthorsViewsPerContent]
          .map((item) => ({
            ...item,
            viewsPerContent:
              Number(item.published_content) > 0 ? Number(item.views) / Number(item.published_content) : 0,
          }))
          .sort((a, b) => b.viewsPerContent - a.viewsPerContent)
          .slice(0, 5)
          .map((item) => ({
            id: String(item.author_id),
            title: item.author_name || __('Unknown Author', 'wp-statistics'),
            subtitle: `${formatDecimal(item.viewsPerContent)} ${__('views', 'wp-statistics')}/${postTypeLabel.toLowerCase()}`,
            thumbnail: item.author_avatar || `${pluginUrl}public/images/placeholder.png`,
            href: `/individual-author?author_id=${item.author_id}`,
          })),
        link: {
          title: __('See all authors', 'wp-statistics'),
          href: '/top-authors',
        },
      },
    ]

    // Add Comments per Content tab only if there's data (comments are enabled)
    if (topAuthorsCommentsPerContent.length > 0) {
      tabs.push({
        id: 'comments_per_content',
        label: `${__('Comments per', 'wp-statistics')} ${postTypeLabel}`,
        items: [...topAuthorsCommentsPerContent]
          .map((item) => ({
            ...item,
            commentsPerContent:
              Number(item.published_content) > 0 ? Number(item.comments || 0) / Number(item.published_content) : 0,
          }))
          .sort((a, b) => b.commentsPerContent - a.commentsPerContent)
          .slice(0, 5)
          .map((item) => ({
            id: String(item.author_id),
            title: item.author_name || __('Unknown Author', 'wp-statistics'),
            subtitle: `${formatDecimal(item.commentsPerContent)} ${__('comments', 'wp-statistics')}/${postTypeLabel.toLowerCase()}`,
            thumbnail: item.author_avatar || `${pluginUrl}public/images/placeholder.png`,
            href: `/individual-author?author_id=${item.author_id}`,
          })),
        link: {
          title: __('See all authors', 'wp-statistics'),
          href: '/top-authors',
        },
      })
    }

    return tabs
  }, [batchResponse, postTypeLabel, pluginUrl])

  return (
    <div className="min-w-0">
      {/* Header */}
      <div className="flex items-center justify-between px-4 py-3 ">
        <h1 className="text-2xl font-semibold text-neutral-800">{__('Authors', 'wp-statistics')}</h1>
        <div className="flex items-center gap-3">
          {filterFields.length > 0 && isInitialized && (
            <FilterButton
              fields={filterFields}
              appliedFilters={filtersForDisplay}
              onApplyFilters={handleAuthorsApplyFilters}
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
        <NoticeContainer className="mb-2" currentRoute="authors" />
        {filtersForDisplay.length > 0 && (
          <FilterBar
            filters={filtersForDisplay}
            onRemoveFilter={(filterId) => {
              // If removing the default post_type filter, clear it by setting a flag
              if (filterId === 'post_type-authors-default') {
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
            <div className="col-span-12">
              <Panel>
                <Metrics metrics={authorsMetrics} columns={3} />
              </Panel>
            </div>

            {/* Row 2: Top Authors Tabbed List */}
            <div className="col-span-12">
              <TabbedList
                title={__('Top Authors', 'wp-statistics')}
                tabs={topAuthorsTabs}
                activeTab={activeTab}
                onTabChange={setActiveTab}
                emptyMessage={__('No authors available for the selected period', 'wp-statistics')}
              />
            </div>
          </div>
        )}
      </div>
    </div>
  )
}
