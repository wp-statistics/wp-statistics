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

// Author row from table format response
export interface AuthorRow {
  author_id: number
  author_name: string
  author_avatar: string
  views: number | string
  visitors: number | string
  published_content: number | string
  comments: number | string
  previous?: {
    views: number | string
    visitors: number | string
    published_content: number | string
    comments: number | string
  }
}

// Authors metrics response (flat format)
export interface AuthorsMetricsResponse {
  success: boolean
  items: Array<Record<string, string | number>>
  totals: {
    published_content: MetricValue
    active_authors: MetricValue
    visitors: MetricValue
    views: MetricValue
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

// Table format response wrapper for top authors
export interface TopAuthorsResponse {
  success: boolean
  data: {
    rows: AuthorRow[]
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

// Batch response structure for authors overview
export interface AuthorsOverviewResponse {
  success: boolean
  items: {
    author_metrics?: AuthorsMetricsResponse
    top_authors?: TopAuthorsResponse
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
            // Author Metrics: Flat format for aggregate totals
            {
              id: 'author_metrics',
              sources: ['published_content', 'active_authors', 'visitors', 'views'],
              group_by: [],
              format: 'flat',
              show_totals: true,
              compare: true,
            },
            // Top Authors: Table format for top authors widget
            {
              id: 'top_authors',
              sources: ['visitors', 'views', 'published_content', 'comments'],
              group_by: ['author'],
              columns: ['author_id', 'author_name', 'author_avatar', 'visitors', 'views', 'published_content', 'comments'],
              per_page: 20, // Fetch more to allow client-side sorting for different tabs
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
