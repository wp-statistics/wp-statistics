import { queryOptions } from '@tanstack/react-query'

import type { Filter } from '@/components/custom/filter-bar'
import { transformFiltersToApi } from '@/lib/api-filter-transform'
import { clientRequest } from '@/lib/client-request'
import { createListParams, queryKeys } from '@/lib/query-keys'
import { WordPress } from '@/lib/wordpress'

/**
 * API response record for a single operating system
 */
export interface OsRecord {
  os_name: string
  os_id: number | string
  visitors: number | string
  previous?: {
    visitors?: number | string
  }
}

/**
 * Table query result structure
 */
interface OsTableResult {
  success: boolean
  data: {
    rows: OsRecord[]
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

export interface GetOsResponse {
  success: boolean
  items: {
    operating_systems?: OsTableResult
    totals?: TotalsResult
  }
  errors?: Record<string, { code: string; message: string }>
  skipped?: string[]
}

export interface GetOsParams {
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

export const getOsQueryOptions = ({
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
}: GetOsParams) => {
  const apiFilters = transformFiltersToApi(filters)
  const hasCompare = !!(previous_date_from && previous_date_to)

  return queryOptions({
    queryKey: queryKeys.devices.operatingSystems(
      createListParams(date_from, date_to, page, per_page, order_by, order, {
        compareDateFrom: previous_date_from,
        compareDateTo: previous_date_to,
        filters: apiFilters,
        context,
      })
    ),
    queryFn: () =>
      clientRequest.post<GetOsResponse>(
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
              id: 'operating_systems',
              sources: ['visitors'],
              group_by: ['os'],
              columns: ['os_name', 'os_id', 'visitors'],
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
 * Helper to extract operating systems data from batch response
 */
export function extractOsData(response: { data?: GetOsResponse } | undefined): {
  rows: OsRecord[]
  totals: { visitors: number }
  meta: { totalRows: number; totalPages: number; page: number } | null
} {
  const osResult = response?.data?.items?.operating_systems
  const totalsResult = response?.data?.items?.totals

  if (!osResult?.success) {
    return { rows: [], totals: { visitors: 0 }, meta: null }
  }

  const rows = osResult.data?.rows || []

  const totals = {
    visitors: Number(
      (totalsResult?.success ? totalsResult.totals?.visitors : undefined) ??
        osResult.data?.totals?.visitors ??
        0
    ),
  }

  const meta = osResult.meta
    ? {
        totalRows: Number(osResult.meta.total_rows) || 0,
        totalPages: Number(osResult.meta.total_pages) || 1,
        page: Number(osResult.meta.page) || 1,
      }
    : null

  return { rows, totals, meta }
}
