import { queryOptions } from '@tanstack/react-query'

import type { Filter } from '@/components/custom/filter-bar'
import { transformFiltersToApi, type ApiFilters } from '@/lib/api-filter-transform'
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
export interface MetricsResponse {
  success: boolean
  items: Array<Record<string, string | number>>
  totals: {
    views: MetricValue
    visitors: MetricValue
    avg_time_on_page: MetricValue
    bounce_rate: MetricValue
    pages_per_session: MetricValue
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

// Chart format response (for page views trends)
export interface PageViewsTrendsChartResponse {
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

// Table format row types
export interface TopPageRow {
  page_uri: string
  page_uri_id: number
  resource_id: number | null
  page_title: string
  page_wp_id: number | null
  page_type: string
  views: number | string
  visitors: number | string
  bounce_rate?: number | string
  avg_time_on_page?: number | string
  previous?: {
    views: number | string
    visitors: number | string
  }
}

export interface EntryPageRow {
  page_uri: string
  page_uri_id: number
  resource_id: number | null
  page_title: string
  page_wp_id: number | null
  page_type: string
  sessions: number | string
  bounce_rate?: number | string
  previous?: {
    sessions: number | string
  }
}

export interface Page404Row {
  page_uri: string
  page_uri_id: number
  views: number | string
  previous?: {
    views: number | string
  }
}

// Table format response wrapper
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
export interface PageInsightsOverviewResponse {
  success: boolean
  items: {
    // Flat format - totals at top level
    metrics?: MetricsResponse
    // Top content type metrics
    metrics_top_post_type?: {
      success: boolean
      items: [{ page_type: string; views: number }]
      totals: Record<string, unknown>
    }
    // Chart format - labels and datasets at top level
    page_views_trends?: PageViewsTrendsChartResponse
    // Table format - data.rows structure
    top_pages?: TableQueryResult<TopPageRow>
    entry_pages?: TableQueryResult<EntryPageRow>
    pages_404?: TableQueryResult<Page404Row>
  }
  errors?: Record<string, { code: string; message: string }>
  skipped?: string[]
  meta?: {
    preferences?: Record<string, unknown>
  }
}

export interface GetPageInsightsOverviewParams {
  dateFrom: string
  dateTo: string
  compareDateFrom?: string
  compareDateTo?: string
  timeframe?: 'daily' | 'weekly' | 'monthly'
  filters?: Filter[]
}

export const getPageInsightsOverviewQueryOptions = ({
  dateFrom,
  dateTo,
  compareDateFrom,
  compareDateTo,
  timeframe = 'daily',
  filters = [],
}: GetPageInsightsOverviewParams) => {
  // Determine the appropriate date group_by based on timeframe
  const dateGroupBy = timeframe === 'monthly' ? 'month' : timeframe === 'weekly' ? 'week' : 'date'
  // Transform UI filters to API format
  const apiFilters = transformFiltersToApi(filters)
  // Check if compare dates are provided
  const hasCompare = !!(compareDateFrom && compareDateTo)

  return queryOptions({
    queryKey: ['page-insights-overview', dateFrom, dateTo, compareDateFrom, compareDateTo, timeframe, apiFilters],
    queryFn: () =>
      clientRequest.post<PageInsightsOverviewResponse>(
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
              sources: ['views', 'visitors', 'avg_time_on_page', 'bounce_rate', 'pages_per_session'],
              group_by: [],
              format: 'flat',
              show_totals: true,
              compare: true,
            },
            // Top Post Type Query (most viewed content type)
            {
              id: 'metrics_top_post_type',
              sources: ['views'],
              group_by: ['page'],
              columns: ['page_type', 'views'],
              per_page: 1,
              order_by: 'views',
              order: 'DESC',
              format: 'flat',
              show_totals: false,
              compare: false,
            },
            // Page Views Trends: Chart format for line chart
            {
              id: 'page_views_trends',
              sources: ['views', 'visitors'],
              group_by: [dateGroupBy],
              format: 'chart',
              show_totals: false,
              compare: true,
            },
            // Top Pages: Table format with comparison
            {
              id: 'top_pages',
              sources: ['views', 'visitors'],
              group_by: ['page'],
              columns: ['page_uri', 'page_uri_id', 'resource_id', 'page_title', 'page_wp_id', 'page_type', 'views', 'visitors'],
              per_page: 5,
              order_by: 'views',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: true,
            },
            // Entry Pages: Table format with comparison
            {
              id: 'entry_pages',
              sources: ['sessions', 'bounce_rate'],
              group_by: ['entry_page'],
              columns: ['page_uri', 'page_uri_id', 'resource_id', 'page_title', 'page_wp_id', 'page_type', 'sessions', 'bounce_rate'],
              per_page: 5,
              order_by: 'sessions',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: true,
            },
            // 404 Pages: Table format
            {
              id: 'pages_404',
              sources: ['views'],
              group_by: ['page'],
              columns: ['page_uri', 'page_uri_id', 'views'],
              filters: [
                {
                  key: 'post_type',
                  operator: 'is',
                  value: '404',
                },
              ],
              per_page: 5,
              order_by: 'views',
              order: 'DESC',
              format: 'table',
              show_totals: false,
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
