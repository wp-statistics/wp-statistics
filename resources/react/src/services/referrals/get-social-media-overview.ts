import { queryOptions } from '@tanstack/react-query'

import type { FilterRowData } from '@/components/custom/filter-button'
import { type ApiFilters, transformFiltersToApi } from '@/lib/api-filter-transform'
import { clientRequest } from '@/lib/client-request'
import { WordPress } from '@/lib/wordpress'

import type { ReferrerRecord } from './get-referrers'

// Re-export types
export type { ApiFilters }

/**
 * Chart format response for social media trends.
 * Uses dynamic datasets for top 3 social platforms + Total.
 */
export interface SocialMediaChartResponse {
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
 * Table format response for social media referrers
 */
export interface SocialMediaTableResponse {
  success: boolean
  data: {
    rows: ReferrerRecord[]
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
 * Batch response structure for social media overview
 */
export interface SocialMediaOverviewResponse {
  success: boolean
  items: {
    chart?: SocialMediaChartResponse
    table?: SocialMediaTableResponse
  }
  errors?: Record<string, { code: string; message: string }>
  skipped?: string[]
  meta?: {
    preferences?: Record<string, unknown>
  }
}

export interface GetSocialMediaOverviewParams {
  dateFrom: string
  dateTo: string
  compareDateFrom?: string
  compareDateTo?: string
  filters?: FilterRowData[]
  /** Additional API filters (e.g., social type filter for all/organic/paid) */
  apiFilters?: ApiFilters
  // Table params
  page?: number
  perPage?: number
  orderBy?: string
  order?: 'asc' | 'desc'
  context?: string
}

// Map frontend column names to API column names for sorting
const columnMapping: Record<string, string> = {
  domain: 'referrer_domain',
  name: 'referrer_name',
  sessionDuration: 'avg_session_duration',
  bounceRate: 'bounce_rate',
  pagesPerSession: 'pages_per_session',
}

/**
 * Creates query options for fetching social media overview data.
 * Uses batch queries to fetch both chart and table data in a single request.
 */
export const getSocialMediaOverviewQueryOptions = ({
  dateFrom,
  dateTo,
  compareDateFrom,
  compareDateTo,
  filters = [],
  apiFilters = {},
  page = 1,
  perPage = 25,
  orderBy = 'visitors',
  order = 'desc',
  context,
}: GetSocialMediaOverviewParams) => {
  // Map frontend column name to API column name
  const apiOrderBy = columnMapping[orderBy] || orderBy

  // Transform UI filters to API format and merge with social type filter
  const uiApiFilters = transformFiltersToApi(filters)
  const mergedFilters = { ...uiApiFilters, ...apiFilters }

  // Check if compare dates are provided
  const hasCompare = !!(compareDateFrom && compareDateTo)

  return queryOptions({
    queryKey: [
      'social-media-overview',
      dateFrom,
      dateTo,
      compareDateFrom,
      compareDateTo,
      page,
      perPage,
      apiOrderBy,
      order,
      mergedFilters,
      context,
    ],
    queryFn: () =>
      clientRequest.post<SocialMediaOverviewResponse>(
        '',
        {
          date_from: dateFrom,
          date_to: dateTo,
          compare: hasCompare,
          ...(hasCompare && {
            previous_date_from: compareDateFrom,
            previous_date_to: compareDateTo,
          }),
          ...(Object.keys(mergedFilters).length > 0 && { filters: mergedFilters }),
          queries: [
            // Chart query: Use social media chart provider for top 3 platforms + total
            {
              id: 'chart',
              chart: 'social_media_chart',
              compare: hasCompare,
            },
            // Table query: Referrers filtered by social type
            {
              id: 'table',
              sources: ['visitors', 'views', 'avg_session_duration', 'bounce_rate', 'pages_per_session'],
              group_by: ['referrer'],
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
