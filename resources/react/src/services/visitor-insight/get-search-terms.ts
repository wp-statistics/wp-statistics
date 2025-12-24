import { queryOptions } from '@tanstack/react-query'

import { clientRequest } from '@/lib/client-request'

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
  const hasCompare = previous_date_from && previous_date_to

  return queryOptions({
    queryKey: ['search-terms', page, per_page, date_from, date_to, previous_date_from, previous_date_to],
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
            action: 'wp_statistics_analytics',
          },
        }
      ),
  })
}
