import { queryOptions } from '@tanstack/react-query'

import type { Filter } from '@/components/custom/filter-bar'
import { transformFiltersToApi } from '@/lib/api-filter-transform'
import { clientRequest } from '@/lib/client-request'
import { WordPress } from '@/lib/wordpress'

export type VisitorType = 'user' | 'ip' | 'hash'

// Visitor info response based on type
export interface UserVisitorInfo {
  user_id: number
  user_login: string
  user_email: string | null
  user_role: string | null
  first_visit: string | null
  last_visit: string | null
  first_visit_formatted?: string
  last_visit_formatted?: string
  total_sessions: number
  total_views: number
  // Location and device info (from most recent session)
  country_code: string | null
  country_name: string | null
  region_name: string | null
  city_name: string | null
  browser_name: string | null
  browser_version: string | null
  os_name: string | null
  device_type_name: string | null
}

export interface IpVisitorInfo {
  ip_address: string
  first_visit: string | null
  last_visit: string | null
  first_visit_formatted?: string
  last_visit_formatted?: string
  total_sessions: number
  total_views: number
  country_code: string | null
  country_name: string | null
  region_name: string | null
  city_name: string | null
  browser_name: string | null
  browser_version: string | null
  os_name: string | null
  device_type_name: string | null
}

export interface HashVisitorInfo {
  visitor_hash: string
  first_visit: string | null
  last_visit: string | null
  first_visit_formatted?: string
  last_visit_formatted?: string
  total_sessions: number
  total_views: number
  browser_name: string | null
  browser_version: string | null
  os_name: string | null
  device_type_name: string | null
  // Hash visitors may not have location data
  country_code: string | null
  country_name: string | null
}

export type VisitorInfo = UserVisitorInfo | IpVisitorInfo | HashVisitorInfo

// Visitor info response (table format)
export interface VisitorInfoResponse {
  success: boolean
  data: {
    rows: VisitorInfo[]
  }
}

// Metric value with current/previous structure
interface MetricValue {
  current: number | string
  previous?: number | string
}

// Totals structure for flat format
interface VisitorMetricsTotals {
  sessions?: MetricValue
  views?: MetricValue
  bounce_rate?: MetricValue
  avg_session_duration?: MetricValue
  pages_per_session?: MetricValue
}

// Visitor metrics response (flat format)
export interface VisitorMetricsResponse {
  success: boolean
  items: Array<Record<string, string | number>>
  totals: VisitorMetricsTotals
  meta: {
    date_from: string
    date_to: string
    cached: boolean
    cache_ttl: number
    compare_from?: string
    compare_to?: string
  }
}

// Chart response for traffic trends
export interface TrafficTrendsChartResponse {
  success: boolean
  data: {
    labels: string[]
    datasets: {
      [key: string]: {
        current: (number | null)[]
        previous?: (number | null)[]
      }
    }
  }
  meta?: {
    date_from: string
    date_to: string
    compare?: boolean
    compare_from?: string
    compare_to?: string
  }
}

// Session row for session history
export interface SessionRow {
  session_id: number
  session_start: string
  session_end: string | null
  session_start_formatted?: string
  session_duration: number
  page_count: number
  entry_page: string | null
  entry_page_title: string | null
  exit_page: string | null
  exit_page_title: string | null
  referrer_domain: string | null
  referrer_name: string | null
  referrer_channel: string | null
}

// Page view row for session journey
export interface PageViewRow {
  page_uri: string
  page_title: string | null
  time_on_page: number | null
  timestamp: string
}

// Table query result structure
export interface TableQueryResult<T> {
  success: boolean
  data: {
    rows: T[]
    totals?: Record<string, number | string>
  }
  meta?: {
    date_from: string
    date_to: string
    page: number
    per_page: number
    total_rows: number
  }
}

// Top page row
export interface TopPageRow {
  page_uri: string
  page_title: string | null
  page_wp_id: number | null
  views: number | string
  previous?: { views?: number }
}

// Referrer row
export interface ReferrerRow {
  referrer_domain: string
  referrer_name?: string
  referrer_channel?: string
  sessions: number | string
  previous?: { sessions?: number }
}

