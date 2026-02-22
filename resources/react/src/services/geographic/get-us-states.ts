import { queryOptions } from '@tanstack/react-query'

import type { Filter } from '@/components/custom/filter-bar'
import { transformFiltersToApi } from '@/lib/api-filter-transform'
import { clientRequest } from '@/lib/client-request'
import { createListParams, queryKeys } from '@/lib/query-keys'
import { WordPress } from '@/lib/wordpress'

/**
 * API response record for a single US state
 */
export interface USStateRecord {
  region_code: string
  region_name: string
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
export interface USStatesTableResult {
  success: boolean
  data: {
    rows: USStateRecord[]
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
 * Batch response structure for US states query
 */
export interface GetUSStatesResponse {
  success: boolean
  items: {
    us_states?: USStatesTableResult
    totals?: TotalsResult
  }
  errors?: Record<string, { code: string; message: string }>
  skipped?: string[]
}

export interface GetUSStatesParams {
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
  region: 'region_name',
  visitors: 'visitors',
  views: 'views',
  bounceRate: 'bounce_rate',
  sessionDuration: 'avg_session_duration',
}

// Default columns when no specific columns are provided
const DEFAULT_COLUMNS = [
  'region_code',
  'region_name',
  'visitors',
  'views',
  'bounce_rate',
  'avg_session_duration',
]

// US country filter - hardcoded to filter for United States only
const US_COUNTRY_FILTER = [
  { key: 'country', operator: 'is', value: 'US' },
]

export const getUSStatesQueryOptions = ({
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
}: GetUSStatesParams) => {
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
    queryKey: queryKeys.geographic.usStates(
      createListParams(date_from, date_to, page, per_page, apiOrderBy, order, {
        compareDateFrom: previous_date_from,
        compareDateTo: previous_date_to,
        filters: apiFilters,
        context,
        columns: apiColumns,
      })
    ),
    queryFn: () =>
      clientRequest.post<GetUSStatesResponse>(
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
              id: 'us_states',
              sources: ['visitors', 'views', 'bounce_rate', 'avg_session_duration'],
              group_by: ['region'],
              columns: apiColumns,
              page,
              per_page,
              order_by: apiOrderBy,
              order: order.toUpperCase(),
              format: 'table',
              show_totals: false, // Don't need totals in paginated query
              compare: hasCompare,
              filters: US_COUNTRY_FILTER, // Hardcoded US filter
              ...(context && { context }),
            },
            {
              id: 'totals',
              sources: ['visitors', 'views'],
              group_by: [], // No grouping = site-wide aggregate
              format: 'flat',
              compare: false, // No comparison needed for totals
              filters: US_COUNTRY_FILTER, // Hardcoded US filter
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
 * Helper to extract US states data from batch response
 */
export function extractUSStatesData(response: { data?: GetUSStatesResponse } | undefined): {
  rows: USStateRecord[]
  totals: { visitors: number; views: number }
  meta: { totalRows: number; totalPages: number; page: number } | null
} {
  const statesResult = response?.data?.items?.us_states
  const totalsResult = response?.data?.items?.totals

  if (!statesResult?.success) {
    return { rows: [], totals: { visitors: 0, views: 0 }, meta: null }
  }

  const rows = statesResult.data?.rows || []

  // Extract totals from the separate totals query (preferred) or fallback to states query totals
  // FlatFormatter returns { success, items, totals: { visitors, views }, meta } structure
  // Use nullish coalescing (??) instead of || to handle 0 values correctly
  const totals = {
    visitors: Number(
      (totalsResult?.success ? totalsResult.totals?.visitors : undefined) ??
        statesResult.data?.totals?.visitors ??
        0
    ),
    views: Number(
      (totalsResult?.success ? totalsResult.totals?.views : undefined) ??
        statesResult.data?.totals?.views ??
        0
    ),
  }

  const meta = statesResult.meta
    ? {
        totalRows: Number(statesResult.meta.total_rows) || 0,
        totalPages: Number(statesResult.meta.total_pages) || 1,
        page: Number(statesResult.meta.page) || 1,
      }
    : null

  return { rows, totals, meta }
}
