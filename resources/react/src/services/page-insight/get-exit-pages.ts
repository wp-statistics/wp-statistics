import { queryOptions } from '@tanstack/react-query'

import type { Filter } from '@/components/custom/filter-bar'
import { transformFiltersToApi, type ApiFilters } from '@/lib/api-filter-transform'
import { clientRequest } from '@/lib/client-request'
import { WordPress } from '@/lib/wordpress'

export type { ApiFilters }

export interface ExitPageRecord {
  page_uri: string
  page_uri_id: number
  resource_id: number | null
  page_title: string
  page_wp_id: number | null
  page_type: string
  sessions: number
}

export interface GetExitPagesResponse {
  success: boolean
  data: {
    rows: ExitPageRecord[]
    total: number
    totals?: {
      sessions: number
    }
  }
  meta?: {
    date_from: string
    date_to: string
    page: number
    per_page: number
    total_pages: number
    total_rows: number
  }
}

export interface GetExitPagesParams {
  page: number
  per_page: number
  order_by: string
  order: 'asc' | 'desc'
  date_from: string
  date_to: string
  previous_date_from?: string
  previous_date_to?: string
  filters?: Filter[]
}

export const getExitPagesQueryOptions = ({
  page,
  per_page,
  order_by,
  order,
  date_from,
  date_to,
  previous_date_from,
  previous_date_to,
  filters = [],
}: GetExitPagesParams) => {
  const apiFilters = transformFiltersToApi(filters)
  const hasCompare = !!(previous_date_from && previous_date_to)

  return queryOptions({
    queryKey: [
      'exit-pages',
      page,
      per_page,
      order_by,
      order,
      date_from,
      date_to,
      previous_date_from,
      previous_date_to,
      apiFilters,
    ],
    queryFn: () =>
      clientRequest.post<GetExitPagesResponse>(
        '',
        {
          sources: ['sessions'],
          group_by: ['exit_page'],
          columns: ['page_uri', 'page_uri_id', 'resource_id', 'page_title', 'page_wp_id', 'page_type', 'sessions'],
          date_from,
          date_to,
          compare: hasCompare,
          ...(hasCompare && {
            previous_date_from,
            previous_date_to,
          }),
          page,
          per_page,
          order_by,
          order: order.toUpperCase(),
          format: 'table',
          show_totals: false,
          ...(Object.keys(apiFilters).length > 0 && { filters: apiFilters }),
        },
        {
          params: {
            action: WordPress.getInstance().getAnalyticsAction(),
          },
        }
      ),
  })
}
