import { queryOptions } from '@tanstack/react-query'

import { clientRequest } from '@/lib/client-request'
import { WordPress } from '@/lib/wordpress'

export interface ExitPageRecord {
  page_uri: string
  page_title: string
  page_type?: string
  page_wp_id?: number | string | null
  sessions: number
  previous?: {
    sessions: number
  }
}

export interface GetExitPagesResponse {
  success: boolean
  data: {
    rows: ExitPageRecord[]
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

export interface GetExitPagesParams {
  page: number
  per_page: number
  date_from: string
  date_to: string
  previous_date_from?: string
  previous_date_to?: string
}

export const getExitPagesQueryOptions = ({
  page,
  per_page,
  date_from,
  date_to,
  previous_date_from,
  previous_date_to,
}: GetExitPagesParams) => {
  const hasCompare = !!(previous_date_from && previous_date_to)

  return queryOptions({
    queryKey: [
      'exit-pages',
      page,
      per_page,
      date_from,
      date_to,
      previous_date_from,
      previous_date_to,
      hasCompare,
    ],
    queryFn: () =>
      clientRequest.post<GetExitPagesResponse>(
        '',
        {
          sources: ['sessions'],
          group_by: ['exit_page'],
          date_from,
          date_to,
          compare: hasCompare,
          ...(hasCompare && {
            previous_date_from,
            previous_date_to,
          }),
          page,
          per_page,
          order_by: 'sessions',
          order: 'DESC',
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
