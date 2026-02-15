import { queryOptions } from '@tanstack/react-query'

import type { Filter } from '@/components/custom/filter-bar'
import { transformFiltersToApi } from '@/lib/api-filter-transform'
import { clientRequest } from '@/lib/client-request'
import { createListParams, queryKeys } from '@/lib/query-keys'
import { WordPress } from '@/lib/wordpress'

// Reuse types from US States - same data structure
import type { TotalsResult, USStateRecord } from './get-us-states'

/** Region record - reuses USStateRecord since structure is identical */
export type CountryRegionRecord = USStateRecord

/**
 * Table query result structure (used in batch responses)
 */
export interface CountryRegionsTableResult {
  success: boolean
  data: {
    rows: CountryRegionRecord[]
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
 * Batch response structure for country regions query
 */
export interface GetCountryRegionsResponse {
  success: boolean
  items: {
    country_regions?: CountryRegionsTableResult
    totals?: TotalsResult
  }
  errors?: Record<string, { code: string; message: string }>
  skipped?: string[]
}

export interface GetCountryRegionsParams {
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
  countryCode: string
}

// Shared column mapping for region-based queries
const COLUMN_MAPPING: Record<string, string> = {
  region: 'region_name',
  visitors: 'visitors',
  views: 'views',
  bounceRate: 'bounce_rate',
  sessionDuration: 'avg_session_duration',
}

// Default columns for region queries
const DEFAULT_COLUMNS = [
  'region_code',
  'region_name',
  'visitors',
  'views',
  'bounce_rate',
  'avg_session_duration',
]

export const getCountryRegionsQueryOptions = ({
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
  countryCode,
}: GetCountryRegionsParams) => {
  const apiOrderBy = COLUMN_MAPPING[order_by] || order_by
  const apiFilters = transformFiltersToApi(filters)
  const hasCompare = !!(previous_date_from && previous_date_to)
  const apiColumns = columns && columns.length > 0 ? columns : DEFAULT_COLUMNS

  // Dynamic country filter based on user's country
  const countryFilter = [{ key: 'country', operator: 'is', value: countryCode }]

  return queryOptions({
    // eslint-disable-next-line @tanstack/query/exhaustive-deps -- hasCompare is derived from compareDateFrom/compareDateTo which are in the key
    queryKey: queryKeys.geographic.countryRegions({
      ...createListParams(date_from, date_to, page, per_page, apiOrderBy, order, {
        compareDateFrom: previous_date_from,
        compareDateTo: previous_date_to,
        filters: apiFilters,
        context,
        columns: apiColumns,
      }),
      countryCode,
    }),
    queryFn: () =>
      clientRequest.post<GetCountryRegionsResponse>(
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
              id: 'country_regions',
              sources: ['visitors', 'views', 'bounce_rate', 'avg_session_duration'],
              group_by: ['region'],
              columns: apiColumns,
              page,
              per_page,
              order_by: apiOrderBy,
              order: order.toUpperCase(),
              format: 'table',
              show_totals: false,
              compare: hasCompare,
              filters: countryFilter,
              ...(context && { context }),
            },
            {
              id: 'totals',
              sources: ['visitors', 'views'],
              group_by: [],
              format: 'flat',
              compare: false,
              filters: countryFilter,
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
 * Helper to extract country regions data from batch response
 */
export function extractCountryRegionsData(response: { data?: GetCountryRegionsResponse } | undefined): {
  rows: CountryRegionRecord[]
  totals: { visitors: number; views: number }
  meta: { totalRows: number; totalPages: number; page: number } | null
} {
  const regionsResult = response?.data?.items?.country_regions
  const totalsResult = response?.data?.items?.totals

  if (!regionsResult?.success) {
    return { rows: [], totals: { visitors: 0, views: 0 }, meta: null }
  }

  const rows = regionsResult.data?.rows || []

  const totals = {
    visitors: Number(
      (totalsResult?.success ? totalsResult.totals?.visitors : undefined) ??
        regionsResult.data?.totals?.visitors ??
        0
    ),
    views: Number(
      (totalsResult?.success ? totalsResult.totals?.views : undefined) ?? regionsResult.data?.totals?.views ?? 0
    ),
  }

  const meta = regionsResult.meta
    ? {
        totalRows: Number(regionsResult.meta.total_rows) || 0,
        totalPages: Number(regionsResult.meta.total_pages) || 1,
        page: Number(regionsResult.meta.page) || 1,
      }
    : null

  return { rows, totals, meta }
}
