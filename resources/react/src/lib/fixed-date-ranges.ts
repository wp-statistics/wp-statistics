/**
 * Fixed Date Range Utility
 *
 * Calculates fixed date periods (Today, Yesterday, Last 7 Days, etc.)
 * independent of the global date filter. Used for Traffic Summary widget.
 */

import { __ } from '@wordpress/i18n'

import { formatDateForAPI } from '@/lib/utils'

export type FixedDatePeriodId = 'today' | 'yesterday' | 'last_7_days' | 'last_28_days' | 'total'

export interface FixedDatePeriod {
  id: FixedDatePeriodId
  label: string
  dateFrom: string
  dateTo: string
  compareDateFrom?: string
  compareDateTo?: string
  /** Human-readable comparison description for tooltips */
  comparisonLabel?: string
}

/**
 * Get a date offset by a number of days from today
 */
function getDateOffset(daysAgo: number): Date {
  const date = new Date()
  date.setHours(0, 0, 0, 0)
  date.setDate(date.getDate() - daysAgo)
  return date
}

/**
 * Get fixed date periods with comparison ranges
 *
 * @returns Array of 5 fixed date periods:
 * - Today vs Yesterday
 * - Yesterday vs Day before yesterday
 * - Last 7 Days vs Previous 7 days
 * - Last 28 Days vs Previous 28 days
 * - Total (no comparison)
 */
export function getFixedDatePeriods(): FixedDatePeriod[] {
  const today = getDateOffset(0)
  const yesterday = getDateOffset(1)
  const dayBeforeYesterday = getDateOffset(2)

  // Last 7 days: from 6 days ago to today (7 days total)
  const last7DaysStart = getDateOffset(6)
  // Previous 7 days: from 13 days ago to 7 days ago
  const prev7DaysStart = getDateOffset(13)
  const prev7DaysEnd = getDateOffset(7)

  // Last 28 days: from 27 days ago to today (28 days total)
  const last28DaysStart = getDateOffset(27)
  // Previous 28 days: from 55 days ago to 28 days ago
  const prev28DaysStart = getDateOffset(55)
  const prev28DaysEnd = getDateOffset(28)

  return [
    {
      id: 'today',
      label: __('Today', 'wp-statistics'),
      dateFrom: formatDateForAPI(today),
      dateTo: formatDateForAPI(today),
      compareDateFrom: formatDateForAPI(yesterday),
      compareDateTo: formatDateForAPI(yesterday),
      comparisonLabel: __('vs. Yesterday', 'wp-statistics'),
    },
    {
      id: 'yesterday',
      label: __('Yesterday', 'wp-statistics'),
      dateFrom: formatDateForAPI(yesterday),
      dateTo: formatDateForAPI(yesterday),
      compareDateFrom: formatDateForAPI(dayBeforeYesterday),
      compareDateTo: formatDateForAPI(dayBeforeYesterday),
      comparisonLabel: __('vs. Day before', 'wp-statistics'),
    },
    {
      id: 'last_7_days',
      label: __('Last 7 Days', 'wp-statistics'),
      dateFrom: formatDateForAPI(last7DaysStart),
      dateTo: formatDateForAPI(today),
      compareDateFrom: formatDateForAPI(prev7DaysStart),
      compareDateTo: formatDateForAPI(prev7DaysEnd),
      comparisonLabel: __('vs. Previous 7 days', 'wp-statistics'),
    },
    {
      id: 'last_28_days',
      label: __('Last 28 Days', 'wp-statistics'),
      dateFrom: formatDateForAPI(last28DaysStart),
      dateTo: formatDateForAPI(today),
      compareDateFrom: formatDateForAPI(prev28DaysStart),
      compareDateTo: formatDateForAPI(prev28DaysEnd),
      comparisonLabel: __('vs. Previous 28 days', 'wp-statistics'),
    },
    {
      id: 'total',
      label: __('Total', 'wp-statistics'),
      // Use a very old date as "beginning of time"
      dateFrom: '2000-01-01',
      dateTo: formatDateForAPI(today),
      // No comparison for total
    },
  ]
}
