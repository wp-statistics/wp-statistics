import { queryOptions } from '@tanstack/react-query'

import type { Filter } from '@/components/custom/filter-bar'
import { clientRequest } from '@/lib/client-request'
import { WordPress } from '@/lib/wordpress'

export interface TopPageRecord {
  page_uri: string
  page_title: string
  page_wp_id: number | null
  visitors: number
  views: number
  bounce_rate: number | null
  avg_time_on_page: number | null
}

export interface GetTopPagesResponse {
  success: boolean
  data: {
    rows: TopPageRecord[]
    total: number
    totals?: {
      visitors: number
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
    preferences?: {
      columns: string[]
    }
  }
}

// API filter format: { filter_key: { operator: value } }
export type ApiFilters = Record<string, Record<string, string | string[]>>

export interface GetTopPagesParams {
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

// Extract the field name from filter ID
const extractFilterKey = (filterId: string): string => {
  return filterId.split('-')[0]
}

// Transform UI filters to API format
const transformFiltersToApi = (filters: Filter[]): ApiFilters => {
  const apiFilters: ApiFilters = {}

  for (const filter of filters) {
    const filterKey = extractFilterKey(filter.id)
    const operator = filter.rawOperator || filter.operator
    const rawValue = filter.rawValue ?? filter.value
    const value: string | string[] = Array.isArray(rawValue)
      ? rawValue
      : typeof rawValue === 'number'
        ? String(rawValue)
        : rawValue

    apiFilters[filterKey] = {
      [operator]: value,
    }
  }

  return apiFilters
}

// Map frontend column names to API column names
const columnMapping: Record<string, string> = {
  page: 'page_uri',
  visitors: 'visitors',
  views: 'views',
  bounceRate: 'bounce_rate',
  sessionDuration: 'avg_time_on_page',
}

// Default columns when no specific columns are provided
const DEFAULT_COLUMNS = ['page_uri', 'page_title', 'page_wp_id', 'visitors', 'views', 'bounce_rate', 'avg_time_on_page']

export const getTopPagesQueryOptions = ({
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
}: GetTopPagesParams) => {
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
      'top-pages',
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
      clientRequest.post<GetTopPagesResponse>(
        '',
        {
          sources: ['visitors', 'views', 'bounce_rate', 'avg_time_on_page'],
          group_by: ['page'],
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
          ...(Object.keys(apiFilters).length > 0 && { filters: apiFilters }),
        },
        {
          params: {
            action: WordPress.getInstance().getAnalyticsAction(),
          },
        }
      ),
  })
}
