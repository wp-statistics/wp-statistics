import { queryOptions } from '@tanstack/react-query'

import type { Filter } from '@/components/custom/filter-bar'
import { transformFiltersToApi } from '@/lib/api-filter-transform'
import { clientRequest } from '@/lib/client-request'
import { createListParams, queryKeys } from '@/lib/query-keys'
import { WordPress } from '@/lib/wordpress'

/**
 * API response record for a single browser
 */
export interface BrowserRecord {
  browser_name: string
  browser_id: number | string
  visitors: number | string
  previous?: {
    visitors?: number | string
  }
}

/**
 * API response record for a browser version
 */
export interface BrowserVersionRecord {
  browser_version: string
  visitors: number | string
  previous?: {
    visitors?: number | string
  }
}

/**
 * Table query result structure
 */
interface BrowsersTableResult {
  success: boolean
  data: {
    rows: BrowserRecord[]
    totals?: {
      visitors?: number | string
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

interface TotalsResult {
  success: boolean
  items?: unknown[]
  totals?: {
    visitors?: number | string
  }
  meta?: Record<string, unknown>
}

export interface GetBrowsersResponse {
  success: boolean
  items: {
    browsers?: BrowsersTableResult
    totals?: TotalsResult
  }
  errors?: Record<string, { code: string; message: string }>
  skipped?: string[]
}

export interface GetBrowsersParams {
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
}

export const getBrowsersQueryOptions = ({
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
}: GetBrowsersParams) => {
  const apiFilters = transformFiltersToApi(filters)
  const hasCompare = !!(previous_date_from && previous_date_to)

  return queryOptions({
    queryKey: queryKeys.devices.browsers(
      createListParams(date_from, date_to, page, per_page, order_by, order, {
        compareDateFrom: previous_date_from,
        compareDateTo: previous_date_to,
        filters: apiFilters,
        context,
      })
    ),
    queryFn: () =>
      clientRequest.post<GetBrowsersResponse>(
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
              id: 'browsers',
              sources: ['visitors'],
              group_by: ['browser'],
              columns: ['browser_name', 'browser_id', 'visitors'],
              page,
              per_page,
              order_by,
              order: order.toUpperCase(),
              format: 'table',
              show_totals: false,
              compare: hasCompare,
              ...(context && { context }),
            },
            {
              id: 'totals',
              sources: ['visitors'],
              group_by: [],
              format: 'flat',
              compare: false,
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
 * Helper to extract browsers data from batch response
 */
export function extractBrowsersData(response: { data?: GetBrowsersResponse } | undefined): {
  rows: BrowserRecord[]
  totals: { visitors: number }
  meta: { totalRows: number; totalPages: number; page: number } | null
} {
  const browsersResult = response?.data?.items?.browsers
  const totalsResult = response?.data?.items?.totals

  if (!browsersResult?.success) {
    return { rows: [], totals: { visitors: 0 }, meta: null }
  }

  const rows = browsersResult.data?.rows || []

  const totals = {
    visitors: Number(
      (totalsResult?.success ? totalsResult.totals?.visitors : undefined) ??
        browsersResult.data?.totals?.visitors ??
        0
    ),
  }

  const meta = browsersResult.meta
    ? {
        totalRows: Number(browsersResult.meta.total_rows) || 0,
        totalPages: Number(browsersResult.meta.total_pages) || 1,
        page: Number(browsersResult.meta.page) || 1,
      }
    : null

  return { rows, totals, meta }
}

// ============================================================================
// Browser Versions (sub-row expansion)
// ============================================================================

interface BrowserVersionsTableResult {
  success: boolean
  data: {
    rows: BrowserVersionRecord[]
  }
  meta?: Record<string, unknown>
}

interface GetBrowserVersionsResponse {
  success: boolean
  items: {
    versions?: BrowserVersionsTableResult
  }
  errors?: Record<string, { code: string; message: string }>
}

export interface GetBrowserVersionsParams {
  browserId: number | string
  date_from: string
  date_to: string
  previous_date_from?: string
  previous_date_to?: string
  filters?: Filter[]
}

export const getBrowserVersionsQueryOptions = ({
  browserId,
  date_from,
  date_to,
  previous_date_from,
  previous_date_to,
  filters = [],
}: GetBrowserVersionsParams) => {
  const hasCompare = !!(previous_date_from && previous_date_to)
  const apiFilters = transformFiltersToApi(filters)

  return queryOptions({
    queryKey: ['wp-statistics', 'devices', 'browser-versions', browserId, date_from, date_to, previous_date_from, previous_date_to, apiFilters],
    queryFn: () =>
      clientRequest.post<GetBrowserVersionsResponse>(
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
              id: 'versions',
              sources: ['visitors'],
              group_by: ['browser_version'],
              columns: ['browser_version', 'visitors'],
              per_page: 100,
              order_by: 'visitors',
              order: 'DESC',
              format: 'table',
              show_totals: false,
              compare: hasCompare,
              filters: [
                {
                  key: 'browser',
                  operator: 'is',
                  value: String(browserId),
                },
              ],
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

export function extractBrowserVersionsData(
  response: { data?: GetBrowserVersionsResponse } | undefined
): BrowserVersionRecord[] {
  const result = response?.data?.items?.versions
  if (!result?.success) return []
  return result.data?.rows || []
}
