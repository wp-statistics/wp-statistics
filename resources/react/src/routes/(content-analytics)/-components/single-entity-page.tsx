/**
 * Shared component for single entity pages (author, category).
 * Reduces code duplication between author_.$authorId.lazy.tsx and category_.$termId.lazy.tsx.
 */

import { keepPreviousData, type UseQueryOptions } from '@tanstack/react-query'
import { useQuery } from '@tanstack/react-query'
import { Link } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { ArrowLeft } from 'lucide-react'
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
import { formatCompactNumber, formatDecimal, formatDuration, getTotalValue } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'

/**
 * Configuration for a metric to display
 */
export interface MetricConfig {
  key: string
  label: string
  format: 'number' | 'percentage' | 'duration'
}

/**
 * Props for SingleEntityPage component
 */
export interface SingleEntityPageProps {
  /** The entity ID (authorId or termId) */
  entityId: string
  /** Type of entity for display and routing */
  entityType: 'author' | 'category'
  /** Function to create query options for fetching data */
  getQueryOptions: (params: {
    entityId: string
    dateFrom: string
    dateTo: string
    compareDateFrom?: string
    compareDateTo?: string
    filters: unknown[]
  }) => UseQueryOptions<unknown, Error, unknown, unknown[]>
  /** Key to extract metrics from batch response (e.g., 'author_metrics' or 'category_metrics') */
  metricsKey: string
  /** Key to extract info from batch response (e.g., 'author_info' or 'category_info') */
  infoKey: string
  /** Configuration for which metrics to display */
  metrics: MetricConfig[]
  /** Number of columns for metrics display */
  metricsColumns?: number
}

/**
 * Generic single entity page component for author and category pages.
 * Handles data fetching, filtering, date ranges, and metric display.
 */
export function SingleEntityPage({
  entityId,
  entityType,
  getQueryOptions,
  metricsKey,
  infoKey,
  metrics: metricConfigs,
  metricsColumns = 3,
}: SingleEntityPageProps) {
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

  // Get filter fields for individual content analytics
  const filterFields = useMemo<FilterField[]>(() => {
    return wp.getFilterFieldsByGroup('individual-content') as FilterField[]
  }, [wp])

  const handleDateRangeUpdate = useCallback(
    (values: { range: DateRange; rangeCompare?: DateRange; period?: string }) => {
      setDateRange(values.range, values.rangeCompare, values.period)
    },
    [setDateRange]
  )

  // Batch query for entity data
  const {
    data: batchResponse,
    isLoading,
    isFetching,
  } = useQuery({
    ...getQueryOptions({
      entityId,
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

  // Extract data from batch response
  const response = batchResponse as { data?: { items?: Record<string, unknown> } } | undefined
  const metricsResponse = response?.data?.items?.[metricsKey] as { totals?: Record<string, unknown> } | undefined
  const infoResponse = response?.data?.items?.[infoKey] as { data?: { rows?: Array<{ page_title?: string }> } } | undefined

  // Get entity info from table response (first row)
  const infoRow = infoResponse?.data?.rows?.[0]
  const defaultName = entityType === 'author' ? __('Author', 'wp-statistics') : __('Category', 'wp-statistics')
  const entityName = infoRow?.page_title || defaultName

  const calcPercentage = usePercentageCalc()
  const { label: comparisonDateLabel } = useComparisonDateLabel()

  // Only show skeleton on initial load (no data yet), not on refetches
  const showSkeleton = isLoading && !batchResponse
  const showFullPageLoading = isFetching && !isLoading

  // Build metrics with visibility filtering
  const entityMetrics = useMemo(() => {
    const totals = metricsResponse?.totals as Record<string, { current?: number; previous?: number } | number> | undefined
    if (!totals) return []

    const result: MetricItem[] = metricConfigs.map((config) => {
      const value = getTotalValue(totals[config.key])
      const prevValue = getTotalValue((totals[config.key] as { previous?: number })?.previous)

      let formattedValue: string
      let formattedPrevValue: string

      switch (config.format) {
        case 'percentage':
          formattedValue = `${formatDecimal(value)}%`
          formattedPrevValue = `${formatDecimal(prevValue)}%`
          break
        case 'duration':
          formattedValue = formatDuration(value)
          formattedPrevValue = formatDuration(prevValue)
          break
        default:
          formattedValue = formatCompactNumber(value)
          formattedPrevValue = formatCompactNumber(prevValue)
      }

      return {
        label: config.label,
        value: formattedValue,
        ...(isCompareEnabled
          ? {
              ...calcPercentage(value, prevValue),
              comparisonDateLabel,
              previousValue: formattedPrevValue,
            }
          : {}),
      }
    })

    return result
  }, [metricsResponse, calcPercentage, isCompareEnabled, comparisonDateLabel, metricConfigs])

  // Determine back link and labels based on entity type
  const backLinkTo = entityType === 'author' ? '/author-pages' : '/category-pages'
  const backLinkLabel =
    entityType === 'author' ? __('Back to Author Pages', 'wp-statistics') : __('Back to Category Pages', 'wp-statistics')
  const noticeRoute = entityType === 'author' ? 'single-author' : 'single-category'

  return (
    <div className="min-w-0">
      {/* Header */}
      <div className="flex items-center justify-between px-4 py-3">
        <div className="flex items-center gap-3">
          <Link
            to={backLinkTo}
            className="p-1.5 -ml-1.5 rounded-md hover:bg-neutral-100 transition-colors"
            aria-label={backLinkLabel}
          >
            <ArrowLeft className="h-5 w-5 text-neutral-500" />
          </Link>
          <h1 className="text-2xl font-semibold text-neutral-800 truncate max-w-[400px]" title={entityName}>
            {showSkeleton ? __('Loading...', 'wp-statistics') : entityName}
          </h1>
        </div>
        <div className="flex items-center gap-3">
          <div className="hidden lg:flex">
            {filterFields.length > 0 && isInitialized && (
              <FilterButton
                fields={filterFields}
                appliedFilters={appliedFilters || []}
                onApplyFilters={handleApplyFilters}
                filterGroup={entityType}
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
        <NoticeContainer className="mb-2" currentRoute={noticeRoute} />

        {showSkeleton || showFullPageLoading ? (
          <div className="grid gap-3 grid-cols-12">
            <div className="col-span-12">
              <PanelSkeleton showTitle={false}>
                <MetricsSkeleton count={metricConfigs.length} columns={metricsColumns} />
              </PanelSkeleton>
            </div>
          </div>
        ) : (
          <div className="grid gap-3 grid-cols-12">
            {/* Metrics Row */}
            {entityMetrics.length > 0 && (
              <div className="col-span-12">
                <Panel>
                  <Metrics metrics={entityMetrics} columns={metricsColumns} />
                </Panel>
              </div>
            )}
          </div>
        )}
      </div>
    </div>
  )
}