// Batch response structure for single visitor
export interface SingleVisitorResponse {
  success: boolean
  data: {
    items: {
      visitor_info?: VisitorInfoResponse
      visitor_metrics?: VisitorMetricsResponse
      traffic_trends?: TrafficTrendsChartResponse
      sessions?: TableQueryResult<SessionRow>
      top_pages?: TableQueryResult<TopPageRow>
      top_referrers?: TableQueryResult<ReferrerRow>
    }
  }
  errors?: Record<string, { code: string; message: string }>
  skipped?: string[]
  meta?: {
    preferences?: Record<string, unknown>
  }
}

export interface GetSingleVisitorParams {
  type: VisitorType
  id: string
  dateFrom: string
  dateTo: string
  compareDateFrom?: string
  compareDateTo?: string
  filters?: Filter[]
  timeframe?: 'daily' | 'weekly' | 'monthly'
  sessionsPage?: number
  sessionsPerPage?: number
}

/**
 * Build visitor filter based on type
 */
function buildVisitorFilter(type: VisitorType, id: string): Array<{ key: string; operator: string; value: string }> {
  switch (type) {
    case 'user':
      return [{ key: 'user_id', operator: 'is', value: id }]
    case 'ip':
      return [{ key: 'ip', operator: 'is', value: id }]
    case 'hash':
      // Use 'starts_with' because the URL hash is truncated to 6 chars but DB stores full hash
      return [{ key: 'visitor_hash', operator: 'starts_with', value: id }]
  }
}

