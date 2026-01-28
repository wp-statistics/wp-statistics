import { queryOptions } from '@tanstack/react-query'

import type { Filter } from '@/components/custom/filter-bar'
import { type ApiFilters,transformFiltersToApi } from '@/lib/api-filter-transform'
import { clientRequest } from '@/lib/client-request'
import { WordPress } from '@/lib/wordpress'

// Re-export ApiFilters type for backward compatibility
export type { ApiFilters }

// Response types for each query in the batch

// Metric value with current/previous structure
interface MetricValue {
  current: number | string
  previous: number | string
}

// Flat format response (for metrics query without group_by)
// Flat format: { success, items: [...], totals: { metric: { current, previous } }, meta: {...} }
export interface MetricsResponse {
  success: boolean
  items: Array<Record<string, string | number>>
  totals: {
    visitors: MetricValue
    views: MetricValue
    sessions: MetricValue
    avg_session_duration: MetricValue
    pages_per_session: MetricValue
    bounce_rate: MetricValue
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
// Chart format: { success, labels: [...], previousLabels?: [...], datasets: [{key, label, data, comparison?}], meta: {...} }
// Note: Previous period data comes as separate datasets with key like "visitors_previous" and comparison: true
export interface TrafficTrendsChartResponse {
  success: boolean
  labels: string[]
  previousLabels?: string[] // Previous period date labels (for index-based alignment when periods differ in length)
  datasets: Array<{
    key: string
    label: string
    data: (number | string)[]
    comparison?: boolean // true for previous period datasets
  }>
  meta?: Record<string, unknown>
}

// Table format row types
export interface TopCountryRow {
  country_code: string
  country_name: string
  visitors: number | string
  previous?: {
    visitors: number | string
  }
}

export interface DeviceTypeRow {
  device_type_name: string
  visitors: number | string
  previous?: {
    visitors: number | string
  }
}

export interface OperatingSystemRow {
  os_name: string
  visitors: number | string
  previous?: {
    visitors: number | string
  }
}

export interface TopVisitorRow {
  visitor_id: string
  visitor_hash: string
  ip_address: string | null
  user_id: number | null
  user_login: string | null
  user_email: string | null
  user_role: string | null
  total_views: number
  country_code: string | null
  country_name: string | null
  region_name: string | null
  city_name: string | null
  os_name: string | null
  browser_name: string | null
  browser_version: string | null
  device_type_name: string | null
  referrer_domain: string | null
  referrer_channel: string | null
  entry_page: string | null
  entry_page_title: string | null
  entry_page_type?: string | null
  entry_page_wp_id?: number | null
  exit_page: string | null
  exit_page_title: string | null
  exit_page_type?: string | null
  exit_page_wp_id?: number | null
}

// Export type alias for TopVisitorsData (used by overview-top-visitors component)
export interface TopVisitorsData {
  rows: TopVisitorRow[]
}

export interface TopEntryPageRow {
  page_uri: string
  page_title: string
  page_type: string
  views: string
  visitors: string
  bounce_rate: string
}

export interface TopReferrerRow {
  referrer_domain: string | null
  referrer_name: string | null
  referrer_channel: string | null
  visitors: number | string
  previous?: {
    visitors: number | string
  }
}

export interface CountriesMapRow {
  country_code: string
  country_name: string
  visitors: number | string
  views: number | string
}

// Table format response wrapper
// Table format: { success, data: { rows: [...], totals: {...} }, meta: {...} }
export interface TableQueryResult<T> {
  success: boolean
  data: {
    rows: T[]
    totals?: Record<string, unknown>
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

// Batch response structure
// Batch response: { success, items: { queryId: result, ... }, errors, skipped, meta }
export interface VisitorOverviewResponse {
  success: boolean
  items: {
    // Flat format - totals at top level
    metrics?: MetricsResponse
    metrics_top_country: {
      success: boolean
      items: [{ country_name: string; visitors: number }]
      totals: Record<string, never>
    }
    metrics_top_referrer: {
      success: boolean
      items: [{ referrer_name: string; visitors: number }]
      totals: Record<string, never>
    }
    metrics_top_search: {
      success: boolean
      items: [{ search_term: string; searches: number }]
      totals: Record<string, never>
    }
    metrics_logged_in: {
      success: boolean
      items: []
      totals: {
        visitors: { current: number; previous: number }
      }
    }
    // Chart format - labels and datasets at top level
    traffic_trends?: TrafficTrendsChartResponse
    // Table format - data.rows structure
    top_countries?: TableQueryResult<TopCountryRow>
    device_type?: TableQueryResult<DeviceTypeRow>
    operating_systems?: TableQueryResult<OperatingSystemRow>
    top_visitors?: TableQueryResult<TopVisitorRow>
    top_entry_pages?: TableQueryResult<TopEntryPageRow>
    top_referrers?: TableQueryResult<TopReferrerRow>
    // Countries map for GlobalMap component
    countries_map?: TableQueryResult<CountriesMapRow>
  }
  errors?: Record<string, { code: string; message: string }>
  skipped?: string[]
  meta?: {
    preferences?: Record<string, unknown>
  }
}

export interface GetVisitorOverviewParams {
  dateFrom: string
  dateTo: string
  compareDateFrom?: string
  compareDateTo?: string
  timeframe?: 'daily' | 'weekly' | 'monthly'
  filters?: Filter[]
}

export const getVisitorOverviewQueryOptions = ({
  dateFrom,
  dateTo,
  compareDateFrom,
  compareDateTo,
  timeframe = 'daily',
  filters = [],
}: GetVisitorOverviewParams) => {
  // Determine the appropriate date group_by based on timeframe
  const dateGroupBy = timeframe === 'monthly' ? 'month' : timeframe === 'weekly' ? 'week' : 'date'
  // Transform UI filters to API format
  const apiFilters = transformFiltersToApi(filters)
  // Check if compare dates are provided (must be boolean, not the date value)
  const hasCompare = !!(compareDateFrom && compareDateTo)

  return queryOptions({
    queryKey: ['visitor-overview', dateFrom, dateTo, compareDateFrom, compareDateTo, timeframe, apiFilters, hasCompare, dateGroupBy],
    queryFn: () =>
      clientRequest.post<VisitorOverviewResponse>(
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
            // Metrics: Flat format for aggregate totals
            {
              id: 'metrics',
              sources: ['visitors', 'views', 'sessions', 'avg_session_duration', 'bounce_rate', 'pages_per_session'],
              group_by: [],
              format: 'flat',
              show_totals: true,
              compare: true,
            },
            // Top Country Query
            {
              id: 'metrics_top_country',
              sources: ['visitors'],
              group_by: ['country'],
              columns: ['country_name', 'visitors'],
              per_page: 1,
              order_by: 'visitors',
              order: 'DESC',
              format: 'flat',
              show_totals: false,
              compare: false,
            },
            // Top Referrer Query
            {
              id: 'metrics_top_referrer',
              sources: ['visitors'],
              group_by: ['referrer'],
              columns: ['referrer_name', 'visitors'],
              per_page: 1,
              order_by: 'visitors',
              order: 'DESC',
              format: 'flat',
              show_totals: false,
              compare: false,
            },
            // Top Searche Query
            {
              id: 'metrics_top_search',
              sources: ['searches'],
              group_by: ['search_term'],
              columns: ['search_term', 'searches'],
              per_page: 1,
              order_by: 'searches',
              order: 'DESC',
              format: 'flat',
              show_totals: false,
              compare: false,
            },
            // Logged-in visistor query
            {
              id: 'metrics_logged_in',
              sources: ['visitors'],
              group_by: [],
              filters: [
                {
                  key: 'logged_in',
                  operator: 'is',
                  value: '1',
                },
              ],
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
            // Top Countries: Table format with comparison
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
            // Device Type: Table format with comparison
            {
              id: 'device_type',
              sources: ['visitors'],
              group_by: ['device_type'],
              columns: ['device_type_name', 'device_type_id', 'visitors'],
              per_page: 5,
              order_by: 'visitors',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: true,
            },
            // Operating Systems: Table format with comparison
            {
              id: 'operating_systems',
              sources: ['visitors'],
              group_by: ['os'],
              columns: ['os_name', 'os_id', 'visitors'],
              per_page: 5,
              order_by: 'visitors',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: true,
            },
            // Top Visitors: Table format (no comparison needed)
            {
              id: 'top_visitors',
              sources: ['visitors'],
              group_by: ['visitor'],
              columns: [
                'visitor_id',
                'visitor_hash',
                'ip_address',
                'user_id',
                'user_login',
                'user_email',
                'user_role',
                'total_views',
                'country_code',
                'country_name',
                'region_name',
                'city_name',
                'os_name',
                'browser_name',
                'browser_version',
                'device_type_name',
                'referrer_domain',
                'referrer_channel',
                'entry_page',
                'entry_page_title',
                'entry_page_type',
                'entry_page_wp_id',
                'exit_page',
                'exit_page_title',
                'exit_page_type',
                'exit_page_wp_id',
              ],
              per_page: 10,
              order_by: 'total_views',
              order: 'DESC',
              format: 'table',
              show_totals: false,
              compare: false, // Override: No comparison for visitor list
            },
            // Top Entry Pages: Table format with comparison (no columns - let API auto-select)
            {
              id: 'top_entry_pages',
              sources: ['visitors'],
              group_by: ['entry_page'],
              columns: ['page_uri', 'page_title', 'page_type', 'visitors'],
              per_page: 5,
              order_by: 'visitors',
              order: 'DESC',
              format: 'table',
              show_totals: false,
              compare: false,
            },
            // Top Referrers: Table format with comparison
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
            // Countries Map: All countries with visitors and views for GlobalMap component
            {
              id: 'countries_map',
              sources: ['visitors', 'views'],
              group_by: ['country'],
              columns: ['country_code', 'country_name', 'visitors', 'views'],
              per_page: 250,
              order_by: 'visitors',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: false,
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
