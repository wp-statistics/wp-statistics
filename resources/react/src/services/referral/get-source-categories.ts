import { queryOptions } from '@tanstack/react-query'

import type { Filter } from '@/components/custom/filter-bar'
import { transformFiltersToApi } from '@/lib/api-filter-transform'
import { clientRequest } from '@/lib/client-request'
import { WordPress } from '@/lib/wordpress'

export interface SourceCategoryRow {
  referrer_channel: string
  visitors: number | string
  views: number | string
  avg_session_duration: number | string
  bounce_rate: number | string
  pages_per_session: number | string
  previous?: {
    visitors: number | string
    views: number | string
    avg_session_duration: number | string
    bounce_rate: number | string
    pages_per_session: number | string
  }
}

// Chart format response
export interface SourceCategoriesChartResponse {
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

// Table format response
export interface SourceCategoriesTableResponse {
  success: boolean
  data: {
    rows: SourceCategoryRow[]
    totals?: {
      visitors: { current: number | string; previous?: number | string }
      views: { current: number | string; previous?: number | string }
    }
  }
  meta?: {
    date_from: string
    date_to: string
    page: number
    per_page: number
    total_pages: number
    total_rows: number
    cached: boolean
    cache_ttl: number
  }
}

// Batch response
export interface SourceCategoriesBatchResponse {
  success: boolean
  items: {
    chart?: SourceCategoriesChartResponse
    table?: SourceCategoriesTableResponse
  }
  errors?: Record<string, { code: string; message: string }>
  skipped?: string[]
}

export interface GetSourceCategoriesParams {
  dateFrom: string
  dateTo: string
  compareDateFrom?: string
  compareDateTo?: string
  timeframe?: 'daily' | 'weekly' | 'monthly'
  page?: number
  perPage?: number
  orderBy?: string
  order?: 'ASC' | 'DESC'
  filters?: Filter[]
}

export const getSourceCategoriesQueryOptions = ({
  dateFrom,
  dateTo,
  compareDateFrom,
  compareDateTo,
  timeframe = 'daily',
  page = 1,
  perPage = 20,
  orderBy = 'visitors',
  order = 'DESC',
  filters = [],
}: GetSourceCategoriesParams) => {
  const dateGroupBy = timeframe === 'monthly' ? 'month' : timeframe === 'weekly' ? 'week' : 'date'
  const apiFilters = transformFiltersToApi(filters)
  const hasCompare = !!(compareDateFrom && compareDateTo)

  // Base filter for referred traffic
  const referredFilter = { key: 'referrer_domain', operator: 'is_not_empty', value: '' }

  return queryOptions({
    queryKey: [
      'source-categories',
      dateFrom,
      dateTo,
      compareDateFrom,
      compareDateTo,
      timeframe,
      page,
      perPage,
      orderBy,
      order,
      apiFilters,
    ],
    queryFn: () =>
      clientRequest.post<SourceCategoriesBatchResponse>(
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
            // Chart: trends by channel over time
            {
              id: 'chart',
              sources: ['visitors'],
              group_by: ['referrer_channel', dateGroupBy],
              filters: [referredFilter],
              format: 'chart',
              show_totals: false,
              compare: true,
            },
            // Table: source categories list
            {
              id: 'table',
              sources: ['visitors', 'views', 'avg_session_duration', 'bounce_rate', 'pages_per_session'],
              group_by: ['referrer_channel'],
              columns: ['referrer_channel', 'visitors', 'views', 'avg_session_duration', 'bounce_rate', 'pages_per_session'],
              filters: [referredFilter],
              page,
              per_page: perPage,
              order_by: orderBy,
              order,
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
