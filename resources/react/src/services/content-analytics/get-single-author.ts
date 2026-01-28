import { queryOptions } from '@tanstack/react-query'

import type { Filter } from '@/components/custom/filter-bar'
import { transformFiltersToApi } from '@/lib/api-filter-transform'
import { clientRequest } from '@/lib/client-request'
import { WordPress } from '@/lib/wordpress'

import type { ContentRow } from './get-categories-overview'
import type { TableQueryResult, TrafficTrendsChartResponse } from './get-content-overview'

// Re-export for consumers
export type { ContentRow }

// Metric value with current/previous structure
interface MetricValue {
  current: number | string
  previous?: number | string
}

// Totals structure for flat format
interface MetricsTotals {
  visitors?: MetricValue
  views?: MetricValue
  published_content?: MetricValue
  bounce_rate?: MetricValue
  avg_time_on_page?: MetricValue
  comments?: MetricValue
}

// Single author metrics response (flat format)
export interface SingleAuthorMetricsResponse {
  success: boolean
  items: Array<Record<string, string | number>>
  totals: MetricsTotals
  meta: {
    date_from: string
    date_to: string
    cached: boolean
    cache_ttl: number
    compare_from?: string
    compare_to?: string
  }
}

// Author info row from table query (using group_by: ['author'])
export interface AuthorInfoRow {
  author_id: number
  author_name: string
  author_avatar: string | null
  visitors?: number | string
  views?: number | string
  published_content?: number | string
  comments?: number | string
}

// Author info response (table format)
export interface AuthorInfoResponse {
  success: boolean
  data: {
    rows: AuthorInfoRow[]
  }
}

// Batch response structure for single author
export interface SingleAuthorResponse {
  success: boolean
  items: {
    author_metrics?: SingleAuthorMetricsResponse
    author_info?: AuthorInfoResponse
    traffic_trends?: TrafficTrendsChartResponse
    top_content?: TableQueryResult<ContentRow>
  }
  errors?: Record<string, { code: string; message: string }>
  skipped?: string[]
  meta?: {
    preferences?: Record<string, unknown>
  }
}

export interface GetSingleAuthorParams {
  authorId: string | number
  dateFrom: string
  dateTo: string
  compareDateFrom?: string
  compareDateTo?: string
  filters?: Filter[]
  timeframe?: 'daily' | 'weekly' | 'monthly'
  /** Filter by post type (e.g., 'post', 'page'). If 'all' or undefined, no post_type filter is applied. */
  postType?: string
}

export const getSingleAuthorQueryOptions = ({
  authorId,
  dateFrom,
  dateTo,
  compareDateFrom,
  compareDateTo,
  filters,
  timeframe = 'daily',
  postType,
}: GetSingleAuthorParams) => {
  // Transform UI filters to API format (ensure filters is an array)
  const apiFilters = transformFiltersToApi(filters || [])
  // Check if compare dates are provided
  const hasCompare = !!(compareDateFrom && compareDateTo)

  // Build filters with author and optional post_type
  const filtersWithAuthor: Record<string, unknown> = {
    ...apiFilters,
    author: { is: String(authorId) },
    // Add post_type filter if specified (not 'all')
    ...(postType && postType !== 'all' && { post_type: { is: postType } }),
  }

  // Determine group_by based on timeframe
  const chartGroupBy = timeframe === 'daily' ? 'date' : timeframe === 'weekly' ? 'week' : 'month'

  return queryOptions({
    queryKey: ['single-author', authorId, dateFrom, dateTo, compareDateFrom, compareDateTo, apiFilters, hasCompare, timeframe, postType, filtersWithAuthor, chartGroupBy],
    queryFn: () =>
      clientRequest.post<SingleAuthorResponse>(
        '',
        {
          date_from: dateFrom,
          date_to: dateTo,
          compare: hasCompare,
          ...(hasCompare && {
            previous_date_from: compareDateFrom,
            previous_date_to: compareDateTo,
          }),
          filters: filtersWithAuthor,
          queries: [
            // Author Metrics: Flat format for aggregate totals
            {
              id: 'author_metrics',
              sources: [
                'visitors',
                'views',
                'published_content',
                'bounce_rate',
                'avg_time_on_page',
                'comments',
              ],
              group_by: [],
              format: 'flat',
              show_totals: true,
              compare: true,
            },
            // Author Info: Table format to get author metadata (grouped by author)
            {
              id: 'author_info',
              sources: ['visitors', 'views', 'published_content', 'comments'],
              group_by: ['author'],
              columns: [
                'author_id',
                'author_name',
                'author_avatar',
                'visitors',
                'views',
                'published_content',
                'comments',
              ],
              format: 'table',
              per_page: 1,
              show_totals: false,
              compare: false,
            },
            // Traffic Trends: Chart format for performance over time
            {
              id: 'traffic_trends',
              sources: ['visitors', 'views', 'published_content'],
              group_by: [chartGroupBy],
              format: 'chart',
              show_totals: false,
              compare: true,
            },
            // Top Content: Table format for author's content list
            // Note: post_type filter is applied globally (including 'all' which excludes author_archive via queryable types)
            {
              id: 'top_content',
              sources: ['visitors', 'views', 'comments'],
              group_by: ['page'],
              columns: [
                'page_uri',
                'page_title',
                'page_wp_id',
                'page_type',
                'visitors',
                'views',
                'comments',
                'published_date',
              ],
              format: 'table',
              per_page: 15,
              order_by: 'views',
              order: 'DESC',
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
