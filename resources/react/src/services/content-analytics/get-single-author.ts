import { queryOptions } from '@tanstack/react-query'

import type { Filter } from '@/components/custom/filter-bar'
import { transformFiltersToApi } from '@/lib/api-filter-transform'
import { clientRequest } from '@/lib/client-request'
import { WordPress } from '@/lib/wordpress'

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

// Author info row from table query
export interface AuthorInfoRow {
  page_uri: string
  page_title: string
  page_wp_id: number | null
  page_type?: string
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
}

export const getSingleAuthorQueryOptions = ({
  authorId,
  dateFrom,
  dateTo,
  compareDateFrom,
  compareDateTo,
  filters,
}: GetSingleAuthorParams) => {
  // Transform UI filters to API format (ensure filters is an array)
  const apiFilters = transformFiltersToApi(filters || [])
  // Check if compare dates are provided
  const hasCompare = !!(compareDateFrom && compareDateTo)

  // Add author_id filter for the specific author
  const filtersWithAuthorId: Record<string, unknown> = {
    ...apiFilters,
    author_id: String(authorId),
  }

  return queryOptions({
    queryKey: ['single-author', authorId, dateFrom, dateTo, compareDateFrom, compareDateTo, apiFilters, hasCompare],
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
          filters: filtersWithAuthorId,
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
            // Author Info: Table format to get author metadata
            {
              id: 'author_info',
              sources: ['views'],
              group_by: ['page'],
              filters: {
                post_type: { is: 'author_archive' },
                resource_id: String(authorId),
              },
              columns: [
                'page_uri',
                'page_title',
                'page_wp_id',
                'page_type',
              ],
              format: 'table',
              per_page: 1,
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
