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
export interface AuthorsMetricsResponse {
  success: boolean
  items: Array<Record<string, string | number>>
  totals: {
    visitors: MetricValue
    views: MetricValue
    published_content: MetricValue
    active_authors: MetricValue
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

// Top author row
export interface TopAuthorRow {
  author_id: number
  author_name: string
  author_avatar?: string
  views: number | string
  visitors: number | string
  published_content: number | string
  comments?: number | string
  views_per_content?: number | string
  comments_per_content?: number | string
  previous?: {
    views: number | string
    visitors: number | string
    published_content: number | string
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

// Batch response structure for authors overview
export interface AuthorsOverviewResponse {
  success: boolean
  items: {
    // Flat format - totals at top level
    authors_metrics?: AuthorsMetricsResponse
    // Table format queries for top authors
    top_authors_views?: TableQueryResult<TopAuthorRow>
    top_authors_publishing?: TableQueryResult<TopAuthorRow>
    top_authors_views_per_content?: TableQueryResult<TopAuthorRow>
    top_authors_comments_per_content?: TableQueryResult<TopAuthorRow>
  }
  errors?: Record<string, { code: string; message: string }>
  skipped?: string[]
  meta?: {
    preferences?: Record<string, unknown>
  }
}

export interface GetAuthorsOverviewParams {
  dateFrom: string
  dateTo: string
  compareDateFrom?: string
  compareDateTo?: string
  filters?: Filter[]
}

export const getAuthorsOverviewQueryOptions = ({
  dateFrom,
  dateTo,
  compareDateFrom,
  compareDateTo,
  filters = [],
}: GetAuthorsOverviewParams) => {
  // Transform UI filters to API format
  const apiFilters = transformFiltersToApi(filters)
  // Check if compare dates are provided
  const hasCompare = !!(compareDateFrom && compareDateTo)

  return queryOptions({
    queryKey: ['authors-overview', dateFrom, dateTo, compareDateFrom, compareDateTo, apiFilters, hasCompare],
    queryFn: () =>
      clientRequest.post<AuthorsOverviewResponse>(
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
            // Authors Metrics: Flat format for aggregate totals
            {
              id: 'authors_metrics',
              sources: ['visitors', 'views', 'published_content', 'active_authors'],
              group_by: [],
              format: 'flat',
              show_totals: true,
              compare: true,
            },
            // Top Authors - By Views (also used to count active authors via total_rows)
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
            // Top Authors - By Publishing (content count)
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
            // Top Authors - By Views per Content (calculated on frontend)
            {
              id: 'top_authors_views_per_content',
              sources: ['views', 'visitors', 'published_content'],
              group_by: ['author'],
              per_page: 5,
              order_by: 'views',
              order: 'DESC',
              format: 'table',
              show_totals: false,
              compare: false,
            },
            // Top Authors - By Comments per Content (conditional)
            {
              id: 'top_authors_comments_per_content',
              sources: ['views', 'visitors', 'published_content', 'comments'],
              group_by: ['author'],
              per_page: 5,
              order_by: 'comments',
              order: 'DESC',
              format: 'table',
              show_totals: false,
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
