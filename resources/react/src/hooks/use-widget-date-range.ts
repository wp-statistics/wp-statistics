import { useMemo } from 'react'

import { calculateComparisonRange, type ComparisonMode, getPresetRange } from '@/components/custom/date-range-picker'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { usePageOptions } from '@/hooks/use-page-options'
import { formatDateForAPI } from '@/lib/utils'

/**
 * Returns resolved date range for a specific widget based on its preset,
 * including comparison dates when comparison is enabled globally.
 */
export function useWidgetDateRange(widgetId: string) {
  const { getWidgetPreset } = usePageOptions()
  const preset = getWidgetPreset(widgetId)
  const { isCompareEnabled, comparisonMode, apiDateParams } = useGlobalFilters()

  return useMemo(() => {
    const range = getPresetRange(preset)
    const dateFrom = formatDateForAPI(range.from!)
    const dateTo = formatDateForAPI(range.to!)

    let compareDateFrom: string | undefined
    let compareDateTo: string | undefined

    if (isCompareEnabled) {
      // Compute comparison dates for this widget's date range
      const mode: ComparisonMode = (comparisonMode as ComparisonMode) || 'previous_period'
      const compareRange = calculateComparisonRange(range, mode)
      if (compareRange?.from && compareRange?.to) {
        compareDateFrom = formatDateForAPI(compareRange.from)
        compareDateTo = formatDateForAPI(compareRange.to)
      }
    }

    return {
      dateFrom,
      dateTo,
      compareDateFrom,
      compareDateTo,
      isCompareEnabled,
      compareDateToDisplay: isCompareEnabled ? (compareDateTo || apiDateParams.previous_date_to) : undefined,
      preset,
    }
  }, [preset, isCompareEnabled, comparisonMode, apiDateParams.previous_date_to])
}
