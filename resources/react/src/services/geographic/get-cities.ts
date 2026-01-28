import { queryOptions } from '@tanstack/react-query'

import type { Filter } from '@/components/custom/filter-bar'
import { type ApiFilters, transformFiltersToApi } from '@/lib/api-filter-transform'
import { clientRequest } from '@/lib/client-request'
import { createListParams, queryKeys, type QueryFilter } from '@/lib/query-keys'
import { WordPress } from '@/lib/wordpress'

// Re-export ApiFilters for backward compatibility
export type { ApiFilters }

/**
 * API response record for a single city
 */
export interface CityRecord {
  city_id: number
  city_name: string
  city_region_name: string
  country_code: string
  country_name: string
  visitors: number
  views: number
  previous?: {
    visitors?: number
    views?: number
  }
}

/**
 * Table query result structure (used in batch responses)
 */
export interface CitiesTableResult {
  success: boolean
  data: {
    rows: CityRecord[]
  }
  meta?: {
    date_from: string
    date_to: string
    page: number
    per_page: number
    total_pages: number
    total_rows: number
    preferences?: {
      columns?: string[]
      comparison_columns?: string[]
    }
  }
}

/**
 * Batch response structure for cities query
 */
export interface GetCitiesResponse {
  success: boolean
  items: {
    cities?: CitiesTableResult
  }
  errors?: Record<string, { code: string; message: string }>
  skipped?: string[]
}

export interface GetCitiesParams {
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
  /** Query-level filters passed directly to the batch queries */
  queryFilters?: QueryFilter[]
}

// Map frontend column names to API column names
const columnMapping: Record<string, string> = {
  city: 'city_name',
  region: 'city_region_name',
  country: 'country_name',
  visitors: 'visitors',
  views: 'views',
}

// Default columns when no specific columns are provided
const DEFAULT_COLUMNS = [
  'city_id',
  'city_name',
  'city_region_name',
  'country_code',
  'country_name',
  'visitors',
  'views',
]

export const getCitiesQueryOptions = ({
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
  queryFilters,
}: GetCitiesParams) => {
  // Map frontend column name to API column name
  const apiOrderBy = columnMapping[order_by] || order_by
  // Transform UI filters to API format
  const apiFilters = transformFiltersToApi(filters)
  // Check if compare dates are provided
  const hasCompare = !!(previous_date_from && previous_date_to)
  // Use provided columns or default to all columns
  const apiColumns = columns && columns.length > 0 ? columns : DEFAULT_COLUMNS

  return queryOptions({
    // eslint-disable-next-line @tanstack/query/exhaustive-deps -- hasCompare is derived from compareDateFrom/compareDateTo which are in the key
    queryKey: queryKeys.geographic.citiesList(
      createListParams(date_from, date_to, page, per_page, apiOrderBy, order, {
        compareDateFrom: previous_date_from,
        compareDateTo: previous_date_to,
        filters: apiFilters,
        context,
        columns: apiColumns,
        queryFilters,
      })
    ),
    queryFn: () =>
      clientRequest.post<GetCitiesResponse>(
        '',
        {
          date_from,
          date_to,
          compare: hasCompare,
          ...(hasCompare && {
            previous_date_from,
            previous_date_to,
          }),
          ...(Object.keys(apiFilters).length > 0 && { filters: apiFilters }),
          queries: [
            {
              id: 'cities',
              sources: ['visitors', 'views'],
              group_by: ['city'],
              columns: apiColumns,
              page,
              per_page,
              order_by: apiOrderBy,
              order: order.toUpperCase(),
              format: 'table',
              compare: hasCompare,
              ...(context && { context }),
              ...(queryFilters?.length && { filters: queryFilters }),
            },
          ],
        },
        {
          params: {
            action: WordPress.getInstance().getAnalyticsAction(),
          },
        }
      ),
  })
}

/**
 * Helper to extract cities data from batch response
 */
export function extractCitiesData(response: { data?: GetCitiesResponse } | undefined): {
  rows: CityRecord[]
  meta: { totalRows: number; totalPages: number; page: number } | null
} {
  const citiesResult = response?.data?.items?.cities

  if (!citiesResult?.success) {
    return { rows: [], meta: null }
  }

  const rows = citiesResult.data?.rows || []

  const meta = citiesResult.meta
    ? {
        totalRows: Number(citiesResult.meta.total_rows) || 0,
        totalPages: Number(citiesResult.meta.total_pages) || 1,
        page: Number(citiesResult.meta.page) || 1,
      }
    : null

  return { rows, meta }
}
