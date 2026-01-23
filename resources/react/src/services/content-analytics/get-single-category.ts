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
  content_count?: MetricValue
  bounce_rate?: MetricValue
  avg_time_on_page?: MetricValue
}

// Single category metrics response (flat format)
export interface SingleCategoryMetricsResponse {
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

// Category info row from table query
export interface CategoryInfoRow {
  page_uri: string
  page_title: string
  page_wp_id: number | null
  page_type?: string
}

// Category info response (table format)
export interface CategoryInfoResponse {
  success: boolean
  data: {
    rows: CategoryInfoRow[]
  }
}

// Batch response structure for single category
export interface SingleCategoryResponse {
  success: boolean
  items: {
    category_metrics?: SingleCategoryMetricsResponse
    category_info?: CategoryInfoResponse
  }
  errors?: Record<string, { code: string; message: string }>
  skipped?: string[]
  meta?: {
    preferences?: Record<string, unknown>
  }
}

export interface GetSingleCategoryParams {
  termId: string | number
  dateFrom: string
  dateTo: string
  compareDateFrom?: string
  compareDateTo?: string
  filters?: Filter[]
}

export const getSingleCategoryQueryOptions = ({
  termId,
  dateFrom,
  dateTo,
  compareDateFrom,
  compareDateTo,
  filters,
}: GetSingleCategoryParams) => {
  // Transform UI filters to API format (ensure filters is an array)
  const apiFilters = transformFiltersToApi(filters || [])
  // Check if compare dates are provided
  const hasCompare = !!(compareDateFrom && compareDateTo)

  // Add taxonomy_id filter for the specific term
  const filtersWithTermId: Record<string, unknown> = {
    ...apiFilters,
    taxonomy_id: String(termId),
  }

  return queryOptions({
    queryKey: ['single-category', termId, dateFrom, dateTo, compareDateFrom, compareDateTo, apiFilters, hasCompare],
    queryFn: () =>
      clientRequest.post<SingleCategoryResponse>(
        '',
        {
          date_from: dateFrom,
          date_to: dateTo,
          compare: hasCompare,
          ...(hasCompare && {
            previous_date_from: compareDateFrom,
            previous_date_to: compareDateTo,
          }),
          filters: filtersWithTermId,
          queries: [
            // Category Metrics: Flat format for aggregate totals
            {
              id: 'category_metrics',
              sources: [
                'visitors',
                'views',
                'content_count',
                'bounce_rate',
                'avg_time_on_page',
              ],
              group_by: [],
              format: 'flat',
              show_totals: true,
              compare: true,
            },
            // Category Info: Table format to get category metadata
            {
              id: 'category_info',
              sources: ['views'],
              group_by: ['page'],
              filters: {
                resource_id: String(termId),
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
