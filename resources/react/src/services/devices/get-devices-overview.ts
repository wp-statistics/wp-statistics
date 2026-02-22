import { queryOptions } from '@tanstack/react-query'

import type { Filter } from '@/components/custom/filter-bar'
import { transformFiltersToApi } from '@/lib/api-filter-transform'
import { clientRequest } from '@/lib/client-request'
import { WordPress } from '@/lib/wordpress'

// Row types for devices data
export interface BrowserRow {
  browser_name: string
  browser_version?: string
  visitors: number | string
  previous?: {
    visitors: number | string
  }
}

export interface OperatingSystemRow {
  os_name: string
  os_version?: string
  visitors: number | string
  previous?: {
    visitors: number | string
  }
}

export interface DeviceCategoryRow {
  device_type_name: string
  visitors: number | string
  previous?: {
    visitors: number | string
  }
}

export interface ScreenResolutionRow {
  screen_resolution: string
  visitors: number | string
  previous?: {
    visitors: number | string
  }
}

// Table format response wrapper
interface TableQueryResult<T> {
  success: boolean
  data: {
    rows: T[]
    totals?: Record<string, unknown>
  }
  meta?: Record<string, unknown>
}

// Flat format response for single-value metrics
interface FlatQueryResult {
  success: boolean
  items: Array<Record<string, string | number>>
  totals: Record<string, unknown>
}

// Batch response structure
export interface DevicesOverviewResponse {
  success: boolean
  items: {
    // Metrics for top values
    metrics_top_browser?: FlatQueryResult
    metrics_top_os?: FlatQueryResult
    metrics_top_device?: FlatQueryResult
    metrics_top_resolution?: FlatQueryResult
    // Tables
    top_browsers?: TableQueryResult<BrowserRow>
    top_operating_systems?: TableQueryResult<OperatingSystemRow>
    top_device_categories?: TableQueryResult<DeviceCategoryRow>
    top_screen_resolutions?: TableQueryResult<ScreenResolutionRow>
  }
  errors?: Record<string, { code: string; message: string }>
  skipped?: string[]
}

export interface GetDevicesOverviewParams {
  dateFrom: string
  dateTo: string
  compareDateFrom?: string
  compareDateTo?: string
  filters?: Filter[]
}

export const getDevicesOverviewQueryOptions = ({
  dateFrom,
  dateTo,
  compareDateFrom,
  compareDateTo,
  filters = [],
}: GetDevicesOverviewParams) => {
  const apiFilters = transformFiltersToApi(filters)
  const hasCompare = !!(compareDateFrom && compareDateTo)

  return queryOptions({
    queryKey: ['devices-overview', dateFrom, dateTo, compareDateFrom, compareDateTo, apiFilters, hasCompare],
    queryFn: () =>
      clientRequest.post<DevicesOverviewResponse>(
        '',
        {
          date_from: dateFrom,
          date_to: dateTo,
          compare: hasCompare,
          ...(hasCompare && {
            previous_date_from: compareDateFrom,
            previous_date_to: compareDateTo,
          }),
          ...(Object.keys(apiFilters).length > 0 && { filters: apiFilters }),
          queries: [
            // Metric: Top Browser
            {
              id: 'metrics_top_browser',
              sources: ['visitors'],
              group_by: ['browser'],
              columns: ['browser_name', 'visitors'],
              per_page: 1,
              order_by: 'visitors',
              order: 'DESC',
              format: 'flat',
              show_totals: false,
              compare: false,
            },
            // Metric: Top OS
            {
              id: 'metrics_top_os',
              sources: ['visitors'],
              group_by: ['os'],
              columns: ['os_name', 'visitors'],
              per_page: 1,
              order_by: 'visitors',
              order: 'DESC',
              format: 'flat',
              show_totals: false,
              compare: false,
            },
            // Metric: Top Device Category
            {
              id: 'metrics_top_device',
              sources: ['visitors'],
              group_by: ['device_type'],
              columns: ['device_type_name', 'visitors'],
              per_page: 1,
              order_by: 'visitors',
              order: 'DESC',
              format: 'flat',
              show_totals: false,
              compare: false,
            },
            // Metric: Top Screen Resolution
            {
              id: 'metrics_top_resolution',
              sources: ['visitors'],
              group_by: ['screen_resolution'],
              columns: ['screen_resolution', 'visitors'],
              per_page: 1,
              order_by: 'visitors',
              order: 'DESC',
              format: 'flat',
              show_totals: false,
              compare: false,
            },
            // Top Browsers (5)
            {
              id: 'top_browsers',
              sources: ['visitors'],
              group_by: ['browser'],
              columns: ['browser_name', 'visitors'],
              per_page: 5,
              order_by: 'visitors',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: hasCompare,
            },
            // Top Operating Systems (5)
            {
              id: 'top_operating_systems',
              sources: ['visitors'],
              group_by: ['os'],
              columns: ['os_name', 'visitors'],
              per_page: 5,
              order_by: 'visitors',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: hasCompare,
            },
            // Top Device Categories (5)
            {
              id: 'top_device_categories',
              sources: ['visitors'],
              group_by: ['device_type'],
              columns: ['device_type_name', 'visitors'],
              per_page: 5,
              order_by: 'visitors',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: hasCompare,
            },
            // Top Screen Resolutions (5)
            {
              id: 'top_screen_resolutions',
              sources: ['visitors'],
              group_by: ['screen_resolution'],
              columns: ['screen_resolution', 'visitors'],
              per_page: 5,
              order_by: 'visitors',
              order: 'DESC',
              format: 'table',
              show_totals: true,
              compare: hasCompare,
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
