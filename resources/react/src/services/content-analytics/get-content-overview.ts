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
  visitors: number
  views: number
  comments: number
  published_date: string | null
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

// Batch response structure for content overview
export interface ContentOverviewResponse {
  success: boolean
  items: {
    content_metrics?: ContentMetricsResponse
    traffic_trends?: TrafficTrendsChartResponse
    top_content?: TopContentResponse
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
    queryKey: ['content-overview', dateFrom, dateTo, compareDateFrom, compareDateTo, apiFilters, hasCompare, timeframe],
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
              columns: ['page_uri', 'page_title', 'page_wp_id', 'visitors', 'views', 'comments', 'published_date'],
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
