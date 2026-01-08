import { queryOptions } from '@tanstack/react-query'

import type { Filter } from '@/components/custom/filter-bar'
import { type ApiFilters, transformFiltersToApi } from '@/lib/api-filter-transform'
import { clientRequest } from '@/lib/client-request'
import { WordPress } from '@/lib/wordpress'

// Re-export ApiFilters type for backward compatibility
export type { ApiFilters }

/**
 * Top Author record from API
 */
export interface TopAuthorRecord {
  author_id: number
  author_name: string
  author_avatar?: string
  author_posts_url?: string
  visitors: number
  views: number
  published_content: number
  bounce_rate: number | null
  avg_time_on_page: number | null
}

/**
 * API response for Top Authors
 */
export interface GetTopAuthorsResponse {
  success: boolean
  data: {
    rows: TopAuthorRecord[]
    totals?: {
      visitors: number
      views: number
      published_content: number
    }
  }
  meta?: {
    date_from: string
    date_to: string
    page: number
    per_page: number
    total_pages: number
    total_rows: number
    preferences?: {
      columns: string[]
    }
  }
}

export interface GetTopAuthorsParams {
  page: number
  per_page: number
  order_by: string
  order: 'asc' | 'desc'
  date_from: string
  date_to: string
  previous_date_from?: string
  previous_date_to?: string
  filters?: Filter[]
  context?: string
  columns?: string[]
}

// Map frontend column names to API column names
const columnMapping: Record<string, string> = {
  authorName: 'author_name',
  visitors: 'visitors',
  views: 'views',
  published: 'published_content',
  viewsPerContent: 'views', // Calculated client-side, but sort by views
  bounceRate: 'bounce_rate',
  timeOnPage: 'avg_time_on_page',
}

// Default columns when no specific columns are provided
const DEFAULT_COLUMNS = [
  'author_id',
  'author_name',
  'author_avatar',
  'author_posts_url',
  'visitors',
  'views',
  'published_content',
  'bounce_rate',
  'avg_time_on_page',
]

export const getTopAuthorsQueryOptions = ({
  page,
  per_page,
  order_by,
  order,
  date_from,
  date_to,
  previous_date_from,
  previous_date_to,
  filters = [],
  context,
  columns,
}: GetTopAuthorsParams) => {
  // Map frontend column name to API column name
  const apiOrderBy = columnMapping[order_by] || order_by
  // Transform UI filters to API format
  const apiFilters = transformFiltersToApi(filters)
  // Check if compare dates are provided
  const hasCompare = !!(previous_date_from && previous_date_to)
  // Use provided columns or default to all columns
  const apiColumns = columns && columns.length > 0 ? columns : DEFAULT_COLUMNS

  return queryOptions({
    queryKey: [
      'top-authors',
      page,
      per_page,
      apiOrderBy,
      order,
      date_from,
      date_to,
      previous_date_from,
      previous_date_to,
      apiFilters,
      context,
      apiColumns,
    ],
    queryFn: () =>
      clientRequest.post<GetTopAuthorsResponse>(
        '',
        {
          sources: ['visitors', 'views', 'published_content', 'bounce_rate', 'avg_time_on_page'],
          group_by: ['author'],
          columns: apiColumns,
          date_from,
          date_to,
          compare: hasCompare,
          ...(hasCompare && {
            previous_date_from,
            previous_date_to,
          }),
          page,
          per_page,
          order_by: apiOrderBy,
          order: order.toUpperCase(),
          format: 'table',
          ...(context && { context }),
          show_totals: false,
          filters: apiFilters,
        },
        {
          params: {
            action: WordPress.getInstance().getAnalyticsAction(),
          },
        }
      ),
  })
}
