import { queryOptions } from '@tanstack/react-query'

import { clientRequest } from '@/lib/client-request'

export interface SearchTerm {
  search_term: string
  views: number
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
  }
}

export interface GetSearchTermsParams {
  page: number
  per_page: number
  date_from: string
  date_to: string
}

export const getSearchTermsQueryOptions = ({ page, per_page, date_from, date_to }: GetSearchTermsParams) => {
  return queryOptions({
    queryKey: ['search-terms', page, per_page, date_from, date_to],
    queryFn: () =>
      clientRequest.post<GetSearchTermsResponse>(
        '',
        {
          sources: ['searches'],
          group_by: ['search_term'],
          columns: ['search_term', 'searches'],
          date_from,
          date_to,
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
