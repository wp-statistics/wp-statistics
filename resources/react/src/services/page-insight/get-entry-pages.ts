import { queryOptions } from '@tanstack/react-query'

import { clientRequest } from '@/lib/client-request'
import { WordPress } from '@/lib/wordpress'

export interface EntryPageRecord {
  page_uri: string
  page_title: string
  sessions: number
  previous?: {
    sessions: number
  }
}

export interface GetEntryPagesResponse {
  success: boolean
  data: {
    rows: EntryPageRecord[]
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

export interface GetEntryPagesParams {
  page: number
  per_page: number
  date_from: string
  date_to: string
  previous_date_from?: string
  previous_date_to?: string
}

export const getEntryPagesQueryOptions = ({
  page,
  per_page,
  date_from,
  date_to,
  previous_date_from,
  previous_date_to,
}: GetEntryPagesParams) => {
  const hasCompare = !!(previous_date_from && previous_date_to)

  return queryOptions({
    queryKey: [
      'entry-pages',
      page,
      per_page,
      date_from,
      date_to,
      previous_date_from,
      previous_date_to,
    ],
    queryFn: () =>
      clientRequest.post<GetEntryPagesResponse>(
        '',
        {
          sources: ['sessions'],
          group_by: ['entry_page'],
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
