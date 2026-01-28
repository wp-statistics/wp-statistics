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

// Flat format response (for metrics query without group_by)
export interface ContentMetricsResponse {
  success: boolean
  items: Array<Record<string, string | number>>
  totals: {
    visitors: MetricValue
    views: MetricValue
    bounce_rate: MetricValue
    avg_time_on_page: MetricValue
    published_content: MetricValue
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

// Chart format response (for traffic trends chart)
// Matches ChartApiResponse from types/chart.ts for compatibility with useChartData
export interface TrafficTrendsChartResponse {
  success: boolean
  labels: string[]
  previousLabels?: string[]
  datasets: Array<{
    key: string
    label: string
    data: (number | null)[]
    comparison?: boolean
  }>
  meta?: {
    compare_from?: string
    compare_to?: string
    [key: string]: unknown
  }
}

// Top content item for top content widget
export interface TopContentItem {
  page_uri: string
  page_title: string
  page_wp_id: number | null
  page_type?: string
  visitors: number
  views: number
  comments: number
  published_date: string | null
}

// Top referrer row for referrer widgets
export interface TopReferrerRow {
  referrer_domain: string | null
  referrer_name: string | null
  referrer_channel: string | null
  visitors: number | string
  previous?: {
    visitors: number | string
  }
}

// Table format row types for new widgets
export interface TopCountryRow {
  country_code: string
  country_name: string
  visitors: number | string
  previous?: {
    visitors: number | string
  }
}

export interface BrowserRow {
  browser_name: string
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

export interface DeviceTypeRow {
  device_type_name: string
  visitors: number | string
  previous?: {
    visitors: number | string
  }
}

// Table format response wrapper
export interface TableQueryResult<T> {
  success: boolean
  data: {
    rows: T[]
    totals?: { visitors: { current: number; previous?: number } }
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

// Top content response (table format)
export interface TopContentResponse {
  success: boolean
  data: {
    rows: TopContentItem[]
  }
  meta?: {
    total_rows?: number
    page?: number
    per_page?: number
    total_pages?: number
  }
}

// Top referrers response (table format)
export interface TopReferrersResponse {
  success: boolean
  data: {
    rows: TopReferrerRow[]
    totals?: { visitors: { current: number; previous?: number } }
  }
}

// Batch response structure for content overview
export interface ContentOverviewResponse {
  success: boolean
  items: {
    content_metrics?: ContentMetricsResponse
    traffic_trends?: TrafficTrendsChartResponse
    top_content?: TopContentResponse
    top_referrers?: TopReferrersResponse
    top_search_engines?: TopReferrersResponse
    top_countries?: TableQueryResult<TopCountryRow>
    top_browsers?: TableQueryResult<BrowserRow>
    top_operating_systems?: TableQueryResult<OperatingSystemRow>
    top_device_categories?: TableQueryResult<DeviceTypeRow>
  }
  errors?: Record<string, { code: string; message: string }>
  skipped?: string[]
  meta?: {
    preferences?: Record<string, unknown>
  }
}

export interface GetContentOverviewParams {
  dateFrom: string
  dateTo: string
  compareDateFrom?: string
  compareDateTo?: string
  filters?: Filter[]
  timeframe?: 'daily' | 'weekly' | 'monthly'
}

export const getContentOverviewQueryOptions = ({
  dateFrom,
  dateTo,
  compareDateFrom,
  compareDateTo,
  filters = [],
  timeframe = 'daily',
}: GetContentOverviewParams) => {
  // Transform UI filters to API format
  const apiFilters = transformFiltersToApi(filters)
  // Check if compare dates are provided
  const hasCompare = !!(compareDateFrom && compareDateTo)

  // Map timeframe to API group_by value
  const dateGroupBy = timeframe === 'weekly' ? 'week' : timeframe === 'monthly' ? 'month' : 'date'

  return queryOptions({
    queryKey: ['content-overview', dateFrom, dateTo, compareDateFrom, compareDateTo, apiFilters, hasCompare, timeframe, dateGroupBy],
    queryFn: () =>
      clientRequest.post<ContentOverviewResponse>(
        '',
        {
          date_from: dateFrom,
          date_to: dateTo,
          compare: hasCompare,
          ...(hasCompare && {
            previous_date_from: compareDateFrom,
            previous_date_to: compareDateTo,
          }),
          filters: apiFilters,
          queries: [
            // Content Metrics: Flat format for aggregate totals
            {
              id: 'content_metrics',
              sources: ['visitors', 'views', 'bounce_rate', 'avg_time_on_page', 'published_content', 'comments'],
              group_by: [],
              format: 'flat',
              show_totals: true,
              compare: true,
            },
            // Traffic Trends: Chart format for line chart with published content bars
            {
              id: 'traffic_trends',
              sources: ['visitors', 'views', 'published_content'],
              group_by: [dateGroupBy],
              format: 'chart',
              show_totals: false,
              compare: true,
            },
            // Top Content: Table format for top content widget (3 tabs x 5 items each)
            {
              id: 'top_content',
              sources: ['visitors', 'views', 'published_content', 'comments'],
              group_by: ['page'],
              format: 'table',
              show_totals: false,
              compare: false,
              per_page: 15,
              columns: ['page_uri', 'page_title', 'page_wp_id', 'page_type', 'visitors', 'views', 'comments', 'published_date'],
            },
            // Top Referrers: Table format for top referrers widget
            {
              id: 'top_referrers',
              sources: ['visitors'],
              group_by: ['referrer'],
              columns: ['referrer_domain', 'referrer_name', 'referrer_channel', 'visitors'],
              filters: [{ key: 'referrer_domain', operator: 'is_not_empty', value: '' }],
              per_page: 5,
              order_by: 'visitors',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: true,
            },
            // Top Search Engines: Table format for top search engines widget
            {
              id: 'top_search_engines',
              sources: ['visitors'],
              group_by: ['referrer'],
              columns: ['referrer_domain', 'referrer_name', 'visitors'],
              filters: [
                { key: 'referrer_domain', operator: 'is_not_empty', value: '' },
                { key: 'referrer_channel', operator: 'contains', value: 'search' },
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
              id: 'top_operating_systems',
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
              id: 'top_device_categories',
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
