import { queryOptions } from '@tanstack/react-query'

import type { Filter } from '@/components/custom/filter-bar'
import { transformFiltersToApi } from '@/lib/api-filter-transform'
import { clientRequest } from '@/lib/client-request'
import { createListParams, queryKeys } from '@/lib/query-keys'
import { WordPress } from '@/lib/wordpress'

/**
 * API response record for a single device category
 */
export interface DeviceCategoryRecord {
  device_type_name: string
  visitors: number | string
  previous?: {
    visitors?: number | string
  }
}

/**
 * Table query result structure
 */
interface DeviceCategoriesTableResult {
  success: boolean
  data: {
    rows: DeviceCategoryRecord[]
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

export interface GetDeviceCategoriesResponse {
  success: boolean
  items: {
    device_categories?: DeviceCategoriesTableResult
    totals?: TotalsResult
  }
  errors?: Record<string, { code: string; message: string }>
  skipped?: string[]
}

export interface GetDeviceCategoriesParams {
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

export const getDeviceCategoriesQueryOptions = ({
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
}: GetDeviceCategoriesParams) => {
  const apiFilters = transformFiltersToApi(filters)
  const hasCompare = !!(previous_date_from && previous_date_to)

  return queryOptions({
    queryKey: queryKeys.devices.deviceCategories(
      createListParams(date_from, date_to, page, per_page, order_by, order, {
        compareDateFrom: previous_date_from,
        compareDateTo: previous_date_to,
        filters: apiFilters,
        context,
      })
    ),
    queryFn: () =>
      clientRequest.post<GetDeviceCategoriesResponse>(
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
              id: 'device_categories',
              sources: ['visitors'],
              group_by: ['device_type'],
              columns: ['device_type_name', 'visitors'],
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
 * Helper to extract device categories data from batch response
 */
export function extractDeviceCategoriesData(response: { data?: GetDeviceCategoriesResponse } | undefined): {
  rows: DeviceCategoryRecord[]
  totals: { visitors: number }
  meta: { totalRows: number; totalPages: number; page: number } | null
} {
  const categoriesResult = response?.data?.items?.device_categories
  const totalsResult = response?.data?.items?.totals

  if (!categoriesResult?.success) {
    return { rows: [], totals: { visitors: 0 }, meta: null }
  }

  const rows = categoriesResult.data?.rows || []

  const totals = {
    visitors: Number(
      (totalsResult?.success ? totalsResult.totals?.visitors : undefined) ??
        categoriesResult.data?.totals?.visitors ??
        0
    ),
  }

  const meta = categoriesResult.meta
    ? {
        totalRows: Number(categoriesResult.meta.total_rows) || 0,
        totalPages: Number(categoriesResult.meta.total_pages) || 1,
        page: Number(categoriesResult.meta.page) || 1,
      }
    : null

  return { rows, totals, meta }
}
