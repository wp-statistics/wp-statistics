/**
 * WordPress Timezone-Aware Date Utilities
 *
 * Provides date functions that use the WordPress site timezone instead of the browser's local timezone.
 * This ensures consistency between frontend date calculations and backend data.
 */

import { WordPress } from '@/lib/wordpress'

/**
 * Get the current date/time in the WordPress site timezone.
 * Returns a Date object representing "now" as it appears in the WP timezone.
 */
export function getWpNow(): Date {
  const wp = WordPress.getInstance()
  const timezone = wp.getTimezone()

  // Try named timezone first (e.g., "America/New_York")
  if (timezone?.string) {
    try {
      const formatter = new Intl.DateTimeFormat('en-CA', {
        timeZone: timezone.string,
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false,
      })
      const parts = formatter.formatToParts(new Date())
      const get = (type: string) => parts.find((p) => p.type === type)?.value || '0'
      return new Date(
        parseInt(get('year')),
        parseInt(get('month')) - 1,
        parseInt(get('day')),
        parseInt(get('hour')),
        parseInt(get('minute')),
        parseInt(get('second'))
      )
    } catch {
      // Fall through to GMT offset
    }
  }

  // Fallback to GMT offset
  const offsetHours = timezone?.gmtOffset ?? 0
  const now = new Date()
  const utc = now.getTime() + now.getTimezoneOffset() * 60000
  return new Date(utc + offsetHours * 3600000)
}

/**
 * Get today's date at midnight in the WordPress site timezone.
 */
export function getWpToday(): Date {
  const now = getWpNow()
  now.setHours(0, 0, 0, 0)
  return now
}

/**
 * Get the current year in the WordPress site timezone.
 */
export function getWpCurrentYear(): number {
  return getWpNow().getFullYear()
}

/**
 * Parse a date-only string (YYYY-MM-DD) to a Date object.
 * This treats the date as a local date (no timezone conversion).
 */
export function parseDateString(dateStr: string): Date {
  const parts = dateStr.split('-').map((p) => parseInt(p, 10))
  return new Date(parts[0], parts[1] - 1, parts[2])
}

/**
 * Parse a datetime string (YYYY-MM-DD HH:MM:SS or ISO format) to a Date object.
 * This treats the datetime as a local datetime (no timezone conversion).
 */
export function parseDateTimeString(dateTimeStr: string): Date {
  const normalized = dateTimeStr.replace(' ', 'T')
  const match = normalized.match(/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})/)
  if (match) {
    return new Date(
      parseInt(match[1]),
      parseInt(match[2]) - 1,
      parseInt(match[3]),
      parseInt(match[4]),
      parseInt(match[5]),
      parseInt(match[6])
    )
  }
  // Fallback to date-only parsing
  return parseDateString(dateTimeStr.slice(0, 10))
}
