/**
 * Shared types for overview widget renderers.
 */

import type { NavigateFn } from '@tanstack/react-router'

import type { GlobalMapData } from '@/components/custom/global-map'
import type { RegisteredWidget } from '@/contexts/content-registry-context'
import type { FixedDatePeriod } from '@/lib/fixed-date-ranges'
import type { Timeframe } from '@/lib/response-helpers'

export interface BatchQueryResult {
  success?: boolean
  items?: Array<Record<string, unknown>>
  totals?: Record<string, unknown>
  data?: {
    rows: Record<string, unknown>[]
    totals?: Record<string, unknown>
  }
}

export interface OverviewBatchResponse {
  success: boolean
  items: Record<string, BatchQueryResult>
}

/** Shared context passed to all widget renderers */
export interface WidgetRenderContext {
  batchItems: Record<string, BatchQueryResult>
  isCompareEnabled: boolean
  comparisonDateLabel: string
  navigate: NavigateFn
  calcPercentage: (current: number, previous: number) => { percentage: string; isNegative: boolean }
  isWidgetVisible: (id: string) => boolean
  isFetching: boolean
  // Chart-specific
  timeframe: Timeframe
  onTimeframeChange?: (tf: Timeframe) => void
  isChartRefetching: boolean
  apiDateParams: { date_from: string; date_to: string; previous_date_from?: string; previous_date_to?: string }
  // Map-specific
  mapDataByWidgetId: Record<string, GlobalMapData>
  isLoading: boolean
  // Traffic-summary-specific
  fixedDatePeriods: FixedDatePeriod[]
  trafficSummaryQueries: { data: unknown; isLoading: boolean }[]
  // Registered widgets
  registeredWidgets: RegisteredWidget[]
}

export interface TrafficSummaryPeriodResponse {
  success: boolean
  items: {
    traffic_summary?: {
      success: boolean
      totals: Record<string, { current: number | string; previous?: number | string }>
    }
  }
}

export interface TrafficSummaryRow {
  label: string
  values: Record<string, number>
  previousValues?: Record<string, number>
  comparisonLabel?: string
}
