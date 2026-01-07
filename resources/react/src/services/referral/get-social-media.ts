import { queryOptions } from '@tanstack/react-query'

import type { Filter } from '@/components/custom/filter-bar'
import { transformFiltersToApi } from '@/lib/api-filter-transform'
import { clientRequest } from '@/lib/client-request'
import { WordPress } from '@/lib/wordpress'

export interface SocialMediaRow {
  referrer_domain: string
  referrer_name: string
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
export interface SocialMediaChartResponse {
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
export interface SocialMediaTableResponse {
  success: boolean
  data: {
    rows: SocialMediaRow[]
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
export interface SocialMediaBatchResponse {
  success: boolean
  items: {
    chart?: SocialMediaChartResponse
    table?: SocialMediaTableResponse
  }
  errors?: Record<string, { code: string; message: string }>
  skipped?: string[]
}

export type SocialType = 'all' | 'organic' | 'paid'

export interface GetSocialMediaParams {
  dateFrom: string
  dateTo: string
  compareDateFrom?: string
  compareDateTo?: string
  timeframe?: 'daily' | 'weekly' | 'monthly'
  socialType?: SocialType
  page?: number
  perPage?: number
  orderBy?: string
  order?: 'ASC' | 'DESC'
  filters?: Filter[]
}

export const getSocialMediaQueryOptions = ({
  dateFrom,
  dateTo,
  compareDateFrom,
  compareDateTo,
  timeframe = 'daily',
  socialType = 'all',
  page = 1,
  perPage = 20,
  orderBy = 'visitors',
  order = 'DESC',
  filters = [],
}: GetSocialMediaParams) => {
  const dateGroupBy = timeframe === 'monthly' ? 'month' : timeframe === 'weekly' ? 'week' : 'date'
  const apiFilters = transformFiltersToApi(filters)
  const hasCompare = !!(compareDateFrom && compareDateTo)

  // Base filters for social media traffic
  const baseFilters = [
    { key: 'referrer_domain', operator: 'is_not_empty', value: '' },
  ]

  // Add channel filter based on social type
  if (socialType === 'organic') {
    baseFilters.push({ key: 'referrer_channel', operator: 'is', value: 'organic-social' })
  } else if (socialType === 'paid') {
    baseFilters.push({ key: 'referrer_channel', operator: 'is', value: 'paid-social' })
  } else {
    // All social - include both organic and paid
    baseFilters.push({ key: 'referrer_channel', operator: 'contains', value: 'social' })
  }

  return queryOptions({
    queryKey: [
      'social-media',
      dateFrom,
      dateTo,
      compareDateFrom,
      compareDateTo,
      timeframe,
      socialType,
      page,
      perPage,
      orderBy,
      order,
      apiFilters,
    ],
    queryFn: () =>
      clientRequest.post<SocialMediaBatchResponse>(
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
            // Chart: top 3 social platforms trend
            {
              id: 'chart',
              sources: ['visitors'],
              group_by: ['referrer', dateGroupBy],
              filters: baseFilters,
              per_page: 3,
              order_by: 'visitors',
              order: 'DESC',
              format: 'chart',
              show_totals: false,
              compare: true,
            },
            // Table: social media platforms list
            {
              id: 'table',
              sources: ['visitors', 'views', 'avg_session_duration', 'bounce_rate', 'pages_per_session'],
              group_by: ['referrer'],
              columns: [
                'referrer_domain',
                'referrer_name',
                'referrer_channel',
                'visitors',
                'views',
                'avg_session_duration',
                'bounce_rate',
                'pages_per_session',
              ],
              filters: baseFilters,
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
