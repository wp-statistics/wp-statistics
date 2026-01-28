import { queryOptions } from '@tanstack/react-query'

import type { Filter } from '@/components/custom/filter-bar'
import { type ApiFilters,transformFiltersToApi } from '@/lib/api-filter-transform'
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
export interface MetricsResponse {
  success: boolean
  items: Array<Record<string, string | number>>
  totals: {
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

// Table format row types
export interface TopPageRow {
  page_uri: string
  page_title: string
  views: number | string
  previous?: {
    views: number | string
  }
}

export interface NotFoundPageRow {
  page_uri: string
  views: number | string
  previous?: {
    views: number | string
  }
}

export interface CategoryRow {
  page_title: string
  views: number | string
  previous?: {
    views: number | string
  }
}

export interface AuthorRow {
  author_name: string
  views: number | string
  previous?: {
    views: number | string
  }
}

export interface EntryPageRow {
  page_uri: string
  page_title: string
  sessions: number | string
  previous?: {
    sessions: number | string
  }
}

export interface ExitPageRow {
  page_uri: string
  page_title: string
  sessions: number | string
  previous?: {
    sessions: number | string
  }
}

// Totals can be either a simple value or a comparison object
interface TotalValue {
  current?: number | string
  previous?: number | string
}

// Table format response wrapper
export interface TableQueryResult<T> {
  success: boolean
  data: {
    rows: T[]
    totals?: Record<string, number | string | TotalValue>
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

// Flat format for single-item queries (top page, top author)
export interface SingleItemResponse<T> {
  success: boolean
  items: T[]
  totals: Record<string, unknown>
}

// Batch response structure
export interface PageInsightsOverviewResponse {
  success: boolean
  items: {
    // Flat format - totals at top level
    metrics?: MetricsResponse
    metrics_top_page?: SingleItemResponse<{ page_title: string; views: number }>
    // Table format - data.rows structure
    top_pages?: TableQueryResult<TopPageRow>
    pages_404?: TableQueryResult<NotFoundPageRow>
    by_category?: TableQueryResult<CategoryRow>
    by_author?: TableQueryResult<AuthorRow>
    top_entry_pages?: TableQueryResult<EntryPageRow>
    top_exit_pages?: TableQueryResult<ExitPageRow>
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
  filters?: Filter[]
}

export const getPageInsightsOverviewQueryOptions = ({
  dateFrom,
  dateTo,
  compareDateFrom,
  compareDateTo,
  filters = [],
}: GetPageInsightsOverviewParams) => {
  // Transform UI filters to API format
  const apiFilters = transformFiltersToApi(filters)
  // Check if compare dates are provided (must be boolean, not the date value)
  const hasCompare = !!(compareDateFrom && compareDateTo)

  return queryOptions({
    queryKey: ['page-insights-overview', dateFrom, dateTo, compareDateFrom, compareDateTo, apiFilters, hasCompare],
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
              sources: ['visitors', 'views', 'bounce_rate', 'avg_time_on_page'],
              group_by: [],
              format: 'flat',
              show_totals: true,
              compare: true,
            },
            // Top Page Query
            {
              id: 'metrics_top_page',
              sources: ['views'],
              group_by: ['page'],
              columns: ['page_title', 'views'],
              per_page: 1,
              order_by: 'views',
              order: 'DESC',
              format: 'flat',
              show_totals: false,
              compare: false,
            },
            // Top Pages List
            {
              id: 'top_pages',
              sources: ['views'],
              group_by: ['page'],
              columns: ['page_uri', 'page_title', 'views'],
              per_page: 5,
              order_by: 'views',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: true,
            },
            // 404 Pages List
            {
              id: 'pages_404',
              sources: ['views'],
              group_by: ['page'],
              columns: ['page_uri', 'views'],
              filters: [{ key: 'post_type', operator: 'is', value: '404' }],
              per_page: 5,
              order_by: 'views',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: true,
            },
            // By Category
            {
              id: 'by_category',
              sources: ['views'],
              group_by: ['page'],
              columns: ['page_title', 'views'],
              filters: [{ key: 'post_type', operator: 'is', value: 'category' }],
              per_page: 5,
              order_by: 'views',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: true,
            },
            // By Author
            {
              id: 'by_author',
              sources: ['views'],
              group_by: ['author'],
              columns: ['author_name', 'views'],
              per_page: 5,
              order_by: 'views',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: true,
            },
            // Top Entry Pages (requires premium entry-pages feature)
            {
              id: 'top_entry_pages',
              sources: ['sessions'],
              group_by: ['entry_page'],
              columns: ['page_uri', 'page_title', 'sessions'],
              per_page: 5,
              order_by: 'sessions',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: true,
            },
            // Top Exit Pages (requires premium exit-pages feature)
            {
              id: 'top_exit_pages',
              sources: ['sessions'],
              group_by: ['exit_page'],
              columns: ['page_uri', 'page_title', 'sessions'],
              per_page: 5,
              order_by: 'sessions',
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
