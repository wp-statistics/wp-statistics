import { queryOptions } from '@tanstack/react-query'

import type { Filter } from '@/components/custom/filter-bar'
import { transformFiltersToApi, type ApiFilters } from '@/lib/api-filter-transform'
import { clientRequest } from '@/lib/client-request'
import { WordPress } from '@/lib/wordpress'

export type { ApiFilters }

export interface EntryPageRecord {
  page_uri: string
  page_uri_id: number
  resource_id: number | null
  page_title: string
  page_wp_id: number | null
  page_type: string
  sessions: number
  bounce_rate: number | null
}

export interface GetEntryPagesResponse {
  success: boolean
  data: {
    rows: EntryPageRecord[]
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

export interface GetEntryPagesParams {
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

export const getEntryPagesQueryOptions = ({
  page,
  per_page,
  order_by,
  order,
  date_from,
  date_to,
  previous_date_from,
  previous_date_to,
  filters = [],
}: GetEntryPagesParams) => {
  const apiFilters = transformFiltersToApi(filters)
  const hasCompare = !!(previous_date_from && previous_date_to)

  return queryOptions({
    queryKey: [
      'entry-pages',
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
      clientRequest.post<GetEntryPagesResponse>(
        '',
        {
          sources: ['sessions', 'bounce_rate'],
          group_by: ['entry_page'],
          columns: ['page_uri', 'page_uri_id', 'resource_id', 'page_title', 'page_wp_id', 'page_type', 'sessions', 'bounce_rate'],
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
