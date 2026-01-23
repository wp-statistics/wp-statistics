import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute, Link } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { ArrowLeft, LockIcon } from 'lucide-react'
import { useCallback, useMemo } from 'react'

import { type DateRange, DateRangePicker } from '@/components/custom/date-range-picker'
import { FilterButton, type FilterField } from '@/components/custom/filter-button'
import { type MetricItem, Metrics } from '@/components/custom/metrics'
import { NoticeContainer } from '@/components/ui/notice-container'
import { Panel } from '@/components/ui/panel'
import { MetricsSkeleton, PanelSkeleton } from '@/components/ui/skeletons'
import { useComparisonDateLabel } from '@/hooks/use-comparison-date-label'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { usePercentageCalc } from '@/hooks/use-percentage-calc'
import { usePremiumFeature } from '@/hooks/use-premium-feature'
import { formatCompactNumber, formatDecimal, formatDuration, getTotalValue } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'
import { getSingleContentQueryOptions } from '@/services/content-analytics/get-single-content'

export const Route = createLazyFileRoute('/(content-analytics)/content_/$postId')({
  component: RouteComponent,
  errorComponent: ({ error }) => (
    <div className="p-6 text-center">
      <h2 className="text-xl font-semibold text-destructive mb-2">{__('Error Loading Page', 'wp-statistics')}</h2>
      <p className="text-muted-foreground">{error.message}</p>
    </div>
  ),
})

/**
 * Locked state for custom post types without premium
 */
function LockedState({ postType }: { postType: string }) {
  return (
    <Panel className="p-8 text-center">
      <div className="max-w-md mx-auto space-y-4">
        <div className="w-16 h-16 mx-auto rounded-full bg-primary/10 flex items-center justify-center">
          <LockIcon className="w-8 h-8 text-primary" strokeWidth={1.5} />
        </div>
        <h2 className="text-lg font-semibold text-neutral-800">
          {__('Custom Post Type Analytics', 'wp-statistics')}
        </h2>
        <p className="text-sm text-muted-foreground">
          {__(
            'View detailed analytics for custom post types like',
            'wp-statistics'
          )}{' '}
          <span className="font-medium">{postType}</span>.{' '}
          {__(
            'Understand how your custom content performs with visitors, views, engagement metrics, and more.',
            'wp-statistics'
          )}
        </p>
        <p className="text-sm text-muted-foreground">
          {__('This feature requires the Premium addon with Custom Post Type Support.', 'wp-statistics')}
        </p>
        <a
          href="https://wp-statistics.com/pricing/?utm_source=plugin&utm_medium=link&utm_campaign=custom-post-type-support"
          target="_blank"
          rel="noopener noreferrer"
          className="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-primary rounded-md hover:bg-primary/90 transition-colors"
        >
          {__('Upgrade to Premium', 'wp-statistics')}
        </a>
      </div>
    </Panel>
  )
}

