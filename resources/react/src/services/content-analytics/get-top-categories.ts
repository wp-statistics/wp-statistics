import { queryOptions } from '@tanstack/react-query'

import type { Filter } from '@/components/custom/filter-bar'
import { type ApiFilters, transformFiltersToApi } from '@/lib/api-filter-transform'
import { clientRequest } from '@/lib/client-request'
import { WordPress } from '@/lib/wordpress'

// Re-export ApiFilters type for backward compatibility
export type { ApiFilters }

/**
 * Top Category record from API
 */
export interface TopCategoryRecord {
  term_id: number
  term_name: string
  term_slug: string
  taxonomy_type: string
  taxonomy_label?: string
  term_link?: string
  visitors: number
  views: number
  published_content: number
  bounce_rate: number | null
  avg_time_on_page: number | null
}

/**
 * API response for Top Categories
 */
export interface GetTopCategoriesResponse {
  success: boolean
  data: {
    rows: TopCategoryRecord[]
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

export interface GetTopCategoriesParams {
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
  taxonomyType?: string
}

// Map frontend column names to API column names
const columnMapping: Record<string, string> = {
  termName: 'term_name',
  visitors: 'visitors',
  views: 'views',
  published: 'published_content',
  viewsPerContent: 'views', // Calculated client-side, but sort by views
  bounceRate: 'bounce_rate',
  timeOnPage: 'avg_time_on_page',
}

// Default columns when no specific columns are provided
const DEFAULT_COLUMNS = [
  'term_id',
  'term_name',
  'term_slug',
  'taxonomy_type',
  'taxonomy_label',
  'term_link',
  'visitors',
  'views',
  'published_content',
  'bounce_rate',
  'avg_time_on_page',
]

export const getTopCategoriesQueryOptions = ({
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
  taxonomyType = 'category',
}: GetTopCategoriesParams) => {
  // Map frontend column name to API column name
  const apiOrderBy = columnMapping[order_by] || order_by
  // Transform UI filters to API format
  const apiFilters = transformFiltersToApi(filters)
  // Check if compare dates are provided
  const hasCompare = !!(previous_date_from && previous_date_to)
  // Use provided columns or default to all columns
  const apiColumns = columns && columns.length > 0 ? columns : DEFAULT_COLUMNS

  // Add taxonomy_type filter if not already in filters
  const hasTaxonomyTypeFilter = Object.keys(apiFilters).includes('taxonomy_type')
  const filtersWithTaxonomy = hasTaxonomyTypeFilter
    ? apiFilters
    : {
        ...apiFilters,
        taxonomy_type: { is: taxonomyType },
      }

  return queryOptions({
    queryKey: [
      'top-categories',
      page,
      per_page,
      apiOrderBy,
      order,
      date_from,
      date_to,
      previous_date_from,
      previous_date_to,
      filtersWithTaxonomy,
      context,
      apiColumns,
    ],
    queryFn: () =>
      clientRequest.post<GetTopCategoriesResponse>(
        '',
        {
          sources: ['visitors', 'views', 'published_content', 'bounce_rate', 'avg_time_on_page'],
          group_by: ['taxonomy'],
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
          filters: filtersWithTaxonomy,
        },
        {
          params: {
            action: WordPress.getInstance().getAnalyticsAction(),
          },
        }
      ),
  })
}
