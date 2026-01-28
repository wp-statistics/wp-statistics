import { queryOptions } from '@tanstack/react-query'

import { clientRequest } from '@/lib/client-request'
import { WordPress } from '@/lib/wordpress'

export interface SearchTerm {
  search_term: string
  searches: string
}

export interface GetSearchTermsResponse {
  success: boolean
  data: {
    rows: SearchTerm[]
    total: number
    totals?: {
      views: number
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

export interface GetSearchTermsParams {
  page: number
  per_page: number
  date_from: string
  date_to: string
  previous_date_from?: string
  previous_date_to?: string
}

export const getSearchTermsQueryOptions = ({
  page,
  per_page,
  date_from,
  date_to,
  previous_date_from,
  previous_date_to,
}: GetSearchTermsParams) => {
  // Check if compare dates are provided (must be boolean, not the date value)
  const hasCompare = !!(previous_date_from && previous_date_to)

  return queryOptions({
    queryKey: ['search-terms', page, per_page, date_from, date_to, previous_date_from, previous_date_to, hasCompare],
    queryFn: () =>
      clientRequest.post<GetSearchTermsResponse>(
        '',
        {
          sources: ['searches'],
          group_by: ['search_term'],
          columns: ['search_term', 'searches'],
          date_from,
          date_to,
          compare: hasCompare,
          ...(hasCompare && {
            previous_date_from,
            previous_date_to,
          }),
          page,
          per_page,
          order_by: 'searches',
          order: 'DESC',
          format: 'table',
          context: 'search_terms_page_table',
        },
        {
          params: {
            action: WordPress.getInstance().getAnalyticsAction(),
          },
        }
      ),
  })
}
