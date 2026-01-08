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

// Author metadata
export interface AuthorMetadata {
  author_id: number
  author_name: string
  author_avatar: string | null
  author_email: string | null
  author_role: string | null
  author_registered: string | null
  author_posts_url: string | null
  author_profile_url: string | null
}

// Individual author metrics response
export interface IndividualAuthorMetricsResponse {
  success: boolean
  items: Array<Record<string, string | number>>
  totals: {
    visitors: MetricValue
    views: MetricValue
    published_content: MetricValue
    bounce_rate: MetricValue
    avg_time_on_page: MetricValue
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

// Chart format response (for content performance)
export interface ContentPerformanceChartResponse {
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

// Top content row
export interface TopContentRow {
  resource_id: number
  page_title: string
  page_uri: string
  thumbnail_url: string | null
  views: number | string
  visitors: number | string
  published_date: string | null
  comments: number | string
  previous?: {
    views: number | string
    visitors: number | string
  }
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

// Batch response structure for individual author
export interface IndividualAuthorResponse {
  success: boolean
  items: {
    // Author metadata (table format with single row)
    author_metadata?: TableQueryResult<AuthorMetadata>
    // Flat format - totals at top level
    author_metrics?: IndividualAuthorMetricsResponse
    // Traffic summary per period (flat format)
    traffic_summary_today?: TrafficSummaryPeriodResponse
    traffic_summary_yesterday?: TrafficSummaryPeriodResponse
    traffic_summary_last7days?: TrafficSummaryPeriodResponse
    traffic_summary_last28days?: TrafficSummaryPeriodResponse
    traffic_summary_total?: TrafficSummaryPeriodResponse
    // Chart format - labels and datasets at top level
    content_performance?: ContentPerformanceChartResponse
    // Table format queries
    top_content_popular?: TableQueryResult<TopContentRow>
    top_content_recent?: TableQueryResult<TopContentRow>
    top_content_commented?: TableQueryResult<TopContentRow>
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

export interface GetIndividualAuthorParams {
  authorId: number
  dateFrom: string
  dateTo: string
  compareDateFrom?: string
  compareDateTo?: string
  timeframe?: 'daily' | 'weekly' | 'monthly'
  postType?: string
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

export const getIndividualAuthorQueryOptions = ({
  authorId,
  dateFrom,
  dateTo,
  compareDateFrom,
  compareDateTo,
  timeframe = 'daily',
  postType,
  filters = [],
}: GetIndividualAuthorParams) => {
  // Determine the appropriate date group_by based on timeframe
  const dateGroupBy = timeframe === 'monthly' ? 'month' : timeframe === 'weekly' ? 'week' : 'date'
  // Transform UI filters to API format
  const apiFilters = transformFiltersToApi(filters)
  // Check if compare dates are provided
  const hasCompare = !!(compareDateFrom && compareDateTo)

  // Add author filter
  const authorFilter = {
    author: {
      is: String(authorId),
    },
  }

  // Add post_type filter if provided
  const postTypeFilter = postType
    ? {
        post_type: {
          is: postType,
        },
      }
    : {}

  // Merge filters - user's apiFilters override page defaults (author, post_type)
  const mergedFilters = {
    ...authorFilter,
    ...postTypeFilter,
    ...apiFilters,
  }

  // Get traffic summary date ranges
  const trafficSummaryDates = getTrafficSummaryDateRanges()

  return queryOptions({
    // eslint-disable-next-line @tanstack/query/exhaustive-deps -- trafficSummaryDates is computed from current date and mergedFilters is derived from authorId/postType/apiFilters
    queryKey: [
      'individual-author',
      authorId,
      dateFrom,
      dateTo,
      compareDateFrom,
      compareDateTo,
      timeframe,
      postType,
      apiFilters,
      hasCompare,
      dateGroupBy,
    ],
    queryFn: () =>
      clientRequest.post<IndividualAuthorResponse>(
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
            // Author Metadata: Fetch author details
            {
              id: 'author_metadata',
              sources: ['views'],
              group_by: ['author'],
              columns: [
                'author_id',
                'author_name',
                'author_avatar',
                'author_email',
                'author_role',
                'author_registered',
                'author_posts_url',
                'author_profile_url',
              ],
              per_page: 1,
              format: 'table',
              show_totals: false,
              compare: false,
            },
            // Author Metrics: Flat format for aggregate totals
            {
              id: 'author_metrics',
              sources: ['visitors', 'views', 'published_content', 'bounce_rate', 'avg_time_on_page', 'comments'],
              group_by: [],
              format: 'flat',
              show_totals: true,
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
            // Content Performance: Chart format for line chart
            {
              id: 'content_performance',
              sources: ['visitors', 'views', 'published_content'],
              group_by: [dateGroupBy],
              format: 'chart',
              show_totals: false,
              compare: true,
            },
            // Top Content - Most Popular (by views)
            {
              id: 'top_content_popular',
              sources: ['views', 'visitors'],
              group_by: ['page'],
              columns: ['resource_id', 'page_title', 'page_uri', 'thumbnail_url', 'views', 'visitors'],
              per_page: 5,
              order_by: 'views',
              order: 'DESC',
              format: 'table',
              show_totals: false,
              compare: true,
            },
            // Top Content - Most Recent
            {
              id: 'top_content_recent',
              sources: ['views', 'visitors'],
              group_by: ['page'],
              columns: ['resource_id', 'page_title', 'page_uri', 'thumbnail_url', 'views', 'visitors', 'published_date'],
              per_page: 5,
              order_by: 'published_date',
              order: 'DESC',
              format: 'table',
              show_totals: false,
              compare: false,
            },
            // Top Content - Most Commented
            {
              id: 'top_content_commented',
              sources: ['views', 'visitors', 'comments'],
              group_by: ['page'],
              columns: ['resource_id', 'page_title', 'page_uri', 'thumbnail_url', 'views', 'visitors', 'comments'],
              per_page: 5,
              order_by: 'comments',
              order: 'DESC',
              format: 'table',
              show_totals: false,
              compare: false,
            },
            // Top Referrers
            // Note: 'views' source is required for author filter to work
            {
              id: 'top_referrers',
              sources: ['visitors', 'views'],
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
            // Note: 'views' source is required for author filter to work
            {
              id: 'top_search_engines',
              sources: ['visitors', 'views'],
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
            // Note: 'views' source is required for author filter to work
            {
              id: 'top_countries',
              sources: ['visitors', 'views'],
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
            // Note: 'views' source is required for author filter to work
            {
              id: 'top_browsers',
              sources: ['visitors', 'views'],
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
            // Note: 'views' source is required for author filter to work
            {
              id: 'top_os',
              sources: ['visitors', 'views'],
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
            // Note: 'views' source is required for author filter to work
            {
              id: 'top_devices',
              sources: ['visitors', 'views'],
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
