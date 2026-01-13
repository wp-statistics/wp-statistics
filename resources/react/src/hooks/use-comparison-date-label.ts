import { useMemo } from 'react'

import { __ } from '@wordpress/i18n'

import { useGlobalFilters } from '@/hooks/use-global-filters'

export interface ComparisonDateLabel {
  /** Compact label: "Dec 16 - Jan 12 vs. Nov 18 - Dec 15" (year omitted if same) */
  label: string | undefined
  /** Current period only */
  currentPeriodLabel: string
  /** Previous period only */
  previousPeriodLabel: string | undefined
  /** Whether comparison is enabled */
  isCompareEnabled: boolean
}

/**
 * Format a date for display in tooltips
 * @param date - The date to format
 * @param includeYear - Whether to include the year
 */
function formatDate(date: Date, includeYear: boolean = true, locale: string = 'en-US'): string {
  const options: Intl.DateTimeFormatOptions = {
    month: 'short',
    day: 'numeric',
    ...(includeYear && { year: 'numeric' }),
  }
  return date.toLocaleDateString(locale, options)
}

/**
 * Format a date range for display (compact format)
 * Returns "Dec 16 - Jan 12" or just "Dec 16" if same day
 */
function formatDateRange(from: Date, to: Date, includeYear: boolean = true, locale: string = 'en-US'): string {
  const fromStr = formatDate(from, includeYear, locale)
  const toStr = formatDate(to, includeYear, locale)

  // If same day, just show one date
  if (from.getTime() === to.getTime()) {
    return fromStr
  }

  return `${fromStr} - ${toStr}`
}

/**
 * Check if all dates are in the same year
 */
function allSameYear(...dates: Date[]): boolean {
  if (dates.length === 0) return true
  const firstYear = dates[0].getFullYear()
  return dates.every((date) => date.getFullYear() === firstYear)
}

/**
 * Hook that provides formatted date range labels for comparison tooltips
 *
 * @example
 * ```tsx
 * const { label, isCompareEnabled } = useComparisonDateLabel()
 *
 * // label = "Dec 16 - Jan 12 vs. Nov 18 - Dec 15" (compact, year omitted if same)
 * // or undefined if comparison is disabled
 * ```
 */
export function useComparisonDateLabel(): ComparisonDateLabel {
  const { dateFrom, dateTo, compareDateFrom, compareDateTo, isCompareEnabled } = useGlobalFilters()

  return useMemo(() => {
    // For current period label (used standalone), always include year
    const currentPeriodLabel = formatDateRange(dateFrom, dateTo, true)

    if (!isCompareEnabled || !compareDateFrom || !compareDateTo) {
      return {
        label: undefined,
        currentPeriodLabel,
        previousPeriodLabel: undefined,
        isCompareEnabled: false,
      }
    }

    // For comparison tooltip, check if we can omit the year for compactness
    const sameYear = allSameYear(dateFrom, dateTo, compareDateFrom, compareDateTo)

    const currentLabel = formatDateRange(dateFrom, dateTo, !sameYear)
    const previousLabel = formatDateRange(compareDateFrom, compareDateTo, !sameYear)

    return {
      label: `${currentLabel} ${__('vs.', 'wp-statistics')} ${previousLabel}`,
      currentPeriodLabel,
      previousPeriodLabel: formatDateRange(compareDateFrom, compareDateTo, true),
      isCompareEnabled: true,
    }
  }, [dateFrom, dateTo, compareDateFrom, compareDateTo, isCompareEnabled])
}