export const getSingleVisitorQueryOptions = ({
  type,
  id,
  dateFrom,
  dateTo,
  compareDateFrom,
  compareDateTo,
  filters,
  timeframe = 'daily',
  sessionsPage = 1,
  sessionsPerPage = 10,
}: GetSingleVisitorParams) => {
  // Transform UI filters to API format (ensure filters is an array)
  const apiFilters = transformFiltersToApi(filters || [])
  // Check if compare dates are provided
  const hasCompare = !!(compareDateFrom && compareDateTo)

  // Determine group_by based on timeframe
  const chartGroupBy = timeframe === 'daily' ? 'date' : timeframe === 'weekly' ? 'week' : 'month'

  return queryOptions({
    queryKey: [
      'single-visitor',
      type,
      id,
      dateFrom,
      dateTo,
      compareDateFrom,
      compareDateTo,
      apiFilters,
      timeframe,
      sessionsPage,
      sessionsPerPage,
    ],
    queryFn: () => {
      // Visitor filter for queries - created inside queryFn to avoid dependency issues
      const visitorFilter = buildVisitorFilter(type, id)

      // Build columns based on visitor type
      // Available columns from VisitorGroupBy: visitor_hash, first_visit, last_visit,
      // total_sessions, total_views, user_id, user_login, user_email, user_role,
      // ip_address, country_code, country_name, city_name, region_name,
      // device_type_name, os_name, browser_name, browser_version
      const infoColumns =
        type === 'user'
          ? [
              'user_id',
              'user_login',
              'user_email',
              'user_role',
              'first_visit',
              'last_visit',
              'total_sessions',
              'total_views',
              'country_code',
              'country_name',
              'region_name',
              'city_name',
              'browser_name',
              'browser_version',
              'os_name',
              'device_type_name',
            ]
          : type === 'ip'
            ? [
                'ip_address',
                'first_visit',
                'last_visit',
                'total_sessions',
                'total_views',
                'country_code',
                'country_name',
                'region_name',
                'city_name',
                'browser_name',
                'browser_version',
                'os_name',
                'device_type_name',
              ]
            : [
                'visitor_hash',
                'first_visit',
                'last_visit',
                'total_sessions',
                'total_views',
                'browser_name',
                'browser_version',
                'os_name',
                'device_type_name',
                'country_code',
                'country_name',
              ]

      return clientRequest.post<SingleVisitorResponse>(
        '',
        {
          date_from: dateFrom,
          date_to: dateTo,
          compare: hasCompare,
          ...(hasCompare && {
            previous_date_from: compareDateFrom,
            previous_date_to: compareDateTo,
          }),
          ...(Object.keys(apiFilters).length > 0 && { filters: apiFilters }),
          queries: [
            // Visitor Info: Get visitor details - use ALL-TIME date range
            // This ensures first_visit/last_visit show all-time values, not filtered by current date range
            {
              id: 'visitor_info',
              sources: ['visitors'],
              group_by: ['visitor'],
              columns: infoColumns,
              format: 'table',
              per_page: 1,
              order_by: 'last_visit',
              order: 'DESC',
              show_totals: false,
              compare: false,
              filters: visitorFilter,
              date_from: '2000-01-01',
              date_to: '2099-12-31',
            },
            // Visitor Metrics: Flat format for aggregate totals
            {
              id: 'visitor_metrics',
              sources: ['sessions', 'views', 'bounce_rate', 'avg_session_duration', 'pages_per_session'],
              group_by: [],
              format: 'flat',
              show_totals: true,
              compare: true,
              filters: visitorFilter,
            },
            // Traffic Trends: Chart format for activity over time
            {
              id: 'traffic_trends',
              sources: ['sessions', 'views'],
              group_by: [chartGroupBy],
              format: 'chart',
              show_totals: false,
              compare: true,
              filters: visitorFilter,
            },
            // Sessions: Individual session rows using SessionGroupBy
            {
              id: 'sessions',
              sources: ['sessions'],
              group_by: ['session'],
              columns: [
                'session_id',
                'session_start',
                'session_end',
                'session_duration',
                'page_count',
                'entry_page',
                'entry_page_title',
                'exit_page',
                'exit_page_title',
                'referrer_domain',
                'referrer_name',
                'referrer_channel',
              ],
              format: 'table',
              page: sessionsPage,
              per_page: sessionsPerPage,
              order_by: 'session_start',
              order: 'DESC',
              show_totals: false,
              compare: false,
              filters: visitorFilter,
            },
            // Top Pages: 5 items ordered by views
            // Note: Include 'sessions' source to enable user_id filtering
            // Include page_wp_id for client-side deduplication
            {
              id: 'top_pages',
              sources: ['sessions', 'views'],
              group_by: ['page'],
              columns: ['page_uri', 'page_title', 'page_wp_id', 'views'],
              format: 'table',
              per_page: 10,
              order_by: 'views',
              order: 'DESC',
              show_totals: false,
              compare: true,
              filters: visitorFilter,
            },
            // Top Referrers: 5 items ordered by sessions
            {
              id: 'top_referrers',
              sources: ['sessions'],
              group_by: ['referrer'],
              columns: ['referrer_domain', 'referrer_name', 'referrer_channel', 'sessions'],
              format: 'table',
              per_page: 5,
              order_by: 'sessions',
              order: 'DESC',
              show_totals: false,
              compare: true,
              filters: visitorFilter,
            },
          ],
        },
        {
          params: {
            action: WordPress.getInstance().getAnalyticsAction(),
          },
        }
      )
    },
  })
}

/**
 * Separate query for session page views (expandable rows)
 * Called when a session row is expanded
 */
export interface GetSessionPageViewsParams {
  sessionId: number
}

export interface SessionPageViewsResponse {
  success: boolean
  data: {
    rows: PageViewRow[]
  }
}

export const getSessionPageViewsQueryOptions = ({ sessionId }: GetSessionPageViewsParams) => {
  return queryOptions({
    queryKey: ['session-page-views', sessionId],
    queryFn: () =>
      clientRequest.post<SessionPageViewsResponse>(
        '',
        {
          sources: ['views'],
          group_by: ['page_view'],
          columns: ['page_uri', 'page_title', 'time_on_page', 'timestamp'],
          format: 'table',
          per_page: 100,
          order_by: 'timestamp',
          order: 'ASC',
          show_totals: false,
          filters: [{ key: 'session_id', operator: 'is', value: sessionId.toString() }],
          // Use all-time date range since sessions can be from any date
          date_from: '2000-01-01',
          date_to: '2099-12-31',
        },
        {
          params: {
            action: WordPress.getInstance().getAnalyticsAction(),
          },
        }
      ),
    staleTime: 5 * 60 * 1000, // Cache for 5 minutes since session history doesn't change
  })
}
