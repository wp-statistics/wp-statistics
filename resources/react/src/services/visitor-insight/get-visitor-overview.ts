import { queryOptions } from '@tanstack/react-query'

import type { Filter } from '@/components/custom/filter-bar'
import { clientRequest } from '@/lib/client-request'

// API filter format: { filter_key: { operator: value } }
export type ApiFilters = Record<string, Record<string, string | string[]>>

// Extract the field name from filter ID
// Filter IDs are in format: "os-os-filter-1766484171552-9509610" where the first segment is the field name
const extractFilterKey = (filterId: string): string => {
  // Split by hyphen and take the first segment (the actual field name)
  return filterId.split('-')[0]
}

// Transform UI filters to API format
// UI: { id, label, operator, rawOperator, value, rawValue }
// API: { filter_key: { operator: value } }
const transformFiltersToApi = (filters: Filter[]): ApiFilters => {
  const apiFilters: ApiFilters = {}

  for (const filter of filters) {
    // Extract the field name from the filter id (e.g., 'os' from 'os-os-filter-...')
    const filterKey = extractFilterKey(filter.id)
    // Use rawOperator if available, otherwise fall back to operator
    const operator = filter.rawOperator || filter.operator
    // Use rawValue if available, otherwise fall back to value
    // Convert number to string since API expects string | string[]
    const rawValue = filter.rawValue ?? filter.value
    const value: string | string[] = Array.isArray(rawValue)
      ? rawValue
      : typeof rawValue === 'number'
        ? String(rawValue)
        : rawValue

    apiFilters[filterKey] = {
      [operator]: value,
    }
  }

  return apiFilters
}

// Response types for each query in the batch

// Metric value with current/previous structure
interface MetricValue {
  current: number | string
  previous: number | string
}

// Flat format response (for metrics query without group_by)
// Flat format: { success, items: [...], totals: { metric: { current, previous } }, meta: {...} }
export interface MetricsFlatResponse {
  success: boolean
  items: Array<Record<string, unknown>>
  totals: {
    visitors: MetricValue
    views: MetricValue
    sessions: MetricValue
    avg_session_duration: MetricValue
    pages_per_session: MetricValue
    bounce_rate: MetricValue
  }
  meta?: Record<string, unknown>
}

// Chart format response (for traffic trends)
// Chart format: { success, labels: [...], datasets: [{key, label, data, comparison?}], meta: {...} }
// Note: Previous period data comes as separate datasets with key like "visitors_previous" and comparison: true
export interface TrafficTrendsChartResponse {
  success: boolean
  labels: string[]
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
  user_id: string | null
  user_login: string | null
  total_views: string
  country_code: string | null
  country_name: string | null
  region_name: string | null
  city_name: string | null
  os_name: string | null
  browser_name: string | null
  device_type_name: string | null
  referrer_domain: string | null
  referrer_channel: string | null
  entry_page: string | null
  entry_page_title: string | null
  exit_page: string | null
  exit_page_title: string | null
}

export interface TopEntryPageRow {
  page_uri: string
  page_uri_id: string
  resource_id: string
  page_title: string | null
  page_wp_id: string
  page_type: string
  visitors: string
  previous?: {
    visitors: number
  }
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
  }
}

// Batch response structure
// Batch response: { success, items: { queryId: result, ... }, errors, skipped, meta }
export interface VisitorOverviewResponse {
  success: boolean
  items: {
    // Flat format - totals at top level
    metrics?: MetricsFlatResponse
    // Chart format - labels and datasets at top level
    traffic_trends?: TrafficTrendsChartResponse
    // Table format - data.rows structure
    top_countries?: TableQueryResult<TopCountryRow>
    device_type?: TableQueryResult<DeviceTypeRow>
    operating_systems?: TableQueryResult<OperatingSystemRow>
    top_visitors?: TableQueryResult<TopVisitorRow>
    top_entry_pages?: TableQueryResult<TopEntryPageRow>
    top_referrers?: TableQueryResult<TopReferrerRow>
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
  // Check if compare dates are provided
  const hasCompare = compareDateFrom && compareDateTo

  return queryOptions({
    queryKey: ['visitor-overview', dateFrom, dateTo, compareDateFrom, compareDateTo, timeframe, apiFilters],
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
              sources: ['visitors', 'views', 'sessions', 'avg_session_duration', 'pages_per_session', 'bounce_rate'],
              group_by: [],
              format: 'flat',
              show_totals: true,
            },
            // Traffic Trends: Chart format for line chart
            {
              id: 'traffic_trends',
              sources: ['visitors', 'views'],
              group_by: [dateGroupBy],
              format: 'chart',
              show_totals: false,
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
              show_totals: false,
            },
            // Device Type: Table format with comparison
            {
              id: 'device_type',
              sources: ['visitors'],
              group_by: ['device_type'],
              columns: ['device_type_name', 'visitors'],
              per_page: 5,
              order_by: 'visitors',
              order: 'DESC',
              format: 'table',
              show_totals: false,
            },
            // Operating Systems: Table format with comparison
            {
              id: 'operating_systems',
              sources: ['visitors'],
              group_by: ['os'],
              columns: ['os_name', 'visitors'],
              per_page: 5,
              order_by: 'visitors',
              order: 'DESC',
              format: 'table',
              show_totals: false,
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
                'total_views',
                'country_code',
                'country_name',
                'region_name',
                'city_name',
                'os_name',
                'browser_name',
                'device_type_name',
                'referrer_domain',
                'referrer_channel',
                'entry_page',
                'entry_page_title',
                'exit_page',
                'exit_page_title',
              ],
              per_page: 10,
              order_by: 'total_views',
              order: 'DESC',
              format: 'table',
              show_totals: false,
              compare: false, // Override: No comparison for visitor list
            },
            // Top Pages: Table format with comparison (no columns - let API auto-select)
            {
              id: 'top_entry_pages',
              sources: ['visitors'],
              group_by: ['page'],
              per_page: 5,
              order_by: 'visitors',
              order: 'DESC',
              format: 'table',
              show_totals: false,
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
              show_totals: false,
            },
          ],
        },
        {
          params: {
            action: 'wp_statistics_analytics',
          },
        }
      ),
  })
}