function RouteComponent() {
  const { postId } = Route.useParams()

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

  // Check if custom post type support is unlocked
  const { isEnabled: isCustomPostTypeEnabled } = usePremiumFeature('custom-post-type-support')

  // Get filter fields for individual content analytics
  // Uses 'individual-content' group which includes: Country, City, Browser, OS,
  // Device Type, Referrer, Referrer Channel, User Role, Logged In, Author
  const filterFields = useMemo<FilterField[]>(() => {
    return wp.getFilterFieldsByGroup('individual-content') as FilterField[]
  }, [wp])

  const handleDateRangeUpdate = useCallback(
    (values: { range: DateRange; rangeCompare?: DateRange; period?: string }) => {
      setDateRange(values.range, values.rangeCompare, values.period)
    },
    [setDateRange]
  )

  // Batch query for single content data
  const {
    data: batchResponse,
    isLoading,
    isFetching,
  } = useQuery({
    ...getSingleContentQueryOptions({
      postId,
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

  // Extract data from batch response (axios returns AxiosResponse with .data property)
  const metricsResponse = batchResponse?.data?.items?.content_metrics
  const postInfoResponse = batchResponse?.data?.items?.post_info

  // Get post info from table response (first row)
  const postInfoRow = postInfoResponse?.data?.rows?.[0]
  const postTitle = postInfoRow?.page_title || __('Content', 'wp-statistics')
  const postType = postInfoRow?.page_type || 'post'

  // Check if this is a custom post type (not 'post' or 'page')
  const isCustomPostType = postType !== 'post' && postType !== 'page'

  // Show locked state for custom post types without premium
  const showLockedState = isCustomPostType && !isCustomPostTypeEnabled

  const calcPercentage = usePercentageCalc()
  const { label: comparisonDateLabel } = useComparisonDateLabel()

  // Only show skeleton on initial load (no data yet), not on refetches
  const showSkeleton = isLoading && !batchResponse
  const showFullPageLoading = isFetching && !isLoading

  // Build metrics with visibility filtering
  const contentMetrics = useMemo(() => {
    const totals = metricsResponse?.totals
    if (!totals) return []

    const visitors = getTotalValue(totals.visitors)
    const views = getTotalValue(totals.views)
    const bounceRate = getTotalValue(totals.bounce_rate)
    const avgTimeOnPage = getTotalValue(totals.avg_time_on_page)
    const entryPage = getTotalValue(totals.entry_page)
    const exitPage = getTotalValue(totals.exit_page)
    const exitRate = getTotalValue(totals.exit_rate)
    const comments = getTotalValue(totals.comments)

    const prevVisitors = getTotalValue(totals.visitors?.previous)
    const prevViews = getTotalValue(totals.views?.previous)
    const prevBounceRate = getTotalValue(totals.bounce_rate?.previous)
    const prevAvgTimeOnPage = getTotalValue(totals.avg_time_on_page?.previous)
    const prevEntryPage = getTotalValue(totals.entry_page?.previous)
    const prevExitPage = getTotalValue(totals.exit_page?.previous)
    const prevExitRate = getTotalValue(totals.exit_rate?.previous)
    const prevComments = getTotalValue(totals.comments?.previous)

    // Build core metrics (always shown)
    const metrics: MetricItem[] = [
      {
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
      {
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
        label: __('Entry Page', 'wp-statistics'),
        value: formatCompactNumber(entryPage),
        ...(isCompareEnabled
          ? {
              ...calcPercentage(entryPage, prevEntryPage),
              comparisonDateLabel,
              previousValue: formatCompactNumber(prevEntryPage),
            }
          : {}),
      },
      {
        label: __('Exit Page', 'wp-statistics'),
        value: formatCompactNumber(exitPage),
        ...(isCompareEnabled
          ? {
              ...calcPercentage(exitPage, prevExitPage),
              comparisonDateLabel,
              previousValue: formatCompactNumber(prevExitPage),
            }
          : {}),
      },
      {
        label: __('Exit Rate', 'wp-statistics'),
        value: `${formatDecimal(exitRate)}%`,
        ...(isCompareEnabled
          ? {
              ...calcPercentage(exitRate, prevExitRate),
              comparisonDateLabel,
              previousValue: `${formatDecimal(prevExitRate)}%`,
            }
          : {}),
      },
      {
        label: __('Comments', 'wp-statistics'),
        value: formatCompactNumber(comments),
        ...(isCompareEnabled
          ? {
              ...calcPercentage(comments, prevComments),
              comparisonDateLabel,
              previousValue: formatCompactNumber(prevComments),
            }
          : {}),
      },
    ]

    return metrics
  }, [metricsResponse, calcPercentage, isCompareEnabled, comparisonDateLabel])

  return (
    <div className="min-w-0">
      {/* Header */}
      <div className="flex items-center justify-between px-4 py-3">
        <div className="flex items-center gap-3">
          <Link
            to="/content"
            className="p-1.5 -ml-1.5 rounded-md hover:bg-neutral-100 transition-colors"
            aria-label={__('Back to Content', 'wp-statistics')}
          >
            <ArrowLeft className="h-5 w-5 text-neutral-500" />
          </Link>
          <h1 className="text-2xl font-semibold text-neutral-800 truncate max-w-[400px]" title={postTitle}>
            {showSkeleton ? __('Loading...', 'wp-statistics') : postTitle}
          </h1>
        </div>
        <div className="flex items-center gap-3">
          <div className="hidden lg:flex">
            {filterFields.length > 0 && isInitialized && (
              <FilterButton
                fields={filterFields}
                appliedFilters={appliedFilters || []}
                onApplyFilters={handleApplyFilters}
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
        </div>
      </div>

      <div className="p-3">
        <NoticeContainer className="mb-2" currentRoute="single-content" />

        {showLockedState ? (
          <LockedState postType={postType} />
        ) : showSkeleton || showFullPageLoading ? (
          <div className="grid gap-3 grid-cols-12">
            <div className="col-span-12">
              <PanelSkeleton showTitle={false}>
                <MetricsSkeleton count={8} columns={4} />
              </PanelSkeleton>
            </div>
          </div>
        ) : (
          <div className="grid gap-3 grid-cols-12">
            {/* Metrics Row */}
            {contentMetrics.length > 0 && (
              <div className="col-span-12">
                <Panel>
                  <Metrics metrics={contentMetrics} columns={4} />
                </Panel>
              </div>
            )}
          </div>
        )}
      </div>
    </div>
  )
}
