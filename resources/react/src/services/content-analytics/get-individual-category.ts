import { queryOptions } from '@tanstack/react-query'

import { clientRequest } from '@/lib/client-request'
import { WordPress } from '@/lib/wordpress'

// Metric value with current/previous structure
interface MetricValue {
  current: number | string
  previous: number | string
}

// Term metadata
export interface TermMetadata {
  term_id: number
  term_name: string
  term_slug: string
  taxonomy_type: string
  taxonomy_label: string
  term_link?: string
  term_description?: string
  term_count?: number
}

// Individual category metrics response
export interface IndividualCategoryMetricsResponse {
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

// Chart format response (for categories performance)
export interface CategoriesPerformanceChartResponse {
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

// Top author row
export interface TopAuthorRow {
  author_id: number
  author_name: string
  author_avatar?: string
  views: number | string
  visitors: number | string
  published_content: number | string
  previous?: {
    views: number | string
    visitors: number | string
    published_content: number | string
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

// Batch response structure for individual category
export interface IndividualCategoryResponse {
  success: boolean
  items: {
    // Term metadata (table format with single row)
    term_metadata?: TableQueryResult<TermMetadata>
    // Flat format - totals at top level
    category_metrics?: IndividualCategoryMetricsResponse
    // Traffic summary per period (flat format)
    traffic_summary_today?: TrafficSummaryPeriodResponse
    traffic_summary_yesterday?: TrafficSummaryPeriodResponse
    traffic_summary_last7days?: TrafficSummaryPeriodResponse
    traffic_summary_last28days?: TrafficSummaryPeriodResponse
    traffic_summary_total?: TrafficSummaryPeriodResponse
    // Chart format - labels and datasets at top level
    categories_performance?: CategoriesPerformanceChartResponse
    // Table format queries
    top_content_popular?: TableQueryResult<TopContentRow>
    top_content_recent?: TableQueryResult<TopContentRow>
    top_content_commented?: TableQueryResult<TopContentRow>
    top_authors_views?: TableQueryResult<TopAuthorRow>
    top_authors_publishing?: TableQueryResult<TopAuthorRow>
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

export interface GetIndividualCategoryParams {
  termId: number
  dateFrom: string
  dateTo: string
  compareDateFrom?: string
  compareDateTo?: string
  timeframe?: 'daily' | 'weekly' | 'monthly'
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

export const getIndividualCategoryQueryOptions = ({
  termId,
  dateFrom,
  dateTo,
  compareDateFrom,
  compareDateTo,
  timeframe = 'daily',
}: GetIndividualCategoryParams) => {
  // Determine the appropriate date group_by based on timeframe
  const dateGroupBy = timeframe === 'monthly' ? 'month' : timeframe === 'weekly' ? 'week' : 'date'
  // Check if compare dates are provided
  const hasCompare = !!(compareDateFrom && compareDateTo)

  // Term filter - filter by specific term_id
  const termFilter = {
    taxonomy: {
      is: String(termId),
    },
  }

  // Get traffic summary date ranges
  const trafficSummaryDates = getTrafficSummaryDateRanges()

  return queryOptions({
    queryKey: [
      'individual-category',
      termId,
      dateFrom,
      dateTo,
      compareDateFrom,
      compareDateTo,
      timeframe,
      hasCompare,
      dateGroupBy,
    ],
    queryFn: () =>
      clientRequest.post<IndividualCategoryResponse>(
        '',
        {
          date_from: dateFrom,
          date_to: dateTo,
          compare: hasCompare,
          ...(hasCompare && {
            previous_date_from: compareDateFrom,
            previous_date_to: compareDateTo,
          }),
          filters: termFilter,
          queries: [
            // Term Metadata: Fetch term details
            {
              id: 'term_metadata',
              sources: ['views'],
              group_by: ['taxonomy'],
              columns: [
                'term_id',
                'term_name',
                'term_slug',
                'taxonomy_type',
                'taxonomy_label',
                'term_link',
                'term_description',
                'term_count',
              ],
              per_page: 1,
              format: 'table',
              show_totals: false,
              compare: false,
            },
            // Category Metrics: Flat format for aggregate totals
            {
              id: 'category_metrics',
              sources: ['visitors', 'views', 'published_content', 'bounce_rate', 'avg_time_on_page', 'comments'],
              group_by: [],
              format: 'flat',
              show_totals: true,
              compare: true,
            },
            // Traffic Summary - Today
            {
              id: 'traffic_summary_today',
              sources: ['visitors', 'views'],
              group_by: [],
              date_from: trafficSummaryDates.today.current.from,
              date_to: trafficSummaryDates.today.current.to,
              previous_date_from: trafficSummaryDates.today.previous.from,
              previous_date_to: trafficSummaryDates.today.previous.to,
              format: 'flat',
              show_totals: true,
              compare: true,
            },
            // Traffic Summary - Yesterday
            {
              id: 'traffic_summary_yesterday',
              sources: ['visitors', 'views'],
              group_by: [],
              date_from: trafficSummaryDates.yesterday.current.from,
              date_to: trafficSummaryDates.yesterday.current.to,
              previous_date_from: trafficSummaryDates.yesterday.previous.from,
              previous_date_to: trafficSummaryDates.yesterday.previous.to,
              format: 'flat',
              show_totals: true,
              compare: true,
            },
            // Traffic Summary - Last 7 Days
            {
              id: 'traffic_summary_last7days',
              sources: ['visitors', 'views'],
              group_by: [],
              date_from: trafficSummaryDates.last7days.current.from,
              date_to: trafficSummaryDates.last7days.current.to,
              previous_date_from: trafficSummaryDates.last7days.previous.from,
              previous_date_to: trafficSummaryDates.last7days.previous.to,
              format: 'flat',
              show_totals: true,
              compare: true,
            },
            // Traffic Summary - Last 28 Days
            {
              id: 'traffic_summary_last28days',
              sources: ['visitors', 'views'],
              group_by: [],
              date_from: trafficSummaryDates.last28days.current.from,
              date_to: trafficSummaryDates.last28days.current.to,
              previous_date_from: trafficSummaryDates.last28days.previous.from,
              previous_date_to: trafficSummaryDates.last28days.previous.to,
              format: 'flat',
              show_totals: true,
              compare: true,
            },
            // Traffic Summary - Total (all time, no date filter)
            {
              id: 'traffic_summary_total',
              sources: ['visitors', 'views'],
              group_by: [],
              date_from: null,
              date_to: null,
              format: 'flat',
              show_totals: true,
              compare: false,
            },
            // Categories Performance: Chart format for line chart
            {
              id: 'categories_performance',
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
              columns: [
                'resource_id',
                'page_title',
                'page_uri',
                'page_type',
                'page_wp_id',
                'views',
                'visitors',
                'published_date',
                'thumbnail_url',
                'comments',
              ],
              per_page: 5,
              order_by: 'views',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: true,
            },
            // Top Content - Most Commented (by comments)
            {
              id: 'top_content_commented',
              sources: ['views', 'visitors', 'comments'],
              group_by: ['page'],
              columns: [
                'resource_id',
                'page_title',
                'page_uri',
                'page_type',
                'page_wp_id',
                'views',
                'visitors',
                'published_date',
                'thumbnail_url',
                'comments',
              ],
              per_page: 5,
              order_by: 'comments',
              order: 'DESC',
              format: 'table',
              show_totals: false,
              compare: false,
            },
            // Top Content - Most Recent (by published date)
            {
              id: 'top_content_recent',
              sources: ['views', 'visitors'],
              group_by: ['page'],
              columns: [
                'resource_id',
                'page_title',
                'page_uri',
                'page_type',
                'page_wp_id',
                'views',
                'visitors',
                'published_date',
                'thumbnail_url',
                'comments',
              ],
              per_page: 5,
              order_by: 'published_date',
              order: 'DESC',
              format: 'table',
              show_totals: false,
              compare: false,
            },
            // Top Authors - By Views
            {
              id: 'top_authors_views',
              sources: ['views', 'visitors', 'published_content'],
              group_by: ['author'],
              per_page: 5,
              order_by: 'views',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: true,
            },
            // Top Authors - By Publishing
            {
              id: 'top_authors_publishing',
              sources: ['views', 'visitors', 'published_content'],
              group_by: ['author'],
              per_page: 5,
              order_by: 'published_content',
              order: 'DESC',
              format: 'table',
              show_totals: false,
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
              filters: [{ key: 'referrer_channel', operator: 'is', value: 'search' }],
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
