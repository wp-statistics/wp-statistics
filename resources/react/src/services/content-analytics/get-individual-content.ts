import { queryOptions } from '@tanstack/react-query'

import type { Filter } from '@/components/custom/filter-bar'
import { type ApiFilters, transformFiltersToApi } from '@/lib/api-filter-transform'
import { clientRequest } from '@/lib/client-request'
import { WordPress } from '@/lib/wordpress'

// Re-export ApiFilters type for backward compatibility
export type { ApiFilters }

// Metric value with current/previous structure
interface MetricValue {
  current: number | string
  previous: number | string
}

// Content metadata
export interface ContentMetadata {
  resource_id: number
  page_title: string
  page_uri: string
  page_type: string
  page_wp_id: number
  post_type: string
  post_type_label: string
  published_date: string | null
  modified_date: string | null
  author_id: number | null
  author_name: string | null
  permalink: string | null
  edit_url: string | null
  terms: Array<{
    term_id: number
    name: string
    taxonomy: string
    taxonomy_label: string
  }>
}

// Individual content metrics response
export interface IndividualContentMetricsResponse {
  success: boolean
  items: Array<Record<string, string | number>>
  totals: {
    visitors: MetricValue
    views: MetricValue
    avg_time_on_page: MetricValue
    bounce_rate: MetricValue
    entry_page: MetricValue
    exit_page: MetricValue
    exit_rate: MetricValue
    comments: MetricValue
  }
  meta: {
    date_from: string
    date_to: string
    cached: boolean
    cache_ttl: number
    preferences: unknown
    compare_from?: string
    compare_to?: string
  }
}

// Chart format response (for traffic trends)
export interface TrafficTrendsChartResponse {
  success: boolean
  labels: string[]
  datasets: Array<{
    key: string
    label: string
    data: (number | string)[]
    comparison?: boolean
  }>
  meta?: Record<string, unknown>
}

// Traffic summary row
export interface TrafficSummaryRow {
  period: string
  period_label: string
  visitors: number | string
  views: number | string
  previous?: {
    visitors: number | string
    views: number | string
  }
}

// Traffic summary response
export interface TrafficSummaryResponse {
  success: boolean
  data: {
    rows: TrafficSummaryRow[]
  }
  meta?: Record<string, unknown>
}

// Top referrer row
export interface TopReferrerRow {
  referrer_domain: string | null
  referrer_name: string | null
  referrer_channel: string | null
  visitors: number | string
  previous?: {
    visitors: number | string
  }
}

// Top country row
export interface TopCountryRow {
  country_code: string
  country_name: string
  visitors: number | string
  previous?: {
    visitors: number | string
  }
}

// Top browser row
export interface TopBrowserRow {
  browser_name: string
  visitors: number | string
  previous?: {
    visitors: number | string
  }
}

// Top OS row
export interface TopOSRow {
  os_name: string
  visitors: number | string
  previous?: {
    visitors: number | string
  }
}

// Top device row
export interface TopDeviceRow {
  device_type_name: string
  visitors: number | string
  previous?: {
    visitors: number | string
  }
}

// Totals value structure
interface TotalsValue {
  current?: number | string
  previous?: number | string
}

// Table format response wrapper
export interface TableQueryResult<T> {
  success: boolean
  data: {
    rows: T[]
    totals?: {
      visitors?: number | string | TotalsValue
      views?: number | string | TotalsValue
      [key: string]: unknown
    }
  }
  meta?: {
    date_from: string
    date_to: string
    page?: number
    per_page?: number
    total_pages?: number
    total_rows?: number
    preferences?: Record<string, unknown> | null
    cached: boolean
    cache_ttl: number
    compare_from?: string
    compare_to?: string
  }
}

// Term item in cached_terms
export interface TermItem {
  term_id: number
  name: string
  slug: string
  taxonomy: string
}

// Content metadata row from table format query
export interface ContentMetadataRow {
  resource_id: number
  page_title: string
  page_uri: string
  page_type: string
  page_wp_id: number
  published_date: string | null
  modified_date: string | null
  author_id: number | null
  author_name: string | null
  post_type_label: string | null
  permalink: string | null
  edit_url: string | null
  cached_terms: TermItem[] | null
}

// Traffic summary period response (flat format)
export interface TrafficSummaryPeriodResponse {
  success: boolean
  items: Array<Record<string, string | number>>
  totals: {
    visitors: MetricValue
    views: MetricValue
  }
  meta?: Record<string, unknown>
}

