import { queryOptions } from '@tanstack/react-query'

import { clientRequest } from '@/lib/client-request'
import { WordPress } from '@/lib/wordpress'

export interface NotFoundPageRecord {
  page_uri: string
  views: number
}

export interface Get404PagesResponse {
  success: boolean
  data: {
    rows: NotFoundPageRecord[]
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
  date_from: string
  date_to: string
  previous_date_from?: string
  previous_date_to?: string
}

export const get404PagesQueryOptions = ({
  page,
  per_page,
  date_from,
  date_to,
  previous_date_from,
  previous_date_to,
}: Get404PagesParams) => {
  const hasCompare = !!(previous_date_from && previous_date_to)

  return queryOptions({
    queryKey: ['404-pages', page, per_page, date_from, date_to, previous_date_from, previous_date_to, hasCompare],
    queryFn: () =>
      clientRequest.post<Get404PagesResponse>(
        '',
        {
          sources: ['views'],
          group_by: ['page'],
          columns: ['page_uri', 'views'],
          date_from,
          date_to,
          compare: hasCompare,
          ...(hasCompare && {
            previous_date_from,
            previous_date_to,
          }),
          page,
          per_page,
          order_by: 'views',
          order: 'DESC',
          format: 'table',
          show_totals: false,
          filters: {
            post_type: '404',
          },
        },
        {
          params: {
            action: WordPress.getInstance().getAnalyticsAction(),
          },
        }
      ),
  })
}
