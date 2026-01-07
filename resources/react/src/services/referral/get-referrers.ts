import { queryOptions } from '@tanstack/react-query'

import type { Filter } from '@/components/custom/filter-bar'
import { transformFiltersToApi } from '@/lib/api-filter-transform'
import { clientRequest } from '@/lib/client-request'
import { WordPress } from '@/lib/wordpress'

export interface ReferrerRow {
  referrer_id: number | string
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

export interface ReferrersResponse {
  success: boolean
  data: {
    rows: ReferrerRow[]
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

export interface GetReferrersParams {
  dateFrom: string
  dateTo: string
  compareDateFrom?: string
  compareDateTo?: string
  page?: number
  perPage?: number
  orderBy?: string
  order?: 'ASC' | 'DESC'
  filters?: Filter[]
}

export const getReferrersQueryOptions = ({
  dateFrom,
  dateTo,
  compareDateFrom,
  compareDateTo,
  page = 1,
  perPage = 20,
  orderBy = 'visitors',
  order = 'DESC',
  filters = [],
}: GetReferrersParams) => {
  const apiFilters = transformFiltersToApi(filters)
  const hasCompare = !!(compareDateFrom && compareDateTo)

  return queryOptions({
    queryKey: ['referrers', dateFrom, dateTo, compareDateFrom, compareDateTo, page, perPage, orderBy, order, apiFilters],
    queryFn: () =>
      clientRequest.post<ReferrersResponse>(
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
          sources: ['visitors', 'views', 'avg_session_duration', 'bounce_rate', 'pages_per_session'],
          group_by: ['referrer'],
          columns: [
            'referrer_id',
            'referrer_domain',
            'referrer_name',
            'referrer_channel',
            'visitors',
            'views',
            'avg_session_duration',
            'bounce_rate',
            'pages_per_session',
          ],
          page,
          per_page: perPage,
          order_by: orderBy,
          order,
          format: 'table',
          show_totals: true,
        },
        {
          params: {
            action: WordPress.getInstance().getAnalyticsAction(),
          },
        }
      ),
  })
}