// Batch response structure for individual content
export interface IndividualContentResponse {
  success: boolean
  items: {
    // Content metadata (table format with single row)
    content_metadata?: TableQueryResult<ContentMetadataRow>
    // Flat format - totals at top level
    content_metrics?: IndividualContentMetricsResponse
    // Traffic summary for multiple periods
    traffic_summary?: TrafficSummaryResponse
    // Traffic summary per period (flat format)
    traffic_summary_today?: TrafficSummaryPeriodResponse
    traffic_summary_yesterday?: TrafficSummaryPeriodResponse
    traffic_summary_last7days?: TrafficSummaryPeriodResponse
    traffic_summary_last28days?: TrafficSummaryPeriodResponse
    traffic_summary_total?: TrafficSummaryPeriodResponse
    // Chart format - labels and datasets at top level
    traffic_trends?: TrafficTrendsChartResponse
    // Table format queries
    top_referrers?: TableQueryResult<TopReferrerRow>
    top_search_engines?: TableQueryResult<TopReferrerRow>
    top_countries?: TableQueryResult<TopCountryRow>
    top_browsers?: TableQueryResult<TopBrowserRow>
    top_os?: TableQueryResult<TopOSRow>
    top_devices?: TableQueryResult<TopDeviceRow>
  }
  errors?: Record<string, { code: string; message: string }>
  skipped?: string[]
  meta?: {
    preferences?: Record<string, unknown>
  }
}

export interface GetIndividualContentParams {
  resourceId: number
  dateFrom: string
  dateTo: string
  compareDateFrom?: string
  compareDateTo?: string
  timeframe?: 'daily' | 'weekly' | 'monthly'
  filters?: Filter[]
}

// Helper to format date to YYYY-MM-DD string
const formatDateStr = (d: Date): string => d.toISOString().split('T')[0]

// Helper to calculate traffic summary date ranges
const getTrafficSummaryDateRanges = () => {
  const today = new Date()
  const yesterday = new Date(today)
  yesterday.setDate(yesterday.getDate() - 1)

  return {
    today: {
      current: { from: formatDateStr(today), to: formatDateStr(today) },
      previous: { from: formatDateStr(yesterday), to: formatDateStr(yesterday) },
    },
    yesterday: {
      current: { from: formatDateStr(yesterday), to: formatDateStr(yesterday) },
      previous: {
        from: formatDateStr(new Date(yesterday.getTime() - 86400000)),
        to: formatDateStr(new Date(yesterday.getTime() - 86400000)),
      },
    },
    last7days: {
      current: {
        from: formatDateStr(new Date(today.getTime() - 6 * 86400000)),
        to: formatDateStr(today),
      },
      previous: {
        from: formatDateStr(new Date(today.getTime() - 13 * 86400000)),
        to: formatDateStr(new Date(today.getTime() - 7 * 86400000)),
      },
    },
    last28days: {
      current: {
        from: formatDateStr(new Date(today.getTime() - 27 * 86400000)),
        to: formatDateStr(today),
      },
      previous: {
        from: formatDateStr(new Date(today.getTime() - 55 * 86400000)),
        to: formatDateStr(new Date(today.getTime() - 28 * 86400000)),
      },
    },
  }
}

