import { queryOptions } from '@tanstack/react-query'

import { clientRequest } from '@/lib/client-request'
import { WordPress } from '@/lib/wordpress'

export interface AuthorPageRecord {
  page_uri: string
  page_title: string
  views: number
}

export interface GetAuthorPagesResponse {
  success: boolean
  data: {
    rows: AuthorPageRecord[]
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

export interface GetAuthorPagesParams {
  page: number
  per_page: number
  date_from: string
  date_to: string
  previous_date_from?: string
  previous_date_to?: string
}

export const getAuthorPagesQueryOptions = ({
  page,
  per_page,
  date_from,
  date_to,
  previous_date_from,
  previous_date_to,
}: GetAuthorPagesParams) => {
  const hasCompare = !!(previous_date_from && previous_date_to)

  return queryOptions({
    queryKey: [
      'author-pages',
      page,
      per_page,
      date_from,
      date_to,
      previous_date_from,
      previous_date_to,
      hasCompare,
    ],
    queryFn: () =>
      clientRequest.post<GetAuthorPagesResponse>(
        '',
        {
          sources: ['views'],
          group_by: ['page'],
          filters: {
            post_type: { is: 'author_archive' },
          },
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
        },
        {
          params: {
            action: WordPress.getInstance().getAnalyticsAction(),
          },
        }
      ),
  })
}
