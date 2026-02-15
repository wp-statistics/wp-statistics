import { queryOptions } from '@tanstack/react-query'

import { clientRequest } from '@/lib/client-request'
import { WordPress } from '@/lib/wordpress'

export interface CategoryPageRecord {
  page_uri: string
  page_title: string
  views: number
  page_type?: string
  page_wp_id?: number | string
  resource_id?: number | string
}

export interface GetCategoryPagesResponse {
  success: boolean
  data: {
    rows: CategoryPageRecord[]
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

export interface GetCategoryPagesParams {
  page: number
  per_page: number
  date_from: string
  date_to: string
  previous_date_from?: string
  previous_date_to?: string
  taxonomyType?: string
}

export const getCategoryPagesQueryOptions = ({
  page,
  per_page,
  date_from,
  date_to,
  previous_date_from,
  previous_date_to,
  taxonomyType = 'category',
}: GetCategoryPagesParams) => {
  const hasCompare = !!(previous_date_from && previous_date_to)

  return queryOptions({
    queryKey: [
      'category-pages',
      page,
      per_page,
      date_from,
      date_to,
      previous_date_from,
      previous_date_to,
      taxonomyType,
      hasCompare,
    ],
    queryFn: () =>
      clientRequest.post<GetCategoryPagesResponse>(
        '',
        {
          sources: ['views'],
          group_by: ['page'],
          filters: {
            post_type: { is: taxonomyType },
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
