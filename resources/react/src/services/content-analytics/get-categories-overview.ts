import { queryOptions } from '@tanstack/react-query'

import type { Filter } from '@/components/custom/filter-bar'
import { transformFiltersToApi } from '@/lib/api-filter-transform'
import { clientRequest } from '@/lib/client-request'
import { WordPress } from '@/lib/wordpress'

// Metric value with current/previous structure
interface MetricValue {
  current: number | string
  previous: number | string
}

// Category/Term row from table format response
export interface TermRow {
  term_id: number
  term_name: string
  term_slug: string
  views: number | string
  visitors: number | string
  published_content: number | string
  previous?: {
    views: number | string
    visitors: number | string
    published_content: number | string
  }
}

// Author row for top authors widget
export interface AuthorRow {
  author_id: number
  author_name: string
  author_avatar: string
  views: number | string
  visitors: number | string
  published_content: number | string
  previous?: {
    views: number | string
    visitors: number | string
    published_content: number | string
  }
}

// Content row for top content widget
export interface ContentRow {
  page_uri: string
  page_title: string
  page_wp_id: number | null
  page_type?: string
  visitors: number | string
  views: number | string
  comments: number | string
  published_date: string | null
  previous?: {
    views: number | string
    visitors: number | string
  }
}

// Categories metrics response (flat format)
export interface CategoriesMetricsResponse {
  success: boolean
  items: Array<Record<string, string | number>>
  totals: {
    published_content: MetricValue
    visitors: MetricValue
    views: MetricValue
    bounce_rate: MetricValue
    avg_time_on_page: MetricValue
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

// Chart format response for traffic trends
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

// Table format response wrapper
export interface TableQueryResult<T> {
  success: boolean
  data: {
    rows: T[]
    totals?: {
      visitors: { current: number; previous?: number }
      views: { current: number; previous?: number }
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

// Batch response structure for categories overview
export interface CategoriesOverviewResponse {
  success: boolean
  items: {
    category_metrics?: CategoriesMetricsResponse
    traffic_trends?: TrafficTrendsChartResponse
    top_terms?: TableQueryResult<TermRow>
    top_content?: TableQueryResult<ContentRow>
    top_authors?: TableQueryResult<AuthorRow>
  }
  errors?: Record<string, { code: string; message: string }>
  skipped?: string[]
  meta?: {
    preferences?: Record<string, unknown>
  }
}

export interface GetCategoriesOverviewParams {
  dateFrom: string
  dateTo: string
  compareDateFrom?: string
  compareDateTo?: string
  filters?: Filter[]
  taxonomy?: string
  timeframe?: 'daily' | 'weekly' | 'monthly'
}

export const getCategoriesOverviewQueryOptions = ({
  dateFrom,
  dateTo,
  compareDateFrom,
  compareDateTo,
  filters = [],
  taxonomy = 'category',
  timeframe = 'daily',
}: GetCategoriesOverviewParams) => {
  // Transform UI filters to API format
  const apiFilters = transformFiltersToApi(filters)
  // Check if compare dates are provided
  const hasCompare = !!(compareDateFrom && compareDateTo)

  // Map timeframe to API group_by value
  const dateGroupBy = timeframe === 'weekly' ? 'week' : timeframe === 'monthly' ? 'month' : 'date'

  return queryOptions({
    queryKey: ['categories-overview', dateFrom, dateTo, compareDateFrom, compareDateTo, apiFilters, hasCompare, taxonomy, timeframe],
    queryFn: () =>
      clientRequest.post<CategoriesOverviewResponse>(
        '',
        {
          date_from: dateFrom,
          date_to: dateTo,
          compare: hasCompare,
          ...(hasCompare && {
            previous_date_from: compareDateFrom,
            previous_date_to: compareDateTo,
          }),
          filters: {
            ...apiFilters,
            taxonomy_type: { is: taxonomy },
          },
          queries: [
            // Category Metrics: Flat format for aggregate totals
            {
              id: 'category_metrics',
              sources: ['published_content', 'visitors', 'views', 'bounce_rate', 'avg_time_on_page'],
              group_by: [],
              format: 'flat',
              show_totals: true,
              compare: true,
            },
            // Traffic Trends: Chart format for line chart
            {
              id: 'traffic_trends',
              sources: ['visitors', 'views', 'published_content'],
              group_by: [dateGroupBy],
              format: 'chart',
              show_totals: false,
              compare: true,
            },
            // Top Terms: Table format for top terms widget
            {
              id: 'top_terms',
              sources: ['visitors', 'views', 'published_content'],
              group_by: ['taxonomy'],
              columns: ['term_id', 'term_name', 'term_slug', 'visitors', 'views', 'published_content'],
              per_page: 10,
              order_by: 'views',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: true,
            },
            // Top Content: Table format for top content widget
            {
              id: 'top_content',
              sources: ['visitors', 'views', 'comments'],
              group_by: ['page'],
              columns: ['page_wp_id', 'page_title', 'page_uri', 'page_type', 'visitors', 'views', 'comments', 'published_date'],
              per_page: 15,
              order_by: 'views',
              order: 'DESC',
              format: 'table',
              show_totals: false,
              compare: true,
            },
            // Top Authors: Table format for top authors widget
            {
              id: 'top_authors',
              sources: ['visitors', 'views', 'published_content'],
              group_by: ['author'],
              columns: ['author_id', 'author_name', 'author_avatar', 'visitors', 'views', 'published_content'],
              per_page: 15,
              order_by: 'views',
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
