import { __ } from '@wordpress/i18n'
import { useMemo } from 'react'

import { useGlobalFilters } from '@/hooks/use-global-filters'
import { getWpCurrentYear } from '@/lib/wp-date'

export interface ComparisonDateLabel {
  /** Compact label: "Dec 16 - Jan 12 vs. Nov 18 - Dec 15" (year omitted if all dates are in current year) */
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
 * Returns "Dec 16 - Jan 12, 2026" or just "Dec 16, 2026" if same day
 * When includeYear is true, year is shown on both dates
 * When includeYear is false but keepOneYear is true, year is shown only on end date for clarity
 */
function formatDateRange(from: Date, to: Date, includeYear: boolean = true, locale: string = 'en-US', keepOneYear: boolean = true): string {
  // If same day, always show year on that single date for clarity
  if (from.getTime() === to.getTime()) {
    return formatDate(from, includeYear || keepOneYear, locale)
  }

  // When not including year on both, still show it on end date for clarity
  const fromStr = formatDate(from, includeYear, locale)
  const toStr = formatDate(to, includeYear || keepOneYear, locale)

  return `${fromStr} - ${toStr}`
}

/**
 * Check if all dates are in the current year (WordPress timezone)
 */
function allInCurrentYear(...dates: Date[]): boolean {
  const currentYear = getWpCurrentYear()
  return dates.every((date) => date.getFullYear() === currentYear)
}

/**
 * Hook that provides formatted date range labels for comparison tooltips
 *
 * @example
 * ```tsx
 * const { label, isCompareEnabled } = useComparisonDateLabel()
 *
 * // label = "Dec 16 - Jan 12 vs. Nov 18 - Dec 15" (compact, year omitted if all in current year)
 * // or undefined if comparison is disabled
 * ```
 */
export function useComparisonDateLabel(): ComparisonDateLabel {
  const { dateFrom, dateTo, compareDateFrom, compareDateTo, isCompareEnabled } = useGlobalFilters()

  return useMemo(() => {
    // For current period label (used standalone), hide year if in current year
    const showYearInCurrentPeriod = !allInCurrentYear(dateFrom, dateTo)
    const currentPeriodLabel = formatDateRange(dateFrom, dateTo, showYearInCurrentPeriod)

    if (!isCompareEnabled || !compareDateFrom || !compareDateTo) {
      return {
        label: undefined,
        currentPeriodLabel,
        previousPeriodLabel: undefined,
        isCompareEnabled: false,
      }
    }

    // For comparison tooltip, hide year only if all 4 dates are in the current year
    const allCurrentYear = allInCurrentYear(dateFrom, dateTo, compareDateFrom, compareDateTo)
    const showYear = !allCurrentYear

    const currentLabel = formatDateRange(dateFrom, dateTo, showYear)
    const previousLabel = formatDateRange(compareDateFrom, compareDateTo, showYear)

    return {
      label: `${currentLabel} ${__('vs.', 'wp-statistics')} ${previousLabel}`,
      currentPeriodLabel,
      previousPeriodLabel: formatDateRange(compareDateFrom, compareDateTo, !allInCurrentYear(compareDateFrom, compareDateTo)),
      isCompareEnabled: true,
    }
  }, [dateFrom, dateTo, compareDateFrom, compareDateTo, isCompareEnabled])
}