export const getIndividualContentQueryOptions = ({
  resourceId,
  dateFrom,
  dateTo,
  compareDateFrom,
  compareDateTo,
  timeframe = 'daily',
  filters = [],
}: GetIndividualContentParams) => {
  // Determine the appropriate date group_by based on timeframe
  const dateGroupBy = timeframe === 'monthly' ? 'month' : timeframe === 'weekly' ? 'week' : 'date'
  // Transform UI filters to API format
  const apiFilters = transformFiltersToApi(filters)
  // Check if compare dates are provided
  const hasCompare = !!(compareDateFrom && compareDateTo)

  // Add resource_id filter
  const resourceIdFilter = {
    resource_id: {
      is: String(resourceId),
    },
  }

  // Merge filters with resource_id filter
  const mergedFilters = {
    ...apiFilters,
    ...resourceIdFilter,
  }

  // Get traffic summary date ranges
  const trafficSummaryDates = getTrafficSummaryDateRanges()

  return queryOptions({
    queryKey: [
      'individual-content',
      resourceId,
      dateFrom,
      dateTo,
      compareDateFrom,
      compareDateTo,
      timeframe,
      apiFilters,
      hasCompare,
      dateGroupBy,
    ],
    queryFn: () =>
      clientRequest.post<IndividualContentResponse>(
        '',
        {
          date_from: dateFrom,
          date_to: dateTo,
          compare: hasCompare,
          ...(hasCompare && {
            previous_date_from: compareDateFrom,
            previous_date_to: compareDateTo,
          }),
          filters: mergedFilters,
          queries: [
            // Content Metadata: Fetch content details (title, author, dates, terms, etc.)
            {
              id: 'content_metadata',
              sources: ['views'],
              group_by: ['page'],
              columns: [
                'resource_id',
                'page_title',
                'page_uri',
                'page_type',
                'page_wp_id',
                'published_date',
                'modified_date',
                'author_id',
                'author_name',
                'post_type_label',
                'permalink',
                'edit_url',
                'cached_terms',
              ],
              per_page: 1,
              format: 'table',
              show_totals: false,
              compare: false,
            },
            // Content Metrics: Flat format for aggregate totals
            {
              id: 'content_metrics',
              sources: [
                'visitors',
                'views',
                'avg_time_on_page',
                'bounce_rate',
                'entry_page',
                'exit_page',
                'exit_rate',
                'comments',
              ],
              group_by: [],
              format: 'flat',
              show_totals: true,
              compare: true,
            },
            // Traffic Trends: Chart format for line chart
            {
              id: 'traffic_trends',
              sources: ['visitors', 'views'],
              group_by: [dateGroupBy],
              format: 'chart',
              show_totals: false,
              compare: true,
            },
            // Traffic Summary: Today (with query-level date overrides)
            {
              id: 'traffic_summary_today',
              sources: ['visitors', 'views'],
              group_by: [],
              format: 'flat',
              show_totals: true,
              compare: true,
              date_from: trafficSummaryDates.today.current.from,
              date_to: trafficSummaryDates.today.current.to,
              previous_date_from: trafficSummaryDates.today.previous.from,
              previous_date_to: trafficSummaryDates.today.previous.to,
            },
            // Traffic Summary: Yesterday
            {
              id: 'traffic_summary_yesterday',
              sources: ['visitors', 'views'],
              group_by: [],
              format: 'flat',
              show_totals: true,
              compare: true,
              date_from: trafficSummaryDates.yesterday.current.from,
              date_to: trafficSummaryDates.yesterday.current.to,
              previous_date_from: trafficSummaryDates.yesterday.previous.from,
              previous_date_to: trafficSummaryDates.yesterday.previous.to,
            },
            // Traffic Summary: Last 7 Days
            {
              id: 'traffic_summary_last7days',
              sources: ['visitors', 'views'],
              group_by: [],
              format: 'flat',
              show_totals: true,
              compare: true,
              date_from: trafficSummaryDates.last7days.current.from,
              date_to: trafficSummaryDates.last7days.current.to,
              previous_date_from: trafficSummaryDates.last7days.previous.from,
              previous_date_to: trafficSummaryDates.last7days.previous.to,
            },
            // Traffic Summary: Last 28 Days
            {
              id: 'traffic_summary_last28days',
              sources: ['visitors', 'views'],
              group_by: [],
              format: 'flat',
              show_totals: true,
              compare: true,
              date_from: trafficSummaryDates.last28days.current.from,
              date_to: trafficSummaryDates.last28days.current.to,
              previous_date_from: trafficSummaryDates.last28days.previous.from,
              previous_date_to: trafficSummaryDates.last28days.previous.to,
            },
            // Traffic Summary: Total (all-time, no comparison)
            {
              id: 'traffic_summary_total',
              sources: ['visitors', 'views'],
              group_by: [],
              format: 'flat',
              show_totals: true,
              compare: false,
            },
            // Top Referrers
            {
              id: 'top_referrers',
              sources: ['visitors'],
              group_by: ['referrer'],
              columns: ['referrer_domain', 'referrer_name', 'referrer_channel', 'visitors'],
              per_page: 5,
              order_by: 'visitors',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: true,
            },
            // Top Search Engines
            {
              id: 'top_search_engines',
              sources: ['visitors'],
              group_by: ['referrer'],
              columns: ['referrer_domain', 'referrer_name', 'referrer_channel', 'visitors'],
              filters: [
                {
                  key: 'referrer_channel',
                  operator: 'is',
                  value: 'search',
                },
              ],
              per_page: 5,
              order_by: 'visitors',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: true,
            },
            // Top Countries
            {
              id: 'top_countries',
              sources: ['visitors'],
              group_by: ['country'],
              columns: ['country_code', 'country_name', 'visitors'],
              per_page: 5,
              order_by: 'visitors',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: true,
            },
            // Top Browsers
            {
              id: 'top_browsers',
              sources: ['visitors'],
              group_by: ['browser'],
              columns: ['browser_name', 'visitors'],
              per_page: 5,
              order_by: 'visitors',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: true,
            },
            // Top Operating Systems
            {
              id: 'top_os',
              sources: ['visitors'],
              group_by: ['os'],
              columns: ['os_name', 'visitors'],
              per_page: 5,
              order_by: 'visitors',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: true,
            },
            // Top Device Categories
            {
              id: 'top_devices',
              sources: ['visitors'],
              group_by: ['device_type'],
              columns: ['device_type_name', 'visitors'],
              per_page: 5,
              order_by: 'visitors',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: true,
            },
          ],
        },
        {
          params: {
            action: WordPress.getInstance().getAnalyticsAction(),
          },
        }
      ),
  })
}
