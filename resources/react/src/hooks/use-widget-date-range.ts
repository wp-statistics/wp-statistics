import { useMemo } from 'react'

import { calculateComparisonRange, type ComparisonMode, getPresetRange } from '@/components/custom/date-range-picker'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { usePageOptions } from '@/hooks/use-page-options'
import { formatDateForAPI } from '@/lib/utils'

import { getComparisonDateLabel } from './use-comparison-date-label'

export interface WidgetDateRange {
  dateFrom: string
  dateTo: string
  compareDateFrom?: string
  compareDateTo?: string
  isCompareEnabled: boolean
  comparisonDateLabel: string
  apiDateParams: {
    date_from: string
    date_to: string
    previous_date_from?: string
    previous_date_to?: string
  }
}

/**
 * Pure function that computes the resolved date range for a widget preset.
 * Used by both the hook and the parent-level queryDateOverrides computation.
 */
export function computeWidgetDateRange(
  preset: string,
  isCompareEnabled: boolean,
  comparisonMode?: string,
): WidgetDateRange {
  const range = getPresetRange(preset)
  const dateFrom = formatDateForAPI(range.from!)
  const dateTo = formatDateForAPI(range.to!)

  let compareDateFrom: string | undefined
  let compareDateTo: string | undefined
  let compareDateFromDate: Date | undefined
  let compareDateToDate: Date | undefined

  if (isCompareEnabled) {
    const mode: ComparisonMode = (comparisonMode as ComparisonMode) || 'previous_period'
    const compareRange = calculateComparisonRange(range, mode)
    if (compareRange?.from && compareRange?.to) {
      compareDateFrom = formatDateForAPI(compareRange.from)
      compareDateTo = formatDateForAPI(compareRange.to)
      compareDateFromDate = compareRange.from
      compareDateToDate = compareRange.to
    }
  }

  const comparisonLabel = getComparisonDateLabel(
    range.from!,
    range.to!,
    compareDateFromDate,
    compareDateToDate,
    isCompareEnabled,
  )

  return {
    dateFrom,
    dateTo,
    compareDateFrom,
    compareDateTo,
    isCompareEnabled,
    comparisonDateLabel: comparisonLabel.label ?? '',
    apiDateParams: {
      date_from: dateFrom,
      date_to: dateTo,
      previous_date_from: compareDateFrom,
      previous_date_to: compareDateTo,
    },
  }
}

/**
 * Hook that returns resolved date range for a specific widget based on its preset.
 * Delegates to computeWidgetDateRange for the actual computation.
 */
export function useWidgetDateRange(widgetId: string) {
  const { getWidgetPreset } = usePageOptions()
  const preset = getWidgetPreset(widgetId)
  const { isCompareEnabled, comparisonMode } = useGlobalFilters()

  return useMemo(
    () => computeWidgetDateRange(preset, isCompareEnabled, comparisonMode),
    [preset, isCompareEnabled, comparisonMode],
  )
}
