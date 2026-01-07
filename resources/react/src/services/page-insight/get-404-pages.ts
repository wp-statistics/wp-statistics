import { queryOptions } from '@tanstack/react-query'

import type { Filter } from '@/components/custom/filter-bar'
import { transformFiltersToApi, type ApiFilters } from '@/lib/api-filter-transform'
import { clientRequest } from '@/lib/client-request'
import { WordPress } from '@/lib/wordpress'

export type { ApiFilters }

export interface Page404Record {
  page_uri: string
  page_uri_id: number
  views: number
}

export interface Get404PagesResponse {
  success: boolean
  data: {
    rows: Page404Record[]
    total: number
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

export interface Get404PagesParams {
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

export const get404PagesQueryOptions = ({
  page,
  per_page,
  order_by,
  order,
  date_from,
  date_to,
  previous_date_from,
  previous_date_to,
  filters = [],
}: Get404PagesParams) => {
  const apiFilters = transformFiltersToApi(filters)
  const hasCompare = !!(previous_date_from && previous_date_to)

  return queryOptions({
    queryKey: [
      '404-pages',
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
      clientRequest.post<Get404PagesResponse>(
        '',
        {
          sources: ['views'],
          group_by: ['page'],
          columns: ['page_uri', 'page_uri_id', 'views'],
          filters: [
            {
              key: 'post_type',
              operator: 'is',
              value: '404',
            },
            ...Object.entries(apiFilters).map(([key, val]) => ({
              key,
              ...val,
            })),
          ],
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
        },
        {
          params: {
            action: WordPress.getInstance().getAnalyticsAction(),
          },
        }
      ),
  })
}
