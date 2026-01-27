import { queryOptions } from '@tanstack/react-query'

import type { FilterRowData } from '@/components/custom/filter-button'
import { type ApiFilters, transformFiltersToApi } from '@/lib/api-filter-transform'
import { clientRequest } from '@/lib/client-request'
import { WordPress } from '@/lib/wordpress'

import type { SourceCategoryRecord } from '@/components/data-table-columns/source-categories-columns'

// Re-export types
export type { ApiFilters }

/**
 * Chart format response for source categories trends.
 * Uses dynamic datasets for top 3 source categories + Total.
 */
export interface SourceCategoriesChartResponse {
  success: boolean
  labels: string[]
  previousLabels?: string[]
  datasets: Array<{
    key: string
    label: string
    data: (number | null)[]
    comparison?: boolean
  }>
  meta?: Record<string, unknown>
}

/**
 * Table format response for source categories
 */
export interface SourceCategoriesTableResponse {
  success: boolean
  data: {
    rows: SourceCategoryRecord[]
    total: number
    totals?: {
      visitors: { current: number; previous?: number }
      views: { current: number; previous?: number }
    }
  }
  meta?: {
    date_from: string
    date_to: string
    page: number
    per_page: number
    total_pages: number
    total_rows: number
    preferences?: {
      columns: string[]
      comparison_columns?: string[]
    }
  }
}

/**
 * Batch response structure for source categories overview
 */
export interface SourceCategoriesOverviewResponse {
  success: boolean
  items: {
    chart?: SourceCategoriesChartResponse
    table?: SourceCategoriesTableResponse
  }
  errors?: Record<string, { code: string; message: string }>
  skipped?: string[]
  meta?: {
    preferences?: Record<string, unknown>
  }
}

export interface GetSourceCategoriesOverviewParams {
  dateFrom: string
  dateTo: string
  compareDateFrom?: string
  compareDateTo?: string
  filters?: FilterRowData[]
  // Table params
  page?: number
  perPage?: number
  orderBy?: string
  order?: 'asc' | 'desc'
  context?: string
}

// Map frontend column names to API column names for sorting
const columnMapping: Record<string, string> = {
  sourceCategory: 'referrer_channel',
  sessionDuration: 'avg_session_duration',
  bounceRate: 'bounce_rate',
  pagesPerSession: 'pages_per_session',
}

/**
 * Creates query options for fetching source categories overview data.
 * Uses batch queries to fetch both chart and table data in a single request.
 */
export const getSourceCategoriesOverviewQueryOptions = ({
  dateFrom,
  dateTo,
  compareDateFrom,
  compareDateTo,
  filters = [],
  page = 1,
  perPage = 25,
  orderBy = 'visitors',
  order = 'desc',
  context,
}: GetSourceCategoriesOverviewParams) => {
  // Map frontend column name to API column name
  const apiOrderBy = columnMapping[orderBy] || orderBy

  // Transform UI filters to API format
  const apiFilters = transformFiltersToApi(filters)

  // Check if compare dates are provided
  const hasCompare = !!(compareDateFrom && compareDateTo)

  return queryOptions({
    queryKey: [
      'source-categories-overview',
      dateFrom,
      dateTo,
      compareDateFrom,
      compareDateTo,
      page,
      perPage,
      apiOrderBy,
      order,
      apiFilters,
      context,
    ],
    queryFn: () =>
      clientRequest.post<SourceCategoriesOverviewResponse>(
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
            // Chart query: Use source category chart provider for top 3 categories + total
            {
              id: 'chart',
              chart: 'source_category_chart',
              compare: hasCompare,
            },
            // Table query: Group by referrer_channel
            {
              id: 'table',
              sources: ['visitors', 'views', 'avg_session_duration', 'bounce_rate', 'pages_per_session'],
              group_by: ['referrer_channel'],
              page,
              per_page: perPage,
              order_by: apiOrderBy,
              order: order.toUpperCase(),
              format: 'table',
              ...(context && { context }),
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
