import { queryOptions } from '@tanstack/react-query'

import type { Filter } from '@/components/custom/filter-bar'
import { type ApiFilters, transformFiltersToApi } from '@/lib/api-filter-transform'
import { clientRequest } from '@/lib/client-request'
import { createListParams, queryKeys, type QueryFilter } from '@/lib/query-keys'
import { WordPress } from '@/lib/wordpress'

// Re-export ApiFilters for backward compatibility
export type { ApiFilters }

/**
 * API response record for a single country
 */
export interface CountryRecord {
  country_code: string
  country_name: string
  visitors: number
  views: number
  bounce_rate: number | null
  avg_session_duration: number | null
  previous?: {
    visitors?: number
    views?: number
    bounce_rate?: number
    avg_session_duration?: number
  }
}

/**
 * Table query result structure (used in batch responses)
 */
export interface CountriesTableResult {
  success: boolean
  data: {
    rows: CountryRecord[]
    totals?: {
      visitors?: number | string
      views?: number | string
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
      columns?: string[]
      comparison_columns?: string[]
    }
  }
}

/**
 * Flat totals query result (for separate totals query)
 * FlatFormatter returns { success, items, totals, meta } structure
 */
export interface TotalsResult {
  success: boolean
  items?: unknown[]
  totals?: {
    visitors?: number | string
    views?: number | string
  }
  meta?: Record<string, unknown>
}

/**
 * Batch response structure for countries query
 */
export interface GetCountriesResponse {
  success: boolean
  items: {
    countries?: CountriesTableResult
    totals?: TotalsResult
  }
  errors?: Record<string, { code: string; message: string }>
  skipped?: string[]
}

export interface GetCountriesParams {
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
  /** Query-level filters passed directly to the batch queries (e.g., for continent filtering) */
  queryFilters?: QueryFilter[]
}

// Map frontend column names to API column names
const columnMapping: Record<string, string> = {
  country: 'country_name',
  visitors: 'visitors',
  views: 'views',
  bounceRate: 'bounce_rate',
  sessionDuration: 'avg_session_duration',
}

// Default columns when no specific columns are provided
const DEFAULT_COLUMNS = [
  'country_code',
  'country_name',
  'visitors',
  'views',
  'bounce_rate',
  'avg_session_duration',
]

export const getCountriesQueryOptions = ({
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
}: GetCountriesParams) => {
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
    queryKey: queryKeys.geographic.countries(
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
      clientRequest.post<GetCountriesResponse>(
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
          // Use batch query format with separate totals query
          queries: [
            {
              id: 'countries',
              sources: ['visitors', 'views', 'bounce_rate', 'avg_session_duration'],
              group_by: ['country'],
              columns: apiColumns,
              page,
              per_page,
              order_by: apiOrderBy,
              order: order.toUpperCase(),
              format: 'table',
              show_totals: false, // Don't need totals in paginated query
              compare: hasCompare,
              ...(context && { context }),
              ...(queryFilters?.length && { filters: queryFilters }),
            },
            {
              id: 'totals',
              sources: ['visitors', 'views'],
              group_by: [], // No grouping = site-wide aggregate
              format: 'flat',
              compare: false, // No comparison needed for totals
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
 * Helper to extract countries data from batch response
 */
export function extractCountriesData(response: { data?: GetCountriesResponse } | undefined): {
  rows: CountryRecord[]
  totals: { visitors: number; views: number }
  meta: { totalRows: number; totalPages: number; page: number } | null
} {
  const countriesResult = response?.data?.items?.countries
  const totalsResult = response?.data?.items?.totals

  if (!countriesResult?.success) {
    return { rows: [], totals: { visitors: 0, views: 0 }, meta: null }
  }

  const rows = countriesResult.data?.rows || []

  // Extract totals from the separate totals query (preferred) or fallback to countries query totals
  // FlatFormatter returns { success, items, totals: { visitors, views }, meta } structure
  // Use nullish coalescing (??) instead of || to handle 0 values correctly
  const totals = {
    visitors: Number(
      (totalsResult?.success ? totalsResult.totals?.visitors : undefined) ??
        countriesResult.data?.totals?.visitors ??
        0
    ),
    views: Number(
      (totalsResult?.success ? totalsResult.totals?.views : undefined) ??
        countriesResult.data?.totals?.views ??
        0
    ),
  }

  const meta = countriesResult.meta
    ? {
        totalRows: Number(countriesResult.meta.total_rows) || 0,
        totalPages: Number(countriesResult.meta.total_pages) || 1,
        page: Number(countriesResult.meta.page) || 1,
      }
    : null

  return { rows, totals, meta }
}
