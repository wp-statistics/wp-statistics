import { type ClassValue, clsx } from 'clsx'
import { twMerge } from 'tailwind-merge'

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}

export const getToday = (): string => {
  const today = new Date()
  return today.toISOString().split('T')[0]
}

/**
 * Check if a date string represents today's date
 */
export function isToday(dateString: string): boolean {
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  const checkDate = new Date(dateString)
  checkDate.setHours(0, 0, 0, 0)
  return today.getTime() === checkDate.getTime()
}

export const formatDateForAPI = (date: Date): string => {
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

/**
 * Format seconds as duration string
 * - Shows MM:SS when hours is 0 (e.g., "10:34")
 * - Shows HH:MM:SS when hours > 0 (e.g., "01:10:34")
 */
export function formatDuration(seconds: number): string {
  const hours = Math.floor(seconds / 3600)
  const mins = Math.floor((seconds % 3600) / 60)
  const secs = Math.floor(seconds % 60)

  if (hours === 0) {
    return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`
  }
  return `${hours.toString().padStart(2, '0')}:${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`
}

/**
 * Format a number with decimals, removing unnecessary trailing .0
 * Examples: 100.0 → "100", 17.5 → "17.5", 0.0 → "0"
 */
export function formatDecimal(value: number, decimals: number = 1): string {
  if (!Number.isFinite(value)) {
    return String(value)
  }
  const fixed = value.toFixed(decimals)
  return fixed.endsWith('.0') ? fixed.slice(0, -2) : fixed
}

/**
 * Format numbers in compact notation (k, M) for display
 * Examples: 999 → "999", 1500 → "1.5k", 2340000 → "2.34M"
 */
export function formatCompactNumber(num: number): string {
  if (!Number.isFinite(num)) return String(num)
  if (num >= 1000000) return `${formatDecimal(num / 1000000)}M`
  if (num >= 1000) return `${formatDecimal(num / 1000)}k`
  return num.toLocaleString()
}

/**
 * Decode URL-encoded UTF-8 text
 * Useful for decoding search terms with non-ASCII characters (e.g., Persian, Arabic)
 * Returns original text if decoding fails
 */
export function decodeText(text: string | undefined | null): string | undefined {
  if (!text) return undefined
  try {
    return decodeURIComponent(text)
  } catch {
    return text
  }
}

/**
 * Calculate a share/proportion percentage, capped at 100%
 *
 * Use this for "part of whole" calculations where the result should never exceed 100%.
 * For example: logged-in visitors as a share of total visitors, or device type distribution.
 *
 * This prevents display issues when data inconsistencies cause part > whole.
 *
 * @param part - The subset value (e.g., logged-in visitors)
 * @param total - The total value (e.g., all visitors)
 * @returns Percentage between 0 and 100
 *
 * @example
 * calcSharePercentage(50, 100)  // 50
 * calcSharePercentage(100, 100) // 100
 * calcSharePercentage(101, 100) // 100 (capped)
 * calcSharePercentage(0, 0)     // 0
 */
export function calcSharePercentage(part: number, total: number): number {
  if (total <= 0 || part <= 0) return 0
  const percentage = (part / total) * 100
  return Math.min(percentage, 100)
}

/**
 * Extract total value from API response
 * Handles both flat values and { current, previous } structure
 *
 * @example
 * getTotalValue(100)                    // 100
 * getTotalValue("100")                  // 100
 * getTotalValue({ current: 100 })       // 100
 * getTotalValue({ current: "100" })     // 100
 * getTotalValue(null)                   // 0
 */
export function getTotalValue(total: unknown): number {
  if (typeof total === 'number') return total
  if (typeof total === 'string') return Number(total) || 0
  if (total && typeof total === 'object') {
    return Number((total as { current?: number | string }).current) || 0
  }
  return 0
}
